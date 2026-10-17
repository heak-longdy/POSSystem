// Run after building assets, with a disposable Chromium browser listening on
// BROWSER_DEBUG_URL (default http://127.0.0.1:9234). No application database is used.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../../', import.meta.url));
const fixtures = fs.mkdtempSync(path.join(os.tmpdir(), 'pos-select2-'));
execFileSync('php', [path.join(root, 'tests/Fixtures/select2-pages.php'), fixtures]);
const pages = [
    ['inventoryManagement-stockTransfer-create', 'xStockTransferForm', ['shop_id', 'product_id', 'to_shop_id']],
    ['inventoryManagement-stockOut-create', 'xStockOutForm', ['shop_id', 'product_id', 'to_id']],
    ['product-store', 'xProductStore', ['category_id', 'uom_id', 'status']],
    ['staff-store', 'xComponent', ['position_id', 'status']],
    ['staffExpense-store', 'xComponent', ['staff_id', 'type', 'shop_id', 'status']],
    ['customer-store', 'xComponent', ['status']],
    ['setting-invoice', 'invoiceConfig()', ['separator', 'date_format', 'digit_length', 'reset_cycle']],
    ['inventoryManagement-stockIn-index', 'xStockInIndex', ['stock_in_filter_shop_id']],
    ['inventoryManagement-stockOut-index', 'xStockOutIndex', ['stock_out_filter_shop_id']],
    ['inventoryManagement-stockTransfer-index', 'xStockTransferIndex', ['stock_transfer_filter_shop_id']],
    ['inventoryManagement-stockMovement-index', 'xStockMovement', ['stock_movement_filter_shop_id']],
    ['inventoryManagement-stockOnHand-index', 'xStockOnHand', ['stock_on_hand_filter_shop_id']],
    ['report-sales-index', 'xSalesReport', ['shop_id', 'customer_id'], 'salesFilterForm'],
    ['report-inventoryMovement-index', 'xInventoryReport', ['shop_id', 'product_id'], 'inventoryFilterForm'],
    ['report-orderTransaction-index', 'xOrderTransactionReport', ['shop_id', 'customer_id'], 'orderFilterForm'],
    ['report-staffExpense-index', 'xStaffExpenseReport', ['shop_id', 'staff_id'], 'expenseFilterForm'],
    ['report-customerRemaining-index', 'xCustomerRemainingReport', ['cr-customer', 'cr-shop']],
];
let origin;
let dependencyRequests = 0;
let workspaceDependencyRequests = 0;
const fields = ids => ids.map(id => `<select id="${id}" name="${id}" class="select2" style="width:240px"
    ${id === 'category_id' ? 'x-init="fetchSelectCategory()"' : ''}
    ${id === 'uom_id' ? 'x-init="fetchSelectUOM()"' : ''}>
    <option value=""></option><option value="11" selected>Existing selection</option>
    <option value="22">Second selection</option></select>`).join('') + '<input id="current_stock" value="0">';
function pageFields(index) {
    if (index === 0) return fs.readFileSync(path.join(fixtures, 'stock-transfer-selected.html'), 'utf8') + '<input id="current_stock" value="0">';
    if (index === 9) return fs.readFileSync(path.join(fixtures, 'stock-transfer-filter.html'), 'utf8');
    return fields(pages[index][2]);
}
function pageHtml(index, cold = false, fresh = false) {
    const [name, component, ids, form] = pages[index];
    const script = fs.readFileSync(path.join(fixtures, name + '.js'), 'utf8').replace(/http:\/\/localhost/g, origin);
    return `<!doctype html><html><head><meta charset="utf-8">
      <link rel="stylesheet" href="/public/admin-public/css/app.css">
      <link rel="stylesheet" href="/public/admin-public/css/select2.min.css">
      ${index === 9 ? '<style>' + fs.readFileSync(path.join(fixtures, 'stock-transfer-filter.css'), 'utf8') + '</style>' : ''}
      ${cold ? `<script src="/public/admin-public/js/app.js"></script>
      <script src="/public/admin-public/js/select2.min.js"></script>
      <script src="/public/admin-public/js/feather.min.js"></script>` : ''}
      <script src="/dependency.js"></script>
      ${cold ? '' : '<script src="/workspace-dependency.js' + (fresh ? '?fresh=1' : '') + '"></script>'}</head><body>
      ${cold ? '<div id="sidebar" style="display:none"></div><button class="toggle-sidebar"></button><div id="retained">' + fields(['shop_id', 'product_id', 'status']) + '</div>' : ''}
      <div id="workspace-viewport"><div id="tab-pane-${index}" class="workspace-tab-pane active">
      <div x-data="${component}"><form id="${form || 'fixture-form'}">${pageFields(index)}</form></div></div></div>
      <script>if (!document.querySelector('#tab-pane-${index} [x-data]')) throw Error('Page scripts ran before mounting');
      if (!window.fixtureDependencyReady) throw Error('Dependency race');
      ${cold ? '' : "if (!window.workspaceDependencyReady) throw Error('Workspace dependency race');"}
      ${script}
      document.addEventListener('alpine:init', () => {
        window.fixtureRegistrations = (window.fixtureRegistrations || 0) + 1;
      });</script>
      ${cold ? '<script src="/public/admin-public/js/body.js"></script>' : ''}
      </body></html>`;
}
const server = http.createServer((req, res) => {
    if (req.url.startsWith('/workspace-dependency.js')) {
        workspaceDependencyRequests++;
        return setTimeout(() => { res.setHeader('Content-Type', 'text/javascript'); res.end('window.workspaceDependencyReady = true;'); }, 120);
    }
    if (req.url === '/dependency.js') {
        dependencyRequests++;
        return setTimeout(() => { res.setHeader('Content-Type', 'text/javascript'); res.end('window.fixtureDependencyReady = true;'); }, 100);
    }
    if (req.url.startsWith('/page/')) {
        res.setHeader('Content-Type', 'text/html');
        return res.end(pageHtml(Number(req.url.split('/')[2].split('?')[0]), req.url.includes('cold'), req.url.includes('fresh')));
    }
    if (req.url.startsWith('/public/')) {
        const file = path.join(root, req.url.split('?')[0]);
        if (fs.existsSync(file)) {
            res.setHeader('Content-Type', file.endsWith('.css') ? 'text/css' : 'text/javascript');
            return res.end(fs.readFileSync(file));
        }
    }
    res.setHeader('Content-Type', 'application/json');
    res.end(JSON.stringify({ data: [{ id: 33, name: 'AJAX choice', product: { id: 33, name: 'AJAX choice' } }], current_stock: 42 }));
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
origin = `http://127.0.0.1:${server.address().port}`;
const targets = await fetch(`${process.env.BROWSER_DEBUG_URL || 'http://127.0.0.1:9234'}/json/list`).then(r => r.json());
const ws = new WebSocket(targets.find(target => target.type === 'page').webSocketDebuggerUrl);
await new Promise(resolve => ws.addEventListener('open', resolve, { once: true }));
let sequence = 0;
const pending = new Map();
const errors = [];
ws.addEventListener('message', event => {
    const message = JSON.parse(event.data);
    if (message.id) {
        const promise = pending.get(message.id);
        pending.delete(message.id);
        message.error ? promise.reject(message.error) : promise.resolve(message.result);
    }
    if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails);
});
const send = (method, params = {}) => new Promise((resolve, reject) => {
    const id = ++sequence;
    pending.set(id, { resolve, reject });
    ws.send(JSON.stringify({ id, method, params }));
});
const evaluate = async expression => {
    const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    assert.ok(!result.exceptionDetails, JSON.stringify(result.exceptionDetails));
    return result.result.value;
};
const waitFor = async expression => {
    for (let attempt = 0; attempt < 100; attempt++) {
        if (await evaluate(`Boolean(${expression})`)) return;
        await new Promise(resolve => setTimeout(resolve, 30));
    }
    assert.fail(`Timed out: ${expression}\n${JSON.stringify(errors)}`);
};
const click = async selector => {
    const point = await evaluate(`(() => { const el = document.querySelector(${JSON.stringify(selector)});
      el.scrollIntoView({block: 'center'}); const r = el.getBoundingClientRect();
      const x = r.x + r.width / 2, y = r.y + r.height / 2;
      if (!el.contains(document.elementFromPoint(x, y))) throw Error('Click intercepted: ' + ${JSON.stringify(selector)});
      return {x, y}; })()`);
    await send('Input.dispatchMouseEvent', { type: 'mousePressed', button: 'left', clickCount: 1, ...point });
    await send('Input.dispatchMouseEvent', { type: 'mouseReleased', button: 'left', clickCount: 1, ...point });
};
try {
    await send('Runtime.enable');
    await send('Network.enable');
    await send('Network.setCacheDisabled', { cacheDisabled: true });
    errors.length = 0;
    for (let index = 0; index < pages.length; index++) {
        await send('Page.navigate', { url: `${origin}/page/${index}?cold` });
        await waitFor(`window.AlpineStarted && document.querySelectorAll('#tab-pane-${index} select.select2-hidden-accessible').length === ${pages[index][2].length}`);
        assert.equal(await evaluate('document.querySelectorAll("#retained .select2-container").length'), 0, 'cold initialization must not target retained IDs');
        if (index === 0 || index === 9) {
            await waitFor(`Array.from(document.querySelectorAll('#tab-pane-${index} .select2Reset')).every(el => getComputedStyle(el).display !== 'none')`);
            assert.equal(await evaluate(`document.querySelectorAll('#tab-pane-${index} .select2Reset').length`), index === 0 ? 3 : 1);
        }
        console.log(`Cold load: ${pages[index][0]}`);
    }
    await send('Page.navigate', { url: `${origin}/page/0?cold` });
    await waitFor('window.AlpineStarted && document.querySelectorAll("#tab-pane-0 select.select2-hidden-accessible").length === 3');
    await evaluate(`window.MDI = workspaceMdi(); MDI.tabs = [{key:'0', url:'/page/0'}]; MDI.activeTabKey = '0';
      MDI.$nextTick = cb => Alpine.nextTick(cb); MDI.saveSession = () => {}; MDI.updateSidebarActive = () => {};
      window.originalShop = $('#tab-pane-0 #shop_id').data('select2'); void 0;`);
    for (let index = 1; index < pages.length; index++) {
        await evaluate(`(async () => { const tab = {key:'${index}',url:'/page/${index}'}; MDI.tabs.push(tab); MDI.activeTabKey = tab.key; await MDI.fetchAndMountTab(tab); })()`);
        await waitFor(`document.querySelectorAll('#tab-pane-${index} select.select2-hidden-accessible').length === ${pages[index][2].length}`);
        assert.equal(await evaluate(`$('#tab-pane-0 #shop_id').data('select2') === originalShop`), true, 'navigation must preserve prior instances');
        console.log(`Workspace navigation: ${pages[index][0]}`);
    }
    assert.equal(await evaluate('fixtureRegistrations'), pages.length, 'alpine:init registration runs once per page');
    assert.equal(dependencyRequests, pages.length + 1, 'fetched pages reuse the loaded dependency');
    assert.equal(workspaceDependencyRequests, 1, 'new dependencies load once and finish before page initialization');

    await evaluate(`MDI.switchTab(MDI.tabs[2]);`);
    await click('#tab-pane-2 #category_id + .select2 .select2-selection');
    await waitFor('document.querySelector(".select2-container--open .select2-results__option[aria-selected]")');
    assert.equal(await evaluate('document.activeElement.classList.contains("select2-search__field")'), true);
    await click('.select2-container--open .select2-results__option[aria-selected]');
    assert.equal(await evaluate(`Alpine.$data(document.querySelector('#tab-pane-2 [x-data]')).category.id`), 33, 'AJAX selection updates component state');
    await evaluate(`$select2Data($('#tab-pane-2 #category_id')); void 0;`);
    assert.equal(await evaluate(`Alpine.$data(document.querySelector('#tab-pane-2 [x-data]')).category.id`), '');
    assert.equal(await evaluate(`$('#tab-pane-0 #shop_id').val()`), '11');

    await evaluate(`MDI.switchTab(MDI.tabs[0]); $('#tab-pane-0 #shop_id').append(new Option('Second selection', '22')).val('22').trigger('change'); void 0;`);
    assert.equal(await evaluate(`$('#tab-pane-0 #product_id').val()`), '', 'changing shop clears dependent product');
    assert.equal(await evaluate(`$('#tab-pane-1 #product_id').val()`), '11', 'other stock form remains unchanged');

    // Exercise the actual Stock Transfer clear markup with mouse clicks.
    await evaluate(`MDI.switchTab(MDI.tabs[0]);`);
    await click('#tab-pane-0 #to_shop_id ~ .select2Reset');
    await waitFor(`getComputedStyle(document.querySelector('#tab-pane-0 #to_shop_id ~ .select2Reset')).display === 'none'`);
    assert.equal(await evaluate(`$('#tab-pane-0 #to_shop_id').val()`), '');
    assert.equal(await evaluate(`$('#tab-pane-0 #shop_id').val()`), '22');
    await click('#tab-pane-0 #product_id + .select2 .select2-selection');
    await waitFor(`document.querySelector('.select2-container--open .select2-results__option[aria-selected]')`);
    await click('.select2-container--open .select2-results__option[aria-selected]');
    await waitFor(`getComputedStyle(document.querySelector('#tab-pane-0 #product_id ~ .select2Reset')).display !== 'none'`);
    await click('#tab-pane-0 #product_id ~ .select2Reset');
    await waitFor(`getComputedStyle(document.querySelector('#tab-pane-0 #product_id ~ .select2Reset')).display === 'none'`);
    assert.equal(await evaluate(`$('#tab-pane-0 #product_id').val()`), '');
    assert.equal(await evaluate(`$('#tab-pane-0 #current_stock').val()`), '0');
    await click('#tab-pane-0 #shop_id ~ .select2Reset');
    await waitFor(`getComputedStyle(document.querySelector('#tab-pane-0 #shop_id ~ .select2Reset')).display === 'none'`);
    assert.equal(await evaluate(`$('#tab-pane-0 #shop_id').val()`), '');
    assert.equal(await evaluate(`$('#tab-pane-1 #shop_id').val()`), '11');
    assert.equal(await evaluate(`document.querySelectorAll('.select2-container--open').length`), 0, 'clear does not open dropdown');
    await evaluate(`MDI.switchTab(MDI.tabs[9]);`);
    await click('#tab-pane-9 .select2Reset');
    await waitFor(`getComputedStyle(document.querySelector('#tab-pane-9 .select2Reset')).display === 'none'`);
    assert.equal(await evaluate(`$('#tab-pane-9 #stock_transfer_filter_shop_id').val()`), '');
    assert.equal(await evaluate(`Alpine.$data(document.querySelector('#tab-pane-9 [x-data]')).shopId`), '');

    // Reuse a real component in asynchronously inserted content, then remove it
    // while open. No detached dropdown or plugin instance may survive.
    await evaluate(`const modal = document.createElement('div'); modal.id = 'dynamic'; modal.setAttribute('x-data','xStockTransferForm');
      modal.innerHTML = ${JSON.stringify(fields(pages[0][2]))}; document.body.appendChild(modal); void 0;`);
    await waitFor('document.querySelectorAll("#dynamic select.select2-hidden-accessible").length === 3');
    await evaluate(`window.removedSelect = document.querySelector('#dynamic #shop_id'); $(removedSelect).select2('open'); document.querySelector('#dynamic').remove();`);
    await waitFor('!$(removedSelect).data("select2") && !document.querySelector(".select2-container--open")');
    await evaluate(`$('#tab-pane-0 #shop_id').select2('open'); MDI.switchTab(MDI.tabs[2]);`);
    assert.equal(await evaluate('document.querySelectorAll(".select2-container--open").length'), 0);
    assert.equal(await evaluate(`$('#tab-pane-0 #shop_id').data('select2') === originalShop`), true);
    // Refresh and server validation replace content without retaining old plugins.
    await evaluate(`window.oldProduct = document.querySelector('#tab-pane-2 #category_id'); MDI.refreshTab('2');`);
    await waitFor(`document.querySelector('#tab-pane-2 #category_id') !== oldProduct && $('#tab-pane-2 #category_id').data('select2')`);
    assert.equal(await evaluate(`!!$(oldProduct).data('select2')`), false);
    await evaluate(`(async () => {
      const pane = document.querySelector('#tab-pane-2'); window.validationOld = pane.querySelector('#category_id');
      const doc = new DOMParser().parseFromString(await fetch('/page/2').then(r => r.text()), 'text/html');
      if (typeof Alpine.destroyTree === 'function') Alpine.destroyTree(pane);
      else pane.replaceChildren();
      await MDI.executeScriptsFromDoc(doc, pane, () => { pane.innerHTML = doc.querySelector('.workspace-tab-pane').innerHTML; });
      Alpine.initTree(pane);
    })()`);
    await waitFor(`$('#tab-pane-2 #category_id').data('select2') && !$(validationOld).data('select2')`);
    await evaluate(`MDI.switchTab(MDI.tabs[0]); MDI.switchTab(MDI.tabs[2]);`);
    assert.equal(await evaluate(`document.querySelectorAll('#tab-pane-2 select.select2-hidden-accessible').length`), 3);
    assert.equal(await evaluate(`document.querySelectorAll('#tab-pane-2 select + .select2-container').length`), 3);

    // A shared, delayed dependency must not make a background tab active again.
    await evaluate(`(async () => {
      MDI.tabs[1].url = '/page/1?fresh=1'; MDI.tabs[2].url = '/page/2?fresh=1';
      MDI.activeTabKey = '1'; const first = MDI.fetchAndMountTab(MDI.tabs[1]);
      if (first !== MDI.fetchAndMountTab(MDI.tabs[1])) throw Error('Duplicate tab request');
      MDI.activeTabKey = '2'; const second = MDI.fetchAndMountTab(MDI.tabs[2]);
      await Promise.all([first, second]);
    })()`);
    await waitFor(`$('#tab-pane-1 #shop_id').data('select2') && $('#tab-pane-2 #category_id').data('select2')`);
    assert.equal(workspaceDependencyRequests, 2, 'concurrent tabs share one dependency request');
    assert.equal(await evaluate(`document.querySelectorAll('.workspace-tab-pane.active').length`), 1);
    assert.equal(await evaluate(`document.querySelector('.workspace-tab-pane.active').id`), 'tab-pane-2');
    assert.equal(await evaluate(`$('#tab-pane-0 #shop_id').data('select2') === originalShop`), true);
    for (const state of ['empty', 'readonly']) {
        const html = fs.readFileSync(path.join(fixtures, `stock-transfer-${state}.html`), 'utf8');
        await evaluate(`const stateRoot = document.createElement('div'); stateRoot.id = 'stock-${state}';
          stateRoot.setAttribute('x-data', ${JSON.stringify(state === 'readonly' ? 'Object.assign(xStockTransferForm(), { isReadonly: true })' : 'xStockTransferForm')}); stateRoot.innerHTML = ${JSON.stringify(html)};
          document.body.appendChild(stateRoot); void 0;`);
        await waitFor(`document.querySelectorAll('#stock-${state} select.select2-hidden-accessible').length === 3`);
        await waitFor(`Array.from(document.querySelectorAll('#stock-${state} .select2Reset')).every(el => getComputedStyle(el).display === 'none')`);
        if (state === 'readonly') {
            assert.equal(await evaluate(`document.querySelectorAll('#stock-readonly select:disabled').length`), 3);
            await evaluate(`Alpine.$data(document.querySelector('#stock-readonly')).resetSelect('shop_id');`);
            assert.equal(await evaluate(`$('#stock-readonly #shop_id').val()`), '11');
        }
        await evaluate(`document.querySelector('#stock-${state}').remove();`);
    }
    assert.equal(errors.length, 0, JSON.stringify(errors));
    console.log('Passed: first loads, navigation, AJAX selection, focus, visible Stock Transfer clear buttons, empty/readonly states, reset, dependent fields, tab reuse, dynamic insertion, cleanup, refresh, validation replacement and concurrent navigation.');
} finally {
    ws.close();
    server.closeAllConnections();
    server.close();
    fs.rmSync(fixtures, { recursive: true, force: true });
}
