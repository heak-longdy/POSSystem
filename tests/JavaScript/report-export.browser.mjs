// Run against a disposable Chromium browser at BROWSER_DEBUG_URL (default :9235).
// Real Blade scripts, Alpine, Axios, ExcelJS, FileSaver and browser downloads;
// report API responses use isolated fixtures rather than application data.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import ExcelJS from 'exceljs';

const root = fileURLToPath(new URL('../../', import.meta.url));
const fixtures = fs.mkdtempSync(path.join(os.tmpdir(), 'pos-report-export-'));
const downloads = path.join(fixtures, 'downloads');
fs.mkdirSync(downloads);
execFileSync('php', [path.join(root, 'tests/Fixtures/report-export.php'), fixtures], { cwd: root });
const messages = JSON.parse(fs.readFileSync(path.join(fixtures, 'messages.json')));
const modules = {
    sales: ['xSalesReport', 'sales', [15, 13, 13]],
    orderTransaction: ['xOrderTransactionReport', 'order-transaction', [15, 13, 13]],
    inventoryMovement: ['xInventoryReport', 'inventory-movement', [13, 11, 12]],
    staffExpense: ['xStaffExpenseReport', 'staff-expense', [11, 12, 13]],
};
const row = {
    index: 1, invoice_number: 'INV-TEST', order_date_formatted: '18 Oct 2026, 10:00 AM',
    date: '2026-10-18', day_name: 'Sunday', month_name: 'October 2026', active_days: 1,
    customer_name: 'Test customer', customer_phone: '012345678', shop_name: 'Central', barber_name: 'Staff',
    items: [{ name: 'Tea & ទឹកតែ', qty: 2 }], gross_sales: 120, total_discount: 20, discount: 20,
    total_price: 100, net_sales: 100, paid_amount: 60, remaining_amount: 40,
    payment_status: 'Partial', pay_way: 'Cash', invoices_count: 1, items_qty: 2,
    paid_count: 0, partial_count: 1, pending_count: 0, avg_ticket: 100, top_pay_method: 'Cash', top_shop_name: 'Central',
    product_name: 'Tea & ទឹកតែ', category_name: 'Beverages', uom_name: 'Box',
    created_at_formatted: '18 Oct 2026', qty: 8, current_stock: 18, status: 'stock_in',
    from_title: 'Supplier', to_title: 'Central', request_by_name: 'Admin', remark: 'Test',
    stock_in_qty: 8, stock_out_qty: 2, sales_out_qty: 1, internal_out_qty: 1, transfer_qty: 3,
    net_movement: 6, top_product: 'Tea', total_transactions: 4,
    staff_name: 'Test staff', staff_phone: '012345678', position_name: 'Cashier',
    expense_date_formatted: '18 Oct 2026', type_label: 'Deduction', amount: 5, is_deduction: true,
    description: 'Test expense', created_by_name: 'Admin', status_label: 'Active', transactions_count: 2,
    salary_total: 100, bonus_total: 10, deduction_total: 5, other_total: 0, gross_total: 110,
    net_total: 105, avg_daily_expense: 105, top_staff: 'Test staff', top_shop: 'Central',
};
let origin, responseMode = 'ok';
const requests = [];
const server = http.createServer((req, res) => {
    const url = new URL(req.url, origin);
    if (url.pathname.startsWith('/page/')) {
        const module = url.pathname.split('/').pop();
        const source = fs.readFileSync(path.join(fixtures, module + '.html'), 'utf8')
            .replaceAll('http://localhost', origin).replaceAll('http:\\/\\/localhost', origin);
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        return res.end(`<!doctype html><html><head><meta charset="utf-8"><script src="/admin-public/js/app.js"></script></head>
            <body><div id="report" x-data="${modules[module][0]}"><button id="export" @click="exportExcel()" :disabled="exportLoading">Export</button></div>
            <script>window.exportAlerts=[];window.alert=message=>exportAlerts.push(message);sessionStorage.clear();</script>
            ${source}<script>Alpine.start();window.reportReady=true;</script></body></html>`);
    }
    if (url.pathname.startsWith('/admin/report/')) {
        requests.push({ path: url.pathname, params: Object.fromEntries(url.searchParams) });
        if (responseMode === 'network') return res.destroy();
        if (responseMode === 'html') {
            res.setHeader('Content-Type', 'text/html'); return res.end('<form>Login</form>');
        }
        res.setHeader('Content-Type', 'application/json');
        if (typeof responseMode === 'number') { res.statusCode = responseMode; return res.end('{}'); }
        return res.end(JSON.stringify({ rows: responseMode === 'empty' ? [] : [row] }));
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (file.startsWith(path.join(root, 'public') + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
        res.setHeader('Content-Type', 'application/javascript'); return res.end(fs.readFileSync(file));
    }
    res.statusCode = 404; res.end();
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
origin = `http://127.0.0.1:${server.address().port}`;
const targets = await fetch(`${process.env.BROWSER_DEBUG_URL || 'http://127.0.0.1:9235'}/json/list`).then(r => r.json());
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
const waitFor = async check => {
    for (let i = 0; i < 200; i++) {
        if (await check()) return;
        await new Promise(resolve => setTimeout(resolve, 25));
    }
    assert.fail('Timed out; browser errors: ' + JSON.stringify(errors));
};
const component = 'Alpine.$data(document.querySelector("#report"))';
const query = 'from_date=2026-10-01&to_date=2026-10-18&shop_id=2&search=Tea%20%26%20%E1%9E%91%E1%9E%B9%E1%9E%80%E1%9E%8F%E1%9F%82&payment_status=Partial&pay_way=Cash&item_type=product&category_id=1&product_id=3&movement_type=stock_in&request_by=4&staff_id=5&type=deduction&status=1&year=2026&from_month=10&to_month=10';
const files = () => fs.readdirSync(downloads).filter(name => !name.endsWith('.crdownload'));
try {
    await send('Runtime.enable');
    await send('Page.enable');
    await send('Browser.setDownloadBehavior', { behavior: 'allowAndName', downloadPath: downloads });
    for (const [module, [, routeName, counts]] of Object.entries(modules)) {
        // Full navigation gives every report a clean JS global scope.
        await evaluate('window.reportReady=false');
        await send('Page.navigate', { url: `${origin}/page/${module}?${query}` });
        await waitFor(() => evaluate('window.reportReady === true'));
        assert.deepEqual(await evaluate('[typeof ExcelJS.Workbook, typeof saveAs]'), ['function', 'function']);
        for (const [index, [view, grouping]] of [['daily', 'ungrouped'], ['daily', 'grouped'], ['monthly', 'grouped'], ['monthly', 'ungrouped']].entries()) {
            if (index === 3 && ['sales', 'orderTransaction'].includes(module)) continue; // These monthly pages are grouped.
            const before = files();
            await evaluate(`${component}.viewMode=${JSON.stringify(view)};${component}.grouping=${JSON.stringify(grouping)};document.querySelector('#export').click()`);
            await waitFor(() => files().length > before.length);
            await waitFor(() => evaluate(`${component}.exportLoading === false`));
            const filename = files().find(name => !before.includes(name));
            const workbook = new ExcelJS.Workbook();
            await workbook.xlsx.readFile(path.join(downloads, filename));
            const sheet = workbook.worksheets[0];
            assert.equal(sheet.columnCount, counts[index === 3 ? 0 : index]);
            assert.equal(sheet.rowCount, 2);
            assert.equal(sheet.getCell('A1').font.bold, true);
            assert.equal(sheet.getCell('A1').fill.fgColor.argb, 'FFE2E8F0');
            assert.ok(sheet.columns.slice(1).some(column => column.width >= 16));
            if (module === 'staffExpense' && grouping === 'ungrouped') assert.equal(sheet.getCell('H2').value, -5);
            if (['sales', 'orderTransaction'].includes(module)) assert.equal(sheet.getCell(grouping === 'ungrouped' ? 'K2' : 'H2').value, 100);
            assert.equal(requests.at(-1).path, `/admin/report/${routeName}/report`);
            assert.deepEqual(requests.at(-1).params, { ...Object.fromEntries(new URLSearchParams(query)), view_mode: view, grouping });
            assert.deepEqual(await evaluate('exportAlerts'), []);
        }
        responseMode = 'empty';
        const beforeEmpty = files().length;
        await evaluate(`${component}.exportExcel()`);
        await waitFor(() => files().length > beforeEmpty);
        const beforeFailures = files().length;
        for (const [mode, message] of [[404, 'route_missing'], [403, 'forbidden'], [401, 'session_expired'], [419, 'session_expired'], [500, 'server_error'], ['html', 'invalid_response'], ['network', 'network_error']]) {
            responseMode = mode;
            await evaluate(`${component}.exportExcel()`);
            assert.equal(await evaluate('exportAlerts.at(-1)'), messages[message]);
            assert.equal(await evaluate(`${component}.exportLoading`), false);
        }
        await evaluate(`window.originalExcelJS=ExcelJS;window.ExcelJS=undefined;${component}.exportExcel()`);
        assert.equal(await evaluate('exportAlerts.at(-1)'), messages.assets_missing);
        await evaluate(`window.ExcelJS=originalExcelJS;window.originalSaveAs=saveAs;window.saveAs=undefined;${component}.exportExcel()`);
        assert.equal(await evaluate('exportAlerts.at(-1)'), messages.assets_missing);
        responseMode = 'ok';
        await evaluate(`window.saveAs=originalSaveAs;window.ExcelJS={Workbook:class {constructor(){throw Error('Test workbook failure')}}};${component}.exportExcel()`);
        assert.equal(await evaluate('exportAlerts.at(-1)'), messages.failed);
        assert.equal(await evaluate(`${component}.exportLoading`), false);
        assert.equal(files().length, beforeFailures);
        responseMode = 'ok';
        console.log(module + ': direct-page downloads, modes, filters, formatting, empty results and error messages passed');
    }
    assert.deepEqual(errors, []);
    console.log('All Report Management browser export checks passed.');
} finally {
    await send('Page.navigate', { url: 'about:blank' });
    ws.close();
    await new Promise(resolve => server.close(resolve));
    fs.rmSync(fixtures, { recursive: true, force: true });
}
