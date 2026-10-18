const assert = require('node:assert/strict');
const { test, after } = require('node:test');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const vm = require('node:vm');
const { execFileSync } = require('node:child_process');
const ExcelJS = require('exceljs');
const moment = require('moment');

const root = path.resolve(__dirname, '../..');
const fixtures = fs.mkdtempSync(path.join(os.tmpdir(), 'pos-inventory-export-'));
execFileSync('php', [path.join(root, 'tests/Fixtures/inventory-export.php'), fixtures], { cwd: root });
after(() => fs.rmSync(fixtures, { recursive: true, force: true }));
const messages = JSON.parse(fs.readFileSync(path.join(fixtures, 'messages.json')));
const modules = ['stockIn', 'stockOut', 'stockTransfer', 'stockOnHand', 'stockMovement'];

function component(module) {
    let instance;
    const alerts = [], downloads = [], calls = [];
    const fields = { 'input[name="search"]': 'Green Tea & ទឹកតែ', 'select[name="shop_id"]': '1', 'select[name="status_type"]': 'stock_in' };
    const context = {
        Alpine: { data: (name, factory) => { instance = factory(); } },
        Axios: { get: async (url, config) => { calls.push({ url, config }); return { data: [] }; } },
        $: () => ({ find: selector => ({ val: () => fields[selector] }) }),
        ExcelJS, moment, Blob,
        saveAs: (blob, name) => downloads.push({ blob, name }),
        alert: message => alerts.push(message),
        console: { error: () => {} },
    };
    context.window = context;
    const html = fs.readFileSync(path.join(fixtures, module + '.html'), 'utf8');
    // Use one JS realm for page arrays and ExcelJS's array checks.
    for (const [, script] of html.matchAll(/<script>([\s\S]*?)<\/script>/g)) {
        vm.runInThisContext('(function(' + Object.keys(context).join(',') + ') {' + script + '})')(...Object.values(context));
    }
    assert.ok(instance, module + ' must register its actual Alpine component');
    return { instance, context, alerts, downloads, calls, fields };
}

for (const module of modules) {
    test(module + ': real Excel workbook and existing request filters', async () => {
        const { instance, context, downloads, alerts, calls, fields } = component(module);
        const item = {
            product_title: 'Green Tea & ទឹកតែ', category_title: 'Beverages', uom_title: 'Box',
            qty: 12, current_stock: 24, created_date: '18/Oct/2026 10:00 AM',
            shop_title: 'Central Shop', destination_shop_title: 'West Shop', supplier_title: 'Supplier',
            destination_title: 'Customer', request_by_title: 'Admin', stock_status_title: 'Confirmed',
            from_title: 'Central Shop', to_title: 'West Shop', remark: 'Test export',
        };
        context.Axios.get = async (url, config) => { calls.push({ url, config }); return { data: [item] }; };
        await instance.excel();
        assert.equal(instance.exportLoading, false);
        assert.equal(alerts.length, 0);
        assert.equal(downloads.length, 1);
        const routeName = module.replace(/[A-Z]/g, match => '-' + match.toLowerCase());
        assert.equal(new URL(calls[0].url).pathname, '/admin/' + routeName + '/report');
        const expected = { search: fields['input[name="search"]'], shop_id: '1' };
        if (module === 'stockMovement') expected.status_type = 'stock_in';
        else if (module !== 'stockOnHand') expected.status = '1';
        assert.deepEqual(JSON.parse(JSON.stringify(calls[0].config.params)), expected);
        assert.equal(calls[0].config.headers.Accept, 'application/json');
        assert.match(downloads[0].name, /^Stock_.*_Report_\d{4}_\d{2}_\d{2}_\d{6}\.xlsx$/);
        const workbook = new ExcelJS.Workbook();
        await workbook.xlsx.load(await downloads[0].blob.arrayBuffer());
        const sheet = workbook.worksheets[0];
        assert.equal(workbook.worksheets.length, 1);
        assert.equal(sheet.columnCount, module === 'stockOnHand' ? 8 : module === 'stockMovement' ? 10 : 11);
        assert.equal(sheet.rowCount, 4);
        assert.equal(sheet.getCell('B4').value, item.product_title);
        const qtyColumn = module === 'stockIn' ? 'G' : 'E';
        assert.equal(sheet.getCell(qtyColumn + '4').value, module === 'stockOnHand' ? 24 : 12);
        assert.equal(sheet.getCell('A1').font.size, 16);
        assert.equal(sheet.getCell('A3').border.top.style, 'thin');
        assert.equal(sheet.getCell('A4').font.size, 10);
        assert.equal(sheet.getColumn(1).width, 8);
        assert.ok(sheet.getCell('B1').isMerged);

        context.Axios.get = async () => ({ data: [] });
        await instance.excel();
        assert.equal(downloads.length, 2, 'an empty result still downloads a valid workbook');
    });

    test(module + ': failures notify the user, skip download and release loading state', async () => {
        const { instance, context, downloads, alerts } = component(module);
        const cases = [
            [{ response: { status: 404 } }, 'route_missing'],
            [{ response: { status: 403 } }, 'forbidden'],
            [{ response: { status: 401 } }, 'session_expired'],
            [{ response: { status: 419 } }, 'session_expired'],
            [{ response: { status: 500 } }, 'server_error'],
            [{ isAxiosError: true, request: {} }, 'network_error'],
            [new Error('Workbook generation failed'), 'failed'],
        ];
        for (const [error, message] of cases) {
            context.Axios.get = async () => { throw error; };
            await instance.excel();
            assert.equal(alerts.at(-1), messages[message]);
            assert.equal(instance.exportLoading, false);
        }
        for (const data of ['<!doctype html><form>Login</form>', { message: 'Unexpected response' }, null]) {
            context.Axios.get = async () => ({ data });
            await instance.excel();
            assert.equal(alerts.at(-1), messages.invalid_response);
            assert.equal(instance.exportLoading, false);
        }
        assert.equal(downloads.length, 0);
    });
}

test('missing named route is reported without making a guessed HTTP request', async () => {
    let requested = false;
    const source = fs.readFileSync(path.join(fixtures, 'missing-route.js'), 'utf8');
    const instance = vm.runInNewContext('({' + source + '})', { Axios: { get: () => { requested = true; } } });
    await assert.rejects(() => instance.requestInventoryExport({ status: '1' }), error => error.code === 'INVENTORY_ROUTE_MISSING');
    assert.equal(requested, false);
});
