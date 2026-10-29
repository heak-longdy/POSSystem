// Run with a disposable Chromium browser at BROWSER_DEBUG_URL (default http://127.0.0.1:9234).
// Uses the real Blade form, Alpine component, ConfirmDialog and built assets; API data is isolated.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../../', import.meta.url));
const fixtures = fs.mkdtempSync(path.join(os.tmpdir(), 'pos-order-picker-'));
execFileSync('php', [path.join(root, 'tests/Fixtures/order-product-picker.php'), fixtures]);
let origin, failNext = false, slowShopPage = '';
const requests = [];
const shops = [{ id: 1, name: 'Central Shop' }, { id: 2, name: 'West Shop' }, { id: 3, name: 'Empty Shop' }];
const products = Array.from({ length: 25 }, (_, i) => ({
    id: i + 1, shop_id: 1, price: 10, discount: i === 1 ? 10 : 0.5, type: i === 1 ? 'percent' : 'usd',
    commission: 0.25, commission_type: 'usd', point: 2, current_stock: (i + 1) * 5,
    product: { id: i + 1, name: `Product ${String(i + 1).padStart(2, '0')}`, image_url: '/images/logo/default.png', price: 12 },
}));
const westProducts = products.map(item => ({
    ...item, id: item.id + 100, shop_id: 2, price: 20, current_stock: item.current_stock + 100,
    product: { ...item.product, id: item.product.id + 100, name: `West ${item.product.name}` },
}));
const server = http.createServer((req, res) => {
    const url = new URL(req.url, origin);
    if (url.pathname.startsWith('/page/')) {
        const mode = url.pathname.split('/').pop();
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        return res.end(`<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
            <link rel="stylesheet" href="/admin-public/css/app.css"><link rel="stylesheet" href="/admin-public/css/select2.min.css">
            <script src="/admin-public/js/app.js"></script><script src="/admin-public/js/feather.min.js"></script>
            <script src="/admin-public/js/select2.min.js"></script><script src="/admin-public/js/jqueryUi.js"></script>
            <script>sessionStorage.clear();</script></head><body><div id="sidebar" style="display:none"></div><button class="toggle-sidebar" style="display:none"></button>
            <main x-data="{}">${fs.readFileSync(path.join(fixtures, 'dialog.html'), 'utf8')}
            ${fs.readFileSync(path.join(fixtures, mode + '.html'), 'utf8')}</main>
            <script src="/admin-public/js/body.js"></script></body></html>`.replaceAll('http://localhost', origin));
    }
    if (url.pathname === '/admin/select/shop') {
        res.setHeader('Content-Type', 'application/json');
        return res.end(JSON.stringify({ data: shops.filter(shop => shop.name.toLowerCase().includes((url.searchParams.get('search') || '').toLowerCase())) }));
    }
    if (url.pathname === '/admin/select/product') {
        const query = Object.fromEntries(url.searchParams);
        requests.push(query);
        res.setHeader('Content-Type', 'application/json');
        if (failNext && query.page) { failNext = false; res.statusCode = 500; return res.end('{}'); }
        const catalog = query.shop_id === '1' ? products : query.shop_id === '2' ? westProducts : [];
        const matches = catalog.filter(item => item.product.name.toLowerCase().includes((query.search || '').toLowerCase()));
        const offset = (Number(query.page || 1) - 1) * 12;
        const total = matches.length;
        const pageNum = Number(query.page || 1);
        const lastPage = Math.max(1, Math.ceil(total / 12));
        const from = total === 0 ? 0 : offset + 1;
        const to = Math.min(offset + 12, total);
        const reply = () => res.end(JSON.stringify({
            data: matches.slice(offset, offset + 12),
            has_more: offset + 12 < matches.length,
            total,
            last_page: lastPage,
            current_page: pageNum,
            from,
            to,
            per_page: 12,
        }));
        // Slow an old search to exercise cancellation and response ordering.
        return setTimeout(reply, query.search === 'Product 01' || `${query.shop_id}:${query.page}` === slowShopPage ? 600 : 30);
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (file.startsWith(path.join(root, 'public') + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
        res.setHeader('Content-Type', file.endsWith('.css') ? 'text/css' : file.endsWith('.js') ? 'text/javascript' : 'image/png');
        return res.end(fs.readFileSync(file));
    }
    res.setHeader('Content-Type', 'application/json'); res.end('{"data":[]}');
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
origin = `http://127.0.0.1:${server.address().port}`;
const targets = await fetch(`${process.env.BROWSER_DEBUG_URL || 'http://127.0.0.1:9234'}/json/list`).then(r => r.json());
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
    const point = await evaluate(`(async () => { await Alpine.nextTick(); const el = document.querySelector(${JSON.stringify(selector)});
        if (!el || el.disabled) throw Error('Unavailable control: ' + ${JSON.stringify(selector)});
        el.scrollIntoView({block:'center',behavior:'instant'});
        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        const r = el.getBoundingClientRect();
        const x = r.x + r.width / 2, y = r.y + r.height / 2;
        if (!el.contains(document.elementFromPoint(x, y))) throw Error('Click intercepted: ' + ${JSON.stringify(selector)});
        return {x,y}; })()`);
    await send('Input.dispatchMouseEvent', { type: 'mousePressed', button: 'left', clickCount: 1, ...point });
    await send('Input.dispatchMouseEvent', { type: 'mouseReleased', button: 'left', clickCount: 1, ...point });
};
const input = async (selector, value) => {
    await evaluate(`(() => { const el = document.querySelector(${JSON.stringify(selector)}); if (!el) return; el.value = ${JSON.stringify(value)};
        el.dispatchEvent(new Event('input', {bubbles:true})); el.dispatchEvent(new Event('change', {bubbles:true})); })()`);
};
const order = `Alpine.$data(document.querySelector('.booking-order-ui'))`;
const picker = `Alpine.$data(document.querySelector('.order-product-picker'))`;
const modal = '.order-product-picker';
const row = index => `${modal} tbody tr:nth-of-type(${index}) input`;
const search = `${modal} input[type=search]`;
const shopSelect = `${modal} .select2-selection`;
const selectShop = async name => {
    await click(shopSelect);
    await waitFor(`document.activeElement?.classList.contains('select2-search__field')`);
    await input(`${modal} .select2-search__field`, name);
    await waitFor(`jQuery.active === 0 && document.querySelector('${modal} .select2-results__option[aria-selected]')?.textContent === ${JSON.stringify(name)}`);
    await click(`${modal} .select2-results__option[aria-selected]`);
};
const open = async () => {
    await click('.order-add-product');
    await waitFor(`document.querySelector('${modal}') && !${picker}.loading`);
    await new Promise(resolve => setTimeout(resolve, 230)); // ConfirmDialog entrance animation.
};
const close = async () => {
    await click(`${modal} .order-product-picker__close`);
    await waitFor(`!document.querySelector('${modal}')`);
};
const navigate = async mode => {
    await evaluate('window.pickerTestNavigating = true');
    await send('Page.navigate', { url: `${origin}/page/${mode}` });
    await waitFor(`!window.pickerTestNavigating && window.AlpineStarted && document.querySelector('.order-add-product') && !${order}.loading`);
};
try {
    await send('Runtime.enable');
    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
    await navigate('create');
    await open();
    assert.equal(await evaluate(`Boolean($(${picker}.$refs.pickerShop).data('select2'))`), true, 'Select2 initializes on the first opening');
    assert.equal(await evaluate(`document.querySelectorAll('${modal} .select2-selection').length`), 1);
    assert.deepEqual(await evaluate(`(() => { const s = getComputedStyle(document.querySelector('${shopSelect}')); return [s.height, s.borderRadius, s.borderColor]; })()`),
        ['43px', '7px', 'rgb(203, 213, 225)'], 'shop filter reuses the order Select2 styling');
    assert.equal(await evaluate(`${picker}.products.length`), 12);
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('${modal} .order-product-picker__product')).color`), 'rgb(15, 23, 42)', 'product names remain readable under global button styles');
    assert.equal(await evaluate(`document.querySelector('${modal} tbody tr:first-of-type .order-product-picker__no').textContent.trim()`), '1', 'first row shows Nº 1');
    assert.equal(await evaluate(`document.activeElement === document.querySelector('${search}')`), true);
    await selectShop('Central Shop');
    await click(row(1)); await click(row(2));
    assert.equal(await evaluate(`${picker}.selectedItems.length`), 2);
    fs.writeFileSync(path.join(os.tmpdir(), 'pos-order-picker-desktop.png'), Buffer.from((await send('Page.captureScreenshot')).data, 'base64'));
    await click(`${modal} .order-product-picker__pagination .next-page`);
    await waitFor(`!${picker}.loading && ${picker}.page === 2`);
    await click(row(1));
    await input(search, 'Product 25');
    await waitFor(`!${picker}.loading && ${picker}.products[0]?.product_id === 25`);
    await click(row(1));
    assert.equal(await evaluate(`${picker}.selectedItems.length`), 4, 'selections survive page and search changes');
    await click(`${modal} .order-product-picker__primary`);
    await waitFor(`!document.querySelector('${modal}') && ${order}.dataCart.length === 4`);
    assert.deepEqual(await evaluate(`${order}.dataCart.map(item => item.product_id)`), [1, 2, 13, 25]);
    assert.equal(await evaluate(`${order}.amountPaid`), 36.5, 'existing discounts and commissions are preserved');
    assert.equal(await evaluate(`document.activeElement.classList.contains('order-add-product')`), true);
    await input('[data-cart-qty]', '3');
    await input('[data-cart-price]', '9.25');
    assert.equal(await evaluate(`${order}.dataCart[0].itemData.discount`), 0.75);
    assert.equal(await evaluate(`${order}.dataCart[0].itemData.originalPrice`), 10);
    assert.equal(await evaluate(`${order}.cartLineTotal(${order}.dataCart[0])`), 27.75);
    assert.equal(await evaluate(`document.querySelector('[data-automatic-discount]').textContent`), '$0.75');
    assert.equal(await evaluate(`document.querySelectorAll('.col-discount input, .col-discount select').length`), 0);
    await open();
    assert.equal(await evaluate(`document.querySelector('${row(1)}').disabled`), true);
    await click(`${modal} thead input`);
    assert.equal(await evaluate(`${picker}.selectedItems.length`), 10, 'select page skips existing products');
    await close();
    assert.equal(await evaluate(`${order}.dataCart.length`), 4, 'cancellation does not modify the order');
    await open();
    assert.equal(await evaluate(`${picker}.selectedItems.length`), 0);
    await input(search, 'Product 01');
    await new Promise(resolve => setTimeout(resolve, 300));
    await input(search, 'Product 25');
    await waitFor(`!${picker}.loading && ${picker}.products[0]?.product_id === 25`);
    await new Promise(resolve => setTimeout(resolve, 400));
    assert.equal(await evaluate(`${picker}.products[0].product_id`), 25, 'stale response must not replace latest search');
    await input(search, 'no matches');
    await waitFor(`!${picker}.loading && !${picker}.products.length`);
    assert.equal(await evaluate(`${picker}.failed`), false);
    await close();
    failNext = true;
    await open();
    assert.equal(await evaluate(`${picker}.failed`), true);
    await click(`${modal} .order-product-picker__state button`);
    await waitFor(`!${picker}.loading && !${picker}.failed && ${picker}.products.length === 12`);
    await close();
    console.log('Create: picker behavior, quantities, editable prices and read-only automatic USD discounts passed.');

    await navigate('edit');
    const existing = await evaluate(`JSON.stringify(${order}.dataCart[0])`);
    await open(); await click(row(2));
    await click(`${modal} .order-product-picker__primary`);
    await waitFor(`!document.querySelector('${modal}') && ${order}.dataCart.length === 2`);
    assert.equal(await evaluate(`JSON.stringify(${order}.dataCart[0])`), existing, 'adding leaves saved detail ID and fields unchanged');
    assert.equal(await evaluate(`document.querySelector('[data-cart-price]').value`), '14.5');
    assert.equal(await evaluate(`document.querySelector('[data-automatic-discount]').textContent`), '$0.50');
    await input('[data-cart-price]', '14.00');
    assert.equal(await evaluate(`${order}.dataCart[0].itemData.pricingMethod`), 'auto_unit_price_v1');
    assert.equal(await evaluate(`${order}.dataCart[0].itemData.originalPrice`), 15);
    assert.equal(await evaluate(`${order}.cartLineTotal(${order}.dataCart[0])`), 42);
    assert.equal(await evaluate(`document.querySelector('[data-automatic-discount]').textContent`), '$1.00');
    await open();
    await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
    await new Promise(resolve => setTimeout(resolve, 250));
    const bounds = await evaluate(`(() => { const r = document.querySelector('${modal}').getBoundingClientRect(); return {left:r.left,right:r.right,top:r.top,bottom:r.bottom,width:innerWidth,height:innerHeight}; })()`);
    assert.ok(bounds.left >= 0 && bounds.right <= bounds.width && bounds.top >= 0 && bounds.bottom <= bounds.height, JSON.stringify(bounds));
    await click(row(3));
    fs.writeFileSync(path.join(os.tmpdir(), 'pos-order-picker-mobile.png'), Buffer.from((await send('Page.captureScreenshot')).data, 'base64'));
    await evaluate(`document.querySelector('${modal} .order-product-picker__primary').focus()`);
    await send('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Tab', code: 'Tab', windowsVirtualKeyCode: 9 });
    assert.equal(await evaluate(`document.activeElement.classList.contains('order-product-picker__close')`), true, 'Tab stays in dialog');
    await send('Emulation.setDeviceMetricsOverride', { width: 740, height: 400, deviceScaleFactor: 1, mobile: true });
    await new Promise(resolve => setTimeout(resolve, 200));
    assert.equal(await evaluate(`(() => { const r = document.querySelector('${modal} .order-product-picker__primary').getBoundingClientRect();
        return r.top >= 0 && r.bottom <= innerHeight; })()`), true, 'Add Item stays visible on a short landscape screen');
    await send('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27 });
    await waitFor(`!document.querySelector('${modal}')`);
    await send('Emulation.clearDeviceMetricsOverride');
    await open(); await click(row(3));
    await evaluate(`${order}.formData.shop_id = 2; ${picker}.addItems()`);
    assert.equal(await evaluate(`${order}.dataCart.length`), 2, 'a shop change prevents stale selections being added');
    await close();
    await evaluate(`${order}.formData.shop_id = 1`);
    await open();
    await evaluate(`window.dispatchEvent(new CustomEvent('workspace:tab-change'))`);
    await waitFor(`!document.querySelector('${modal}')`);
    // The existing shared confirmation branch still opens and runs its callback.
    await evaluate(`${order}.confirmOrderSubmit(() => window.confirmed = true)`);
    await waitFor(`document.querySelector('.dialog-form-footer button:not(.close)')`);
    await new Promise(resolve => setTimeout(resolve, 230));
    await click('.dialog-form-footer button:not(.close)');
    await waitFor('window.confirmed === true');
    console.log('Edit: saved fields retained, mobile layout, keyboard focus, shop isolation and existing ConfirmDialog passed.');

    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
    await navigate('edit');
    await open();
    await click(row(2));
    await selectShop('Central Shop');
    assert.equal(await evaluate(`${order}.dataCart.length`), 1, 'reselecting the current shop keeps the cart');
    assert.equal(await evaluate(`${picker}.selectedItems.length`), 1);
    await selectShop('West Shop');
    await waitFor(`!${picker}.loading && ${picker}.products[0]?.product_id === 101`);
    assert.equal(await evaluate(`${order}.dataCart.length`), 0, 'changing the shop clears existing cart items');
    assert.equal(await evaluate(`String(${order}.formData.shop_id)`), '2');
    assert.equal(await evaluate(`${order}.shopData.name`), 'West Shop');
    assert.equal(await evaluate(`${order}.getScopeEl('#shop_id').val()`), '2', 'sidebar shop stays synchronized');
    assert.equal(await evaluate(`${picker}.selectedItems.length`), 0, 'old shop selections are cleared');
    assert.equal(await evaluate(`${picker}.page`), 1);
    assert.equal(await evaluate(`${picker}.products.every(item => item.shop_id === 2)`), true);
    await click(row(1));
    await click(`${modal} .order-product-picker__pagination .next-page`);
    await waitFor(`!${picker}.loading && ${picker}.page === 2`);
    await click(row(1));
    await input(search, 'Product 25');
    await waitFor(`!${picker}.loading && ${picker}.products[0]?.product_id === 125`);
    await click(row(1));
    await click(`${modal} .order-product-picker__primary`);
    await waitFor(`!document.querySelector('${modal}') && ${order}.dataCart.length === 3`);
    assert.deepEqual(await evaluate(`${order}.dataCart.map(item => item.product_id)`), [101, 113, 125]);
    assert.equal(await evaluate(`${order}.dataCart[0].itemData.price`), 20, 'Add Item keeps the selected shop price');
    assert.equal(await evaluate(`${order}.dataCart[0].current_stock`), 105);
    await open();
    assert.equal(await evaluate(`document.querySelectorAll('${modal} .select2-selection').length`), 1, 'reopening does not duplicate Select2');
    assert.equal(await evaluate(`$(${picker}.$refs.pickerShop).val()`), '2');
    await selectShop('Central Shop');
    await waitFor(`!${picker}.loading && ${picker}.products[0]?.product_id === 1`);
    slowShopPage = '2:2';
    await selectShop('West Shop');
    await waitFor(`!${picker}.loading && ${picker}.products[0]?.product_id === 101`);
    await click(`${modal} .order-product-picker__pagination .next-page`);
    await waitFor(`${picker}.loading && ${picker}.page === 2`);
    await selectShop('Central Shop');
    await waitFor(`!${picker}.loading && ${picker}.products[0]?.product_id === 1`);
    await new Promise(resolve => setTimeout(resolve, 650));
    assert.equal(await evaluate(`${picker}.page`), 1, 'old prefetch cannot restore the previous shop page');
    assert.equal(await evaluate(`${picker}.products.every(item => item.shop_id === 1)`), true);
    slowShopPage = '';
    await input(search, 'Product 01');
    await new Promise(resolve => setTimeout(resolve, 300));
    await selectShop('West Shop');
    await waitFor(`!${picker}.loading && ${picker}.products[0]?.product_id === 101`);
    assert.equal(await evaluate(`${picker}.search`), 'Product 01', 'shop changes retain the search');
    assert.equal(await evaluate(`${picker}.products.length`), 1);
    await click(row(1));
    await click(`${modal} .select2Reset`);
    assert.equal(await evaluate(`${picker}.validShop`), false);
    assert.equal(await evaluate(`${picker}.products.length`), 0);
    assert.equal(await evaluate(`${picker}.selectedItems.length`), 0);
    assert.equal(await evaluate(`${order}.formData.shop_id`), null);
    assert.equal(await evaluate(`document.querySelector('${modal} .order-product-picker__primary').disabled`), true);
    await selectShop('Empty Shop');
    await waitFor(`!${picker}.loading`);
    assert.equal(await evaluate(`${picker}.products.length`), 0);
    assert.equal(await evaluate(`${picker}.failed`), false);
    await close();
    assert.equal(await evaluate(`document.querySelectorAll('.select2-dropdown').length`), 0, 'dialog teardown removes its dropdown');
    console.log('Shop filter: first-open Select2, shop search, cart reset, prices, stock, pagination, stale requests, clearing and reopening passed.');

    await navigate('automatic');
    assert.equal(await evaluate(`document.querySelector('[data-cart-price]').value`), '10.00');
    assert.equal(await evaluate(`document.querySelector('[data-original-price]').textContent.includes('$9.30')`), true);
    assert.equal(await evaluate(`document.querySelector('[data-automatic-discount]').textContent`), '$0.00');
    await click('[data-cart-price]');
    await input('[data-cart-price]', '');
    assert.equal(await evaluate(`Boolean(${order}.validateOrderBeforeSubmit().dataCarts)`), true);
    for (const [text, expected] of [['9', '9'], ['.', '9.'], ['0', '9.0'], ['0', '9.00']]) {
        await send('Input.insertText', { text });
        assert.equal(await evaluate(`document.querySelector('[data-cart-price]').value`), expected);
        assert.equal(await evaluate(`document.querySelector('[data-automatic-discount]').textContent`), '$0.30');
        assert.equal(await evaluate(`${order}.amountPaid`), 18);
    }
    await input('[data-cart-qty]', '3');
    assert.equal(await evaluate(`${order}.amountPaid`), 27);
    assert.equal(await evaluate(`${order}.roundMoney(${order}.subTotal - ${order}.total)`), 0.9);
    await input('[data-cart-price]', '9.001');
    assert.equal(await evaluate(`document.querySelector('[data-cart-price]').getAttribute('aria-invalid')`), 'true');
    assert.equal(await evaluate(`Boolean(${order}.validateOrderBeforeSubmit().dataCarts)`), true);
    await input('[data-cart-price]', '9.30');
    assert.equal(await evaluate(`document.querySelector('[data-automatic-discount]').textContent`), '$0.00');

    // Test Khmer comma decimal input and automatic normalization to dot format
    await click('[data-cart-price]');
    await input('[data-cart-price]', '');
    for (const [text, expected] of [['8', '8'], [',', '8,'], ['3', '8,3'], ['0', '8,30']]) {
        await send('Input.insertText', { text });
        assert.equal(await evaluate(`document.querySelector('[data-cart-price]').value`), expected);
        assert.equal(await evaluate(`document.querySelector('[data-automatic-discount]').textContent`), '$1.00');
        assert.equal(await evaluate(`${order}.amountPaid`), 24.9);
    }
    assert.equal(await evaluate(`Boolean(${order}.validateOrderBeforeSubmit().dataCarts)`), false);
    await evaluate(`document.querySelector('[data-cart-price]').blur()`);
    assert.equal(await evaluate(`document.querySelector('[data-cart-price]').value`), '8.30');

    await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
    await click('[data-cart-price]');
    fs.writeFileSync(path.join(os.tmpdir(), 'pos-order-automatic-discount-mobile.png'), Buffer.from((await send('Page.captureScreenshot')).data, 'base64'));
    console.log('Automatic Edit: saved markup, immutable reference, real decimal typing, quantities and invalid prices passed.');

    await navigate('locked');
    assert.equal(await evaluate(`document.querySelector('[data-cart-price]').disabled`), true);
    assert.equal(await evaluate(`document.querySelector('.order-add-product').disabled`), true);
    await evaluate(`${order}.openProductPicker()`);
    assert.equal(await evaluate(`Alpine.store('confirmDialog').show`), false);
    await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
    await navigate('no-shop');
    const before = requests.length;
    await open();
    assert.equal(await evaluate(`${picker}.validShop`), false);
    assert.equal(requests.length, before, 'no unscoped product request');
    await selectShop('West Shop');
    await waitFor(`!${picker}.loading && ${picker}.products[0]?.product_id === 101`);
    assert.equal(await evaluate(`String(${order}.formData.shop_id)`), '2', 'an initial shop can be selected inside the dialog');
    await close();
    assert.deepEqual(errors, []);
    console.log('Locked orders and no-shop state passed. No browser exceptions.');
} finally {
    ws.close(); server.close(); fs.rmSync(fixtures, { recursive: true, force: true });
}
