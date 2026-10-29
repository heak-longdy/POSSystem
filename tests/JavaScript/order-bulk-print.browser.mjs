// Run with a disposable Chromium browser at BROWSER_DEBUG_URL (default :9236).
// Exercises the actual Blade listing, Alpine, MDI and pagination with isolated orders.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../../', import.meta.url));
const listing = '/admin/order-bulk-print';
let origin;
const server = http.createServer((req, res) => {
    const url = new URL(req.url, origin);
    if (url.pathname === `${listing}/selection`) {
        res.setHeader('Content-Type', 'application/json');
        return res.end(JSON.stringify({ success: true, ids: Array.from({ length: 55 }, (_, i) => 55 - i) }));
    }
    if (url.pathname.startsWith(`${listing}/preview`)) {
        res.setHeader('Content-Type', 'text/html');
        return res.end('<!doctype html><title>Captured print request</title><body>Print request received</body>');
    }
    if (url.pathname === listing) {
        const source = execFileSync('php', [path.join(root, 'tests/Fixtures/order-bulk-print.php'), url.href], { cwd: root, encoding: 'utf8' })
            .replaceAll('http://localhost', origin);
        const direct = url.searchParams.has('direct');
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        if (req.headers['x-mdi-partial']) {
            return res.end(`<!doctype html><title>Order Bulk Print</title><div id="content">${source}</div>`);
        }
        return res.end(`<!doctype html><html><head><meta charset="utf-8">
            <link rel="stylesheet" href="/admin-public/css/app.css"><link rel="stylesheet" href="/admin-public/css/select2.min.css">
            <script src="/admin-public/js/app.js"></script><script src="/admin-public/js/select2.min.js"></script>
            <script src="/admin-public/js/jqueryUi.js"></script></head><body>
            <main ${direct ? '' : 'x-data="workspaceMdi()"'}><div id="workspace-viewport"><div id="tab-pane-initial" class="workspace-tab-pane active">${source}</div></div></main>
            <script>
                window.workspaceAccess={scope:'bulk-print-test',dashboard:false};
                window.alerts=[];window.alert=message=>alerts.push(message);window.printRequests=[];
                Alpine.start();
                if(window.MDI){
                    const open=MDI.openUrlInTab.bind(MDI);
                    MDI.openUrlInTab=(url,...args)=>url.includes('/preview/')?printRequests.push(url):open(url,...args);
                }else{
                    document.addEventListener('click',event=>{
                        const link=event.target.closest('[s-click-link]');
                        if(link&&!link.classList.contains('disabled'))location.href=link.getAttribute('s-click-link');
                    });
                }
            </script></body></html>`);
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
const targets = await fetch(`${process.env.BROWSER_DEBUG_URL || 'http://127.0.0.1:9236'}/json/list`).then(r => r.json());
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
    const state = await evaluate(`({url:location.href,body:document.body?.innerText.slice(0,500),alpine:!!window.Alpine,ids:document.querySelector('.bulk-print-wrapper')&&Alpine.$data(document.querySelector('.bulk-print-wrapper')).currentPageIds})`);
    assert.fail(`Timed out: ${expression}\n${JSON.stringify({errors,state})}`);
};
const click = async selector => {
    const point = await evaluate(`(async () => {
        await Alpine.nextTick(); const el=document.querySelector(${JSON.stringify(selector)});
        if(!el)throw Error('Missing control: '+${JSON.stringify(selector)});
        el.scrollIntoView({block:'center',behavior:'instant'});
        await new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)));
        const rect=el.getBoundingClientRect(),x=rect.x+rect.width/2,y=rect.y+rect.height/2;
        if(!el.contains(document.elementFromPoint(x,y)))throw Error('Click intercepted: '+${JSON.stringify(selector)});
        return {x,y};
    })()`);
    await send('Input.dispatchMouseEvent', { type: 'mousePressed', button: 'left', clickCount: 1, ...point });
    await send('Input.dispatchMouseEvent', { type: 'mouseReleased', button: 'left', clickCount: 1, ...point });
};
const app = `Alpine.$data(document.querySelector('.bulk-print-wrapper'))`;
const row = id => `input[data-order-id="${id}"]`;
const ready = firstId => waitFor(`window.Alpine && document.querySelector(${JSON.stringify(row(firstId))}) && ${app}.currentPageIds?.[0]===${firstId} && document.querySelector('.select2-container')`);
const go = async (button, firstId) => { await click(`.paginationLayout2 .${button}-page`); await ready(firstId); };
const navigate = async (query = '', firstId = 105) => {
    await send('Page.navigate', { url: origin + listing + query }); await ready(firstId);
};
const selected = () => evaluate(`${app}.selectedIds.slice().sort((a,b)=>a-b)`);
const checked = id => evaluate(`(async()=>{await Alpine.nextTick();return document.querySelector(${JSON.stringify(row(id))}).checked})()`);
const header = () => evaluate(`(async()=>{await Alpine.nextTick();const el=document.querySelector('.bulk-header-checkbox');return [el.checked,el.indeterminate]})()`);
const printIds = url => new URL(url, origin).searchParams.get('order_ids').split(',').map(Number).sort((a,b)=>a-b);

try {
    await send('Runtime.enable'); await send('Page.enable');
    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });
    await navigate();
    await evaluate('sessionStorage.clear()');
    await click(row(105)); await click(row(104));
    assert.deepEqual(await header(), [false, true]);
    await go('next', 55); await click(row(55));
    await go('prev', 105);
    assert.deepEqual(await selected(), [55, 104, 105]);
    assert.equal(await checked(105), true); assert.equal(await checked(104), true);
    assert.deepEqual(await header(), [false, true]);
    await go('next', 55); assert.equal(await checked(55), true);
    await click(row(55)); await go('last', 5); await click(row(5));
    assert.deepEqual(await selected(), [5, 104, 105]);
    for (const type of ['invoice', 'delivery', 'consolidated']) {
        await click(`.btn-print-${type}`);
        assert.deepEqual(printIds(await evaluate('printRequests.at(-1)')), [5, 104, 105]);
    }
    assert.equal(await evaluate('printRequests.length'), 3);
    assert.match(await evaluate("document.querySelector('.selection-count-badge').textContent"), /3/);

    // Clear removes records from every page; select/deselect all affects only the current page.
    await click('.btn-clear-selection'); await go('first', 105);
    assert.deepEqual(await selected(), []); assert.equal(await checked(105), false);
    await click('.bulk-header-checkbox'); await go('next', 55); await click('.bulk-header-checkbox');
    assert.equal((await selected()).length, 100);
    await go('last', 5); await click(row(5));
    assert.equal((await selected()).length, 100);
    assert.equal(await evaluate('alerts.length'), 1);
    await go('prev', 55); await go('last', 5); assert.equal(await checked(5), false);
    await go('prev', 55); assert.deepEqual(await header(), [true, false]);
    await click('.bulk-header-checkbox'); assert.equal((await selected()).length, 50);
    await go('first', 105); assert.deepEqual(await header(), [true, false]);
    await click('.btn-clear-selection'); await go('next', 55); assert.deepEqual(await selected(), []);

    // Existing filter/status resets and Select All Matching semantics remain intact.
    await click(row(55)); await click('.status-pill[href*="status=Paid"]'); await ready(55);
    await waitFor(`${app}.selectionContext[0]==='Paid'`);
    assert.deepEqual(await selected(), []);
    await click('.bulk-header-checkbox'); await click('.btn-select-all-matching');
    await waitFor(`${app}.selectedIds.length===55`);
    await go('next', 5); assert.deepEqual(await header(), [true, false]);
    assert.equal(await evaluate(`${app}.allMatchingSelected`), true);
    await click(row(5)); await go('prev', 55);
    assert.equal((await selected()).length, 54); assert.equal(await evaluate(`${app}.allMatchingSelected`), false);
    await click('.btn-filter-apply');
    await waitFor(`document.querySelector('.bulk-print-wrapper') && ${app}.selectedIds.length===0`);
    await ready(55); await click(row(55)); await click('.btn-filter-reset');
    await waitFor(`document.querySelector('.bulk-print-wrapper') && ${app}.selectedIds.length===0`);

    // A differing applied-filter context cannot restore stale selections, even with a stale top-level URL.
    await ready(55); await click(row(55));
    await evaluate(`history.replaceState({},'',${JSON.stringify(listing + '?status=all')});MDI.openUrlInTab(${JSON.stringify(listing + '?status=Paid&search=test')})`);
    await waitFor(`document.querySelector('.bulk-print-wrapper') && ${app}.selectionContext[5]==='test'`);
    assert.deepEqual(await selected(), []);
    await click(row(55));
    await navigate('?status=Paid&search=test&user=2', 55); assert.deepEqual(await selected(), []);

    // Full-page navigation uses the same selection, including all IDs in the print request.
    await evaluate('sessionStorage.clear()'); await navigate('?direct=1');
    await click(row(105)); await go('next', 55); await click(row(55));
    await go('prev', 105); assert.equal(await checked(105), true);
    assert.deepEqual(await selected(), [55, 105]);
    await click('.btn-print-invoice'); await waitFor("location.pathname.includes('/preview/invoice')");
    assert.deepEqual(printIds(await evaluate('location.href')), [55, 105]);

    // Corrupt or unavailable storage must not break the existing page controls.
    await navigate('?direct=1');
    await evaluate(`sessionStorage.setItem(${app}.selectionStorageKey,'invalid JSON')`);
    await navigate('?direct=1'); assert.deepEqual(await selected(), []);
    await evaluate(`Storage.prototype.setItem=()=>{throw Error('storage unavailable')}`);
    await click(row(105)); assert.deepEqual(await selected(), [105]);
    assert.deepEqual(errors, [], 'No JavaScript runtime errors');
    console.log('PASS: MDI/full-page pagination, restored checkboxes/counts, all print actions, deselection/clear, page selection/limits, matching selection, filter and user isolation.');
} finally {
    ws.close(); server.close();
}
