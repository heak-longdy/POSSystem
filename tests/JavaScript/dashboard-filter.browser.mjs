// Run with a disposable Chromium browser at BROWSER_DEBUG_URL (default :9247).
// Uses actual Dashboard Blade, Alpine, jQuery UI and compiled application styles.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../../', import.meta.url));
const fixtures = fs.mkdtempSync(path.join(os.tmpdir(), 'pos-dashboard-filter-'));
execFileSync('php', [path.join(root, 'tests/Fixtures/dashboard-filter.php'), fixtures], { cwd: root });
let origin, locale = 'en', responseStatus = 200;
const requests = [];
const server = http.createServer((req, res) => {
    const url = new URL(req.url, origin);
    if (url.pathname.endsWith('/dashboard')) {
        const params = Object.fromEntries(url.searchParams);
        requests.push(params);
        const state = (params.all_time || !params.from_date || !params.from_date.startsWith('202')) ? 'default' : params.from_date.startsWith('2025') ? 'empty' : 'filtered';
        const payload = JSON.parse(fs.readFileSync(path.join(fixtures, `${locale}-${state}-${params.product_month}.json`)));
        res.statusCode = responseStatus;
        res.setHeader('Content-Type', 'application/json');
        return setTimeout(() => res.end(JSON.stringify({ html: params.ajax === 'product_performance' ? payload.products : payload.html })), 180);
    }
    if (url.pathname.startsWith('/page/')) {
        locale = url.pathname.split('/').pop();
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        const source = fs.readFileSync(path.join(fixtures, `${locale}.html`), 'utf8').replaceAll('http://localhost', origin);
        return res.end(`<!doctype html><html lang="${locale}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="/admin-public/css/app.css"><script src="/admin-public/js/app.js"></script>
            <script src="/admin-public/js/jqueryUi.js"></script></head><body><div id="first" class="workspace-tab-pane active">${source}</div>
            <script>Alpine.start();</script></body></html>`);
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
const targets = await fetch(`${process.env.BROWSER_DEBUG_URL || 'http://127.0.0.1:9247'}/json/list`).then(r => r.json());
const ws = new WebSocket(targets.find(target => target.type === 'page').webSocketDebuggerUrl);
await new Promise(resolve => ws.addEventListener('open', resolve, { once: true }));
let sequence = 0;
const pending = new Map(), errors = [], validationErrors = [];
ws.addEventListener('message', event => {
    const message = JSON.parse(event.data);
    if (message.id) {
        const promise = pending.get(message.id); pending.delete(message.id);
        message.error ? promise.reject(message.error) : promise.resolve(message.result);
    }
    if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails);
    if (message.method === 'Log.entryAdded' && message.params.entry.text.includes('not focusable')) {
        validationErrors.push(message.params.entry.text);
    }
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
    for (let i = 0; i < 100; i++) {
        if (await evaluate(`Boolean(${expression})`)) return;
        await new Promise(resolve => setTimeout(resolve, 30));
    }
    assert.fail(`Timed out: ${expression}`);
};
const click = async selector => {
    const point = await evaluate(`(async () => {
        await Alpine.nextTick();const el=document.querySelector(${JSON.stringify(selector)});
        el.scrollIntoView({block:'center',behavior:'instant'});
        await new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)));
        const rect=el.getBoundingClientRect(), x=rect.x+rect.width/2, y=rect.y+rect.height/2;
        if(!el.contains(document.elementFromPoint(x,y))) throw Error('Click intercepted');
        return {x,y};
    })()`);
    await send('Input.dispatchMouseEvent', { type: 'mousePressed', button: 'left', clickCount: 1, ...point });
    await send('Input.dispatchMouseEvent', { type: 'mouseReleased', button: 'left', clickCount: 1, ...point });
};
const component = `Alpine.$data(document.querySelector('#first .dashboard-container'))`;
const dialog = '.dashboard-date-dialog';
const from = `${dialog} [name=from_date]`, to = `${dialog} [name=to_date]`;
const search = `${dialog} button[type=submit]`;
const reset = `${dialog} button[\\@click="resetFilter()"]`;
const open = '#first .dashboard-filter-button';
const monthSelect = '#first select[data-product-month]';
const setDates = async (start, end) => evaluate(`(() => {
    document.querySelector(${JSON.stringify(from)}).value=${JSON.stringify(start)};
    document.querySelector(${JSON.stringify(to)}).value=${JSON.stringify(end)};
    document.querySelector(${JSON.stringify(from)}).dispatchEvent(new Event('change',{bubbles:true}));
    document.querySelector(${JSON.stringify(to)}).dispatchEvent(new Event('change',{bubbles:true}));
})()`);
const amount = () => evaluate(`document.querySelector('#first .top-mini-amount').textContent`);
const chooseMonth = async value => {
    await evaluate(`document.querySelector(${JSON.stringify(monthSelect)}).value=${JSON.stringify(value)};
        document.querySelector(${JSON.stringify(monthSelect)}).dispatchEvent(new Event('change',{bubbles:true}));`);
    await waitFor(`!${component}.loading`);
};
try {
    await send('Runtime.enable'); await send('Page.enable'); await send('Log.enable');
    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
    for (const lang of ['en', 'km']) {
        await send('Page.navigate', { url: `${origin}/page/${lang}?from_date=1999-01-01&product_month=stale` });
        await waitFor(`document.documentElement.lang==='${lang}' && document.querySelectorAll('.hasDatepicker').length===2`);
        assert.equal(await amount(), '$450.00');
        await click(open);
        await click(from);
        await waitFor(`$('#ui-datepicker-div').is(':visible')`);
        assert.equal(await evaluate(`getComputedStyle(document.querySelector('#ui-datepicker-div')).zIndex`), '210300');

        // Native month/year controls and actual pointer clicks on calendar days.
        await evaluate(`$('.ui-datepicker-year').val('2026').trigger('change');$('.ui-datepicker-month').val('9').trigger('change');`);
        await click('#ui-datepicker-div td[data-month="9"] a[data-date="1"]');
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(from)}).value`), '2026-10-01');
        await click(`${dialog} .form-row:last-child .filter-date-input`);
        await evaluate(`$('.ui-datepicker-year').val('2026').trigger('change');$('.ui-datepicker-month').val('9').trigger('change');`);
        await click('#ui-datepicker-div td[data-month="9"] a[data-date="31"]');
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(to)}).value`), '2026-10-31');
        await click(from);
        await click('#ui-datepicker-div .ui-datepicker-prev');
        assert.equal(await evaluate(`$('.ui-datepicker-month').val()`), '8');
        await click('#ui-datepicker-div .ui-datepicker-next');
        await click('#ui-datepicker-div td[data-month="9"] a[data-date="1"]');
        await click(search);
        await waitFor(`${component}.loading`);
        assert.equal(await evaluate(`document.querySelector('#first [x-ref=dashboardContent]').inert`), true);
        await waitFor(`!${component}.loading && !${component}.showFilter`);
        assert.equal(await amount(), '$125.00');
        assert.match(await evaluate(`document.querySelector('#first .dashboard-filter-range').textContent`), /2026-10-01.*2026-10-31/);
        assert.deepEqual(requests.at(-1), { ajax: 'dashboard', product_month: 'all', from_date: '2026-10-01', to_date: '2026-10-31' });

        // Draft cancellation, validation and typed/cleared bounds.
        await click(open);
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(from)}).value`), '2026-10-01');
        await setDates('2026-10-20', '2026-10-10');
        const count = requests.length;
        await click(search);
        assert.equal(requests.length, count);
        assert.ok(await evaluate(`${component}.validationError`));
        await setDates('2026-02-30', '2026-10-31'); await click(search);
        assert.equal(requests.length, count);
        await setDates('', '2026-10-31');
        assert.equal(await evaluate(`$('${to}').datepicker('option','minDate')`), null);
        await click(search); assert.equal(requests.length, count);
        await setDates('2026-10-01', '');
        assert.equal(await evaluate(`$('${from}').datepicker('option','maxDate')`), null);
        await click(`${dialog} .close`);
        await click(open);
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(from)}).value`), '2026-10-01');
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(to)}).value`), '2026-10-31');
        await click(`${dialog} .dashboard-filter-close`);
        assert.equal(await evaluate(`document.querySelector('#ui-datepicker-div').classList.contains('dashboard-datepicker')`), false);

        await chooseMonth('2026-09');
        assert.equal(requests.at(-1).from_date, '2026-10-01');
        assert.equal(requests.at(-1).to_date, '2026-10-31');
        assert.equal(await evaluate(`document.querySelectorAll('#first .product-empty-state').length`), 1);
        assert.equal(await amount(), '$125.00');
        await chooseMonth('2026-10');
        assert.match(await evaluate(`document.querySelector('#first [data-product-performance-rows]').textContent`), /125.00/);

        responseStatus = 500;
        await chooseMonth('all');
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(monthSelect)}).value`), '2026-10');
        assert.ok(await evaluate(`${component}.requestError`));
        await click(open); await setDates('2025-01-01', '2025-01-01'); await click(search);
        await waitFor(`!${component}.loading`);
        assert.equal(await evaluate(`${component}.showFilter`), true);
        assert.equal(await amount(), '$125.00');
        assert.equal(await evaluate(`${component}.fromDate`), '2026-10-01');
        responseStatus = 200;
        await click(search); await waitFor(`!${component}.loading && !${component}.showFilter`);
        assert.equal(await amount(), '$0.00');
        assert.equal(await evaluate(`document.querySelectorAll('#first .timeline-empty-state, #first .product-empty-state').length`), 2);
        await click(open); await click(reset); await waitFor(`!${component}.loading && !${component}.showFilter`);
        assert.equal(await amount(), '$450.00');
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(monthSelect)}).value`), 'all');
        assert.equal(requests.at(-1).ajax, 'dashboard');
        assert.equal(requests.at(-1).product_month, 'all');
        assert.ok(requests.at(-1).from_date);
        assert.ok(requests.at(-1).to_date);
        await click(open);
        assert.ok(await evaluate(`document.querySelector(${JSON.stringify(from)}).value`));
        await click(`${dialog} .dashboard-filter-close`);
    }

    // A second MDI-style mount has independent state, unique IDs and working calendars.
    await evaluate(`(async () => {
        const html=await fetch('/page/en').then(r=>r.text());
        const doc=new DOMParser().parseFromString(html,'text/html');
        const pane=document.createElement('div');pane.id='second';pane.className='workspace-tab-pane active';
        pane.append(doc.querySelector('.content-wrapper'));document.body.append(pane);
        document.querySelector('#first').style.display='none';await Alpine.nextTick();
    })()`);
    await waitFor(`document.querySelectorAll('.hasDatepicker').length===4`);
    const ids = await evaluate(`Array.from(document.querySelectorAll('.hasDatepicker')).map(el=>el.id)`);
    assert.equal(new Set(ids).size, 4);
    await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
    await click('#second .dashboard-filter-button');
    await click('#second [name=from_date]');
    await waitFor(`$('#ui-datepicker-div').is(':visible')`);
    await evaluate(`$('.ui-datepicker-year').val('2026').trigger('change');$('.ui-datepicker-month').val('9').trigger('change');`);
    await click('#ui-datepicker-div td[data-month="9"] a[data-date="1"]');
    assert.equal(await evaluate(`document.querySelector('#first [name=from_date]').value`), '');
    await click('#second [name=to_date]');
    await evaluate(`$('.ui-datepicker-year').val('2026').trigger('change');$('.ui-datepicker-month').val('9').trigger('change');`);
    await click('#ui-datepicker-div td[data-month="9"] a[data-date="31"]');
    const bounds = await evaluate(`(() => {const r=document.querySelector('#second .dialog-container').getBoundingClientRect();return {left:r.left,right:r.right,width:innerWidth}})()`);
    assert.ok(bounds.left >= 0 && bounds.right <= bounds.width);
    await click('#second button[type=submit]');
    await waitFor(`!Alpine.$data(document.querySelector('#second .dashboard-container')).loading && !Alpine.$data(document.querySelector('#second .dashboard-container')).showFilter`);
    assert.equal(await evaluate(`document.querySelector('#second .top-mini-amount').textContent`), '$125.00');
    assert.equal(await amount(), '$450.00');
    assert.deepEqual(errors, [], 'No JavaScript runtime errors');
    console.log('PASS: English/Khmer Dashboard filter, real calendar selection/navigation, validation, loading, cancel/reset, month intersection, errors, empty results, mobile and isolated workspace mounts.');
} finally {
    ws.close(); server.close(); fs.rmSync(fixtures, { recursive: true, force: true });
}
