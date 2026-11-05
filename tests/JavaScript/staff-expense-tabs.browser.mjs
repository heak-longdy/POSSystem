// Run after building assets with a disposable Chromium at BROWSER_DEBUG_URL (:9236 by default).
// Uses real Blade views, compiled CSS/JS, Alpine and workspace navigation; no application data is saved.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../../', import.meta.url));
const fixtures = fs.mkdtempSync(path.join(os.tmpdir(), 'pos-staff-expense-tabs-'));
const renderOutput = execFileSync('php', [path.join(root, 'tests/Fixtures/staff-expense-tabs.php'), fixtures], { cwd: root, encoding: 'utf8' });
const modules = ['staff-expense', 'stock-on-hand', 'staff'];
for (const module of modules) {
    for (const section of ['layout', 'script']) {
        assert.ok(fs.existsSync(path.join(fixtures, `${module}-${section}.html`)), renderOutput);
    }
}
const route = module => `/admin/${module}/list/1`;
const key = module => `admin-${module}-list`;
const pane = module => `#tab-pane-${key(module)}`;
const requests = new Map();
let origin;
const readSection = (module, section) => fs.readFileSync(path.join(fixtures, `${module}-${section}.html`), 'utf8').replaceAll('http://localhost', origin);
const page = module => `<!doctype html><html><head><meta charset="utf-8">
    <link rel="stylesheet" href="/admin-public/css/app.css">
    <link rel="stylesheet" href="/admin-public/css/select2.min.css">
    <style id="global-style">body { --global-style-test: present; }</style>
    <script src="/admin-public/js/app.js"></script>
    <script src="/admin-public/js/select2.min.js"></script>
    <script src="/admin-public/js/feather.min.js"></script>
    </head><body><div id="sidebar" style="display:none"></div><button class="toggle-sidebar" hidden></button>
    <nav>${modules.map(name => `<button id="menu-${name}" onclick="MDI.openUrlInTab('${route(name)}')">${name}</button>`).join('')}</nav>
    <div x-data="workspaceMdi()"><template x-for="tab in tabs" :key="tab.key">
        <button :id="'switch-' + tab.key" @click="switchTab(tab)" x-text="tab.title"></button>
    </template></div>
    <div id="content"><div id="workspace-viewport"><div id="tab-pane-initial" class="workspace-tab-pane active">
    ${readSection(module, 'layout')}</div></div></div>
    ${readSection(module, 'script')}
    ${module === 'stock-on-hand' ? `<style id="script-section-style" media="screen">
        .table-body .column .row { letter-spacing: 2px; }
        </style><style id="print-style" media="print">.table-body { color: red !important; }</style>
        <link id="page-stylesheet" rel="stylesheet" href="/page-only.css" media="screen">` : ''}
    <script>window.workspaceAccess={scope:'ui-test',dashboard:false,currentPageAllowed:true};
        window.pageInitializations = window.pageInitializations || {};
        pageInitializations['${module}'] = (pageInitializations['${module}'] || 0) + 1;</script>
    <script src="/admin-public/js/body.js"></script></body></html>`;
const server = http.createServer((req, res) => {
    const url = new URL(req.url, origin);
    const module = modules.find(name => url.pathname === route(name));
    if (module) {
        requests.set(module, (requests.get(module) || 0) + 1);
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        const html = page(module);
        if (url.searchParams.has('delay')) return setTimeout(() => res.end(html), 200);
        return res.end(html);
    }
    if (url.pathname === '/page-only.css') {
        res.setHeader('Content-Type', 'text/css');
        return res.end('.table-body .column .row { font-style: italic; }');
    }
    if (url.pathname === '/admin/select/stock-shop') {
        res.setHeader('Content-Type', 'application/json');
        return res.end(JSON.stringify({ data: [{ id: 11, name: 'Test shop' }] }));
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (file.startsWith(path.join(root, 'public') + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
        res.setHeader('Content-Type', file.endsWith('.css') ? 'text/css' : file.endsWith('.js') ? 'text/javascript' : 'application/octet-stream');
        return res.end(fs.readFileSync(file));
    }
    res.writeHead(404).end();
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
    assert.fail(`Timed out: ${expression}\n${JSON.stringify(errors)}`);
};
const click = async selector => {
    const point = await evaluate(`(() => {const el = document.querySelector(${JSON.stringify(selector)});
        el.scrollIntoView({block:'center',behavior:'instant'}); const r = el.getBoundingClientRect();
        const x=r.x+r.width/2,y=r.y+r.height/2;
        if (!el.contains(document.elementFromPoint(x,y))) throw Error('Click intercepted: ' + ${JSON.stringify(selector)});
        return {x,y};})()`);
    await send('Input.dispatchMouseEvent', { type: 'mousePressed', button: 'left', clickCount: 1, ...point });
    await send('Input.dispatchMouseEvent', { type: 'mouseReleased', button: 'left', clickCount: 1, ...point });
};
const ready = module => waitFor(`window.AlpineStarted && window.MDI?.activeTabKey === '${key(module)}' && document.querySelector('${pane(module)} [x-data]')?._x_dataStack && !MDI.tabs.find(t=>t.key==='${key(module)}').isLoading`);
const open = async module => { await click(`#menu-${module}`); await ready(module); };
const activate = async module => { await click(`#switch-${key(module)}`); await ready(module); };
const snapshot = () => evaluate(`(() => {
    const root = document.querySelector('${pane('staff-expense')}');
    const selectors = ['.header', '.header-action-button', '.table-wrapper', '.table-header', '.table-header .row',
        '.table-body .column', '.table-body .row', '.table-body .row span', '.badge', '.pagination'];
    const properties = ['display','width','height','paddingLeft','paddingRight','marginLeft','marginRight',
        'justifyContent','alignItems','color','backgroundColor','fontSize','fontWeight','fontStyle','letterSpacing','borderRadius'];
    return selectors.flatMap(selector => Array.from(root.querySelectorAll(selector), el => {
        const style = getComputedStyle(el); return {selector, styles: Object.fromEntries(properties.map(p=>[p,style[p]]))};
    }));
})()`);

try {
    await send('Runtime.enable'); await send('Page.enable');
    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
    await send('Page.navigate', { url: origin + route('staff-expense') });
    await ready('staff-expense');
    await evaluate('document.fonts.ready');
    const baseline = await snapshot();
    await evaluate(`window.originalStaffPane = document.querySelector('${pane('staff-expense')}');
        window.originalStaffData = originalStaffPane.querySelector('[x-data]')._x_dataStack[0];
        originalStaffPane.querySelector('[name="search"]').value = 'retained filter';
        window.documentMarker = 'same document';`);
    await open('stock-on-hand');
    await open('staff');
    const requestCount = requests.get('staff-expense');
    for (let i = 0; i < 5; i++) {
        await activate('staff-expense');
        assert.deepEqual(await snapshot(), baseline, 'Staff Expenses must keep its direct-load styles after visiting other modules');
        assert.equal(await evaluate(`document.querySelector('${pane('staff-expense')}') === originalStaffPane && originalStaffPane.querySelector('[x-data]')._x_dataStack[0] === originalStaffData`), true);
        assert.equal(await evaluate(`originalStaffPane.querySelector('[name="search"]').value`), 'retained filter');
        await activate('stock-on-hand');
        assert.equal(await evaluate(`getComputedStyle(document.querySelector('${pane('stock-on-hand')} .select2-filter-wrap')).width`), '220px');
        assert.equal(await evaluate(`document.querySelector('${pane('stock-on-hand')} #script-section-style').media`), 'screen');
        assert.equal(await evaluate(`document.querySelector('${pane('stock-on-hand')} #print-style').media`), 'print');
        await click(`${pane('stock-on-hand')} .select2-selection`);
        await waitFor(`document.querySelector('.select2-container--open .select2-results__option[aria-selected]')`);
        await waitFor(`document.activeElement.classList.contains('select2-search__field')`);
        if (i === 0) {
            await click('.select2-container--open .select2-results__option[aria-selected]');
            await evaluate(`window.originalShopSelect = $('${pane('stock-on-hand')} select.select2-hidden-accessible').data('select2'); void 0;`);
        }
        assert.equal(await evaluate(`$('${pane('stock-on-hand')} select.select2-hidden-accessible').val()`), '11');
        assert.equal(await evaluate(`$('${pane('stock-on-hand')} select.select2-hidden-accessible').data('select2') === originalShopSelect`), true);
        await activate('staff');
        assert.equal(await evaluate(`document.querySelectorAll('.select2-container--open').length`), 0);
    }
    assert.equal(requests.get('staff-expense'), requestCount, 'cached switching must not fetch Staff Expenses again');
    assert.equal(await evaluate(`pageInitializations['staff-expense']`), 1, 'cached switching must not rerun the page script');
    await open('staff-expense');
    assert.deepEqual(await snapshot(), baseline, 'menu navigation must reuse the correctly styled cached pane');
    await evaluate(`MDI.closeTab(MDI.tabs.find(t=>t.key==='${key('staff-expense')}'))`);
    await open('staff-expense');
    assert.deepEqual(await snapshot(), baseline, 'closing and reopening must mount correctly styled content');
    assert.equal(await evaluate(`document.querySelectorAll('${pane('staff-expense')} style').length`), await evaluate(`originalStaffPane.querySelectorAll('style').length`), 'mounting must not duplicate inline styles');
    assert.equal(await evaluate('documentMarker'), 'same document', 'navigation must not reload the document');
    assert.equal(await evaluate(`getComputedStyle(document.body).getPropertyValue('--global-style-test').trim()`), 'present');
    console.log('PASS: direct load, five switching cycles, cached menu activation, filters/components, interactive Select2, close/reopen, no reload.');

    // Reverse the initial page: styles emitted after the pane must belong to that initial tab too.
    await send('Page.navigate', { url: origin + route('stock-on-hand') });
    await ready('stock-on-hand');
    await open('staff-expense');
    assert.deepEqual(await snapshot(), baseline, 'the initial Stock On Hand page must not leak layout/script-section/link styles');
    await activate('stock-on-hand');
    assert.equal(await evaluate(`document.querySelector('${pane('stock-on-hand')} #page-stylesheet').media`), 'screen');
    await activate('staff-expense');
    assert.deepEqual(await snapshot(), baseline);

    // A response that completes in the background must keep its styles inactive.
    await evaluate(`MDI.closeTab(MDI.tabs.find(t=>t.key==='${key('stock-on-hand')}'))`);
    await evaluate(`MDI.openUrlInTab('${route('stock-on-hand')}?delay=1'); MDI.switchTab(MDI.tabs.find(t=>t.key==='${key('staff-expense')}'));`);
    await waitFor(`document.querySelector('${pane('stock-on-hand')} [x-data]')?._x_dataStack`);
    assert.deepEqual(await snapshot(), baseline, 'late background loads must not change the visible page styles');
    assert.deepEqual(errors, [], 'page scripts should initialize without JavaScript errors');
    console.log('PASS: reverse entry, script-section CSS, linked CSS, media preservation, late background load, no JavaScript errors.');
} finally {
    ws.close(); server.closeAllConnections(); await new Promise(resolve => server.close(resolve));
    fs.rmSync(fixtures, { recursive: true, force: true });
}
