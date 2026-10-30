// Run against a disposable Chromium browser at BROWSER_DEBUG_URL (default :9242).
// Uses real Blade invoice templates, Alpine, MDI, pointer clicks, and PDF printing.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../../', import.meta.url));
const artifacts = fs.mkdtempSync(path.join(os.tmpdir(), 'pos-invoice-sidebar-'));
const route = '/admin/order-bulk-print/preview/invoice';
let origin;
const server = http.createServer((req, res) => {
    const url = new URL(req.url, origin);
    if (url.pathname.startsWith('/admin/order-bulk-print/preview')) {
        const source = execFileSync('php', [path.join(root, 'tests/Fixtures/order-bulk-print-preview.php'), url.href], { cwd: root, encoding: 'utf8' })
            .replaceAll('http://localhost', origin);
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        if (req.headers['x-mdi-partial']) return res.end(`<!doctype html><title>Invoice preview</title><div id="content">${source}</div>`);
        return res.end(`<!doctype html><html><head><meta charset="utf-8">
            <link rel="stylesheet" href="/admin-public/css/app.css"><script src="/admin-public/js/app.js"></script>
            </head><body><div x-data="workspaceMdi()"><div id="workspace-viewport">
            <div id="tab-pane-initial" class="workspace-tab-pane active">${source}</div></div></div>
            <script>window.workspaceAccess={scope:'invoice-sidebar-test',dashboard:false};
                window.printCalls=0;window.print=()=>{printCalls++;window.dispatchEvent(new Event('afterprint'))};Alpine.start();</script>
            </body></html>`);
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (file.startsWith(path.join(root, 'public') + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
        res.setHeader('Content-Type', file.endsWith('.css') ? 'text/css' : file.endsWith('.js') ? 'text/javascript' : 'application/octet-stream');
        return res.end(fs.readFileSync(file));
    }
    res.statusCode = 404; res.end();
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
origin = `http://127.0.0.1:${server.address().port}`;
const targets = await fetch(`${process.env.BROWSER_DEBUG_URL || 'http://127.0.0.1:9242'}/json/list`).then(r => r.json());
const ws = new WebSocket(targets.find(target => target.type === 'page').webSocketDebuggerUrl);
await new Promise(resolve => ws.addEventListener('open', resolve, { once: true }));
let sequence = 0;
const pending = new Map(), errors = [];
ws.addEventListener('message', event => {
    const message = JSON.parse(event.data);
    if (message.id) {
        const promise = pending.get(message.id); pending.delete(message.id);
        message.error ? promise.reject(message.error) : promise.resolve(message.result);
    }
    if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails);
});
const send = (method, params = {}) => new Promise((resolve, reject) => {
    const id = ++sequence; pending.set(id, { resolve, reject }); ws.send(JSON.stringify({ id, method, params }));
});
const evaluate = async expression => {
    const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    assert.ok(!result.exceptionDetails, JSON.stringify(result.exceptionDetails));
    return result.result.value;
};
const waitFor = async expression => {
    for (let i = 0; i < 150; i++) {
        if (await evaluate(`Boolean(${expression})`)) return;
        await new Promise(resolve => setTimeout(resolve, 30));
    }
    assert.fail(`Timed out: ${expression}\n${JSON.stringify(errors)}`);
};
const scope = '.workspace-tab-pane.active ';
const app = `Alpine.$data(document.querySelector('${scope}.bulk-preview-wrapper'))`;
const click = async selector => {
    const point = await evaluate(`(async () => {
        await Alpine.nextTick();const el=document.querySelector(${JSON.stringify(scope + selector)});
        if(!el)throw Error('Missing control: '+${JSON.stringify(selector)});
        el.scrollIntoView({block:'center',behavior:'instant'});
        await new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)));
        const rect=el.getBoundingClientRect(),x=rect.x+rect.width/2,y=rect.y+rect.height/2;
        if(!el.contains(document.elementFromPoint(x,y)))throw Error('Click intercepted: '+${JSON.stringify(selector)});
        return {x,y};
    })()`);
    await send('Input.dispatchMouseEvent', { type: 'mousePressed', button: 'left', clickCount: 1, ...point });
    await send('Input.dispatchMouseEvent', { type: 'mouseReleased', button: 'left', clickCount: 1, ...point });
    await evaluate('Alpine.nextTick()');
};
const navigate = async (query = '') => {
    await send('Page.navigate', { url: origin + route + query });
    await waitFor(`window.Alpine && document.querySelector('${scope}.bulk-preview-wrapper') && ${app}.isReady`);
};
const selected = () => evaluate(`${app}.selectedInvoiceIds.slice().sort()`);
const visible = () => evaluate(`Array.from(document.querySelectorAll('${scope}.bulk-preview-item')).filter(el=>getComputedStyle(el).display!=='none').map(el=>el.dataset.invoiceId)`);
const checkbox = id => `.bulk-invoice-row[data-invoice-row="${id}"] input`;
const link = id => `.bulk-invoice-row[data-invoice-row="${id}"] button`;
const master = '.bulk-invoice-select-all input';
const pdf = async (name, expectedIds) => {
    await send('Emulation.setEmulatedMedia', { media: 'print' });
    assert.deepEqual(await visible(), expectedIds, 'Print media must show only checked invoices, in original order');
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('${scope}.bulk-invoice-sidebar')).display`), 'none');
    const result = await send('Page.printToPDF', { printBackground: true, preferCSSPageSize: true });
    const buffer = Buffer.from(result.data, 'base64');
    fs.writeFileSync(path.join(artifacts, name + '.pdf'), buffer);
    assert.equal((buffer.toString('latin1').match(/\/Type \/Page\b/g) || []).length, expectedIds.length, 'One page per checked invoice, without a leading blank page');
    await send('Emulation.setEmulatedMedia', { media: '' });
};
const screenshot = async name => {
    const result = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync(path.join(artifacts, name + '.png'), Buffer.from(result.data, 'base64'));
};

try {
    await send('Runtime.enable'); await send('Page.enable');
    await send('Emulation.setDeviceMetricsOverride', { width: 1600, height: 1000, deviceScaleFactor: 1, mobile: false });
    await navigate();
    assert.deepEqual(await selected(), ['42', '43', '44']);
    assert.deepEqual(await visible(), ['44', '43', '42'], 'All selected invoices must appear vertically in one continuous preview');
    assert.equal(await evaluate(`document.querySelectorAll('${scope}.bulk-invoice-row input:checked').length`), 3);
    assert.equal(await evaluate(`document.querySelectorAll('${scope}.btn-view-order-detail').length`), 3, 'Each invoice has a View Detail button');
    assert.match(await evaluate(`document.querySelector('${scope}.bulk-preview-item[data-invoice-id="44"] .btn-view-order-detail').href`), /\/admin\/order\/detail\/44$/);
    await screenshot('desktop');
    await click(link(42));
    assert.deepEqual(await selected(), ['42', '43', '44'], 'Navigating must not change selection');
    assert.deepEqual(await visible(), ['44', '43', '42'], 'All invoices remain visible in continuous scroll view');
    assert.equal(await evaluate(`document.querySelector('${scope}.bulk-invoice-row.is-active').dataset.invoiceRow`), '42');
    await click(link(43));
    assert.equal(await evaluate(`document.querySelector('${scope}.bulk-invoice-row.is-active').dataset.invoiceRow`), '43');
    await click(checkbox(43));
    assert.deepEqual(await selected(), ['42', '44']);
    assert.deepEqual(await visible(), ['44', '43', '42'], 'Unchecking must not hide from preview');
    assert.equal(await evaluate(`document.querySelector('${scope}${master}').indeterminate`), true);
    assert.equal(await evaluate(`document.querySelector('${scope}.bulk-invoice-item[data-invoice-id="43"] .document-separator-status').textContent.trim()`), 'Not selected for printing');
    await pdf('nonadjacent', ['44', '42']);
    assert.deepEqual(await visible(), ['44', '43', '42']);
    await click(master); // Partial selection -> all.
    await click(master); // All -> none.
    assert.deepEqual(await selected(), []);
    assert.equal(await evaluate(`document.querySelector('${scope}.btn-print-all').disabled`), true);
    await evaluate(`${app}.printAll()`);
    assert.equal(await evaluate('printCalls'), 0, 'Empty selection cannot print');
    await click(link(44));
    assert.deepEqual(await selected(), []);
    await click(checkbox(42));
    await pdf('last-only', ['42']);
    await click('.btn-zoom:last-child');
    await click('.btn-print-all');
    await waitFor(`printCalls===1 && !${app}.isPrinting`);
    assert.equal(await evaluate(`${app}.scale`), 1.1, 'Printing restores zoom');
    assert.deepEqual(await selected(), ['42']);
    assert.deepEqual(await visible(), ['44', '43', '42'], 'Continuous preview maintained after print');
    await click(master);
    await pdf('all', ['44', '43', '42']);

    // A second workspace tab must get its own selection and active invoice.
    await evaluate(`window.firstPreview=document.querySelector('${scope}.bulk-preview-wrapper');MDI.openUrlInTab(${JSON.stringify('/admin/order-bulk-print/preview?document_type=invoice&count=2')},'Second preview')`);
    await waitFor(`document.querySelector('${scope}.bulk-preview-wrapper') && ${app}.invoiceIds.length===2 && ${app}.isReady`);
    await click(master);
    assert.deepEqual(await selected(), []);
    assert.equal(await evaluate('Alpine.$data(firstPreview).selectedInvoiceIds.length'), 3);
    await click(link(43));
    assert.equal(await evaluate('Alpine.$data(firstPreview).activeInvoiceId'), '44');

    // Long list, narrow viewport, and vertical continuous scroll.
    await navigate('?count=30');
    assert.equal((await visible()).length, 30, 'All 30 documents appear vertically');
    assert.equal(await evaluate(`(()=>{const list=document.querySelector('${scope}.bulk-invoice-list');return list.scrollHeight>list.clientHeight})()`), true);
    await click(link(15));
    assert.equal(await evaluate(`document.querySelector('${scope}.bulk-invoice-row.is-active').dataset.invoiceRow`), '15');
    await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: false });
    await screenshot('mobile');
    assert.equal(await evaluate('document.documentElement.scrollWidth <= innerWidth'), true, 'Narrow screens must not overflow the page');
    await click(checkbox(15));
    assert.equal((await selected()).length, 29);
    await navigate('?count=1&locale=km');
    assert.equal(await evaluate(`document.querySelector('${scope}.bulk-invoice-sidebar h2').textContent`), 'បញ្ជីវិក្កយបត្រ');
    await pdf('khmer-single', ['44']);

    // Delivery slip and consolidated variants retain their stacked preview.
    for (const type of ['delivery_slip', 'consolidated']) {
        await navigate('?type=' + type);
        assert.equal(await evaluate(`document.querySelector('${scope}.bulk-invoice-sidebar')===null`), true);
        assert.equal(await evaluate(`${app}.canPrint`), true);
        assert.equal((await visible()).length, type === 'delivery_slip' ? 3 : 1);
    }
    assert.deepEqual(errors, [], 'No JavaScript runtime errors');
    console.log('PASS: checked defaults, independent navigation, partial/all/empty selection, print-only checked invoices, PDF page counts, zoom restoration, MDI isolation, responsive layout, Khmer labels, and other preview variants.');
    console.log('Artifacts: ' + artifacts);
} finally {
    ws.close(); server.close();
}
