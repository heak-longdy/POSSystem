// Run with a disposable Chromium browser at BROWSER_DEBUG_URL (default :9235).
// Real Blade, Alpine, datepickers, Select2 and Excel downloads against isolated fixture data.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

import ExcelJS from 'exceljs';

const root = fileURLToPath(new URL('../../', import.meta.url));
const fixtures = fs.mkdtempSync(path.join(os.tmpdir(), 'pos-product-quantity-'));
execFileSync('php', [path.join(root, 'tests/Fixtures/consolidated-product-quantity.php'), fixtures], { cwd: root });
const downloads = path.join(fixtures, 'downloads');
fs.mkdirSync(downloads);
const apiData = JSON.parse(fs.readFileSync(path.join(fixtures, 'response.json')));
const requests = [];
let origin, responseStatus = 200;
const server = http.createServer((req, res) => {
    const url = new URL(req.url, origin);
    if (url.pathname.endsWith('/report')) {
        requests.push(Object.fromEntries(url.searchParams));
        res.statusCode = responseStatus;
        res.setHeader('Content-Type', 'application/json');
        return res.end(JSON.stringify(apiData));
    }
    if (url.pathname.startsWith('/page/')) {
        const locale = url.pathname.split('/').pop();
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        const source = fs.readFileSync(path.join(fixtures, locale + '.html'), 'utf8').replaceAll('http://localhost', origin).replaceAll('http:\\/\\/localhost', origin);
        return res.end(`<!doctype html><html lang="${locale}"><head><meta charset="utf-8">
            <link rel="stylesheet" href="/admin-public/css/app.css"><link rel="stylesheet" href="/admin-public/css/select2.min.css">
            <script src="/admin-public/js/app.js"></script><script src="/admin-public/js/select2.min.js"></script>
            <script src="/admin-public/js/jqueryUi.js"></script></head><body><div class="workspace-tab-pane active" id="first">${source}</div>
            <script>window.submissions=[];window.alerts=[];window.alert=message=>alerts.push(message);document.querySelector('form').addEventListener('submit',event=>{
                event.preventDefault();submissions.push(Object.fromEntries(new FormData(event.target,event.submitter)));
            });Alpine.start();</script></body></html>`);
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
    const safeExpression = `(async () => { const value = await eval(${JSON.stringify(expression)}); return value?.jquery ? null : value; })()`;
    const result = await send('Runtime.evaluate', { expression: safeExpression, returnByValue: true, awaitPromise: true });
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
const component = `Alpine.$data(document.querySelector('.consolidated-product-report'))`;
const files = () => fs.readdirSync(downloads).filter(name => name.endsWith('.xlsx') || !name.includes('.'));
try {
    await send('Runtime.discardConsoleEntries');
    await send('Runtime.enable'); await send('Page.enable');
    await send('Browser.setDownloadBehavior', { behavior: 'allowAndName', downloadPath: downloads });
    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
    for (const locale of ['en', 'km']) {
        await send('Page.navigate', { url: `${origin}/page/${locale}?shop_id=99&page=9&search=stale` });
        await waitFor(`document.documentElement.lang === '${locale}' && document.querySelectorAll('.select2-container').length === 3 && document.querySelectorAll('.hasDatepicker').length === 2`);
        assert.equal(await evaluate(`getComputedStyle(document.querySelector('.select2-selection')).height`), '43px');
        assert.equal(await evaluate(`getComputedStyle(document.querySelector('.select2-selection')).borderRadius`), '7px');

        // Real pointer clicks prove the calendar is above the report and interactive.
        await click('[name=from_date]');
        await waitFor(`document.querySelector('#ui-datepicker-div').style.display !== 'none'`);
        assert.equal(await evaluate(`getComputedStyle(document.querySelector('#ui-datepicker-div')).zIndex`), '9999');
        await evaluate(`$('.ui-datepicker-month').val('8').trigger('change');$('.ui-datepicker-year').val('2025').trigger('change')`);
        await click('#ui-datepicker-div td[data-handler="selectDay"] a');
        assert.match(await evaluate(`document.querySelector('[name=from_date]').value`), /^2025-09-\d{2}$/);
        await click('.filter-date-input:has([name=to_date])');
        await waitFor(`document.querySelector('#ui-datepicker-div').style.display !== 'none'`);
        await click('#ui-datepicker-div td[data-handler="selectDay"] a');
        assert.match(await evaluate(`document.querySelector('[name=to_date]').value`), /^2026-10-\d{2}$/);
        await evaluate(`$('[name=from_date]').val('').trigger('change')`);
        assert.equal(await evaluate(`$('[name=to_date]').datepicker('option','minDate')`), null);
        await evaluate(`$('[name=to_date]').val('').trigger('change')`);
        assert.equal(await evaluate(`$('[name=from_date]').datepicker('option','maxDate')`), null);
        await evaluate(`$('[name=from_date]').val('2026-10-01').trigger('change');$('[name=to_date]').val('2026-10-27').trigger('change')`);
        assert.equal(await evaluate(`$('[name=to_date]').datepicker('option','minDate').getDate()`), 1);
        await evaluate(`document.activeElement.blur();document.querySelector('[name=from_date]').focus()`);
        await waitFor(`document.querySelector('#ui-datepicker-div').style.display !== 'none'`);
        await evaluate(`$('[name=from_date]').datepicker('hide')`);
        assert.equal(await evaluate(`document.querySelector('#ui-datepicker-div').classList.contains('consolidated-product-datepicker')`), false);

        await click('.filter-field-wrap:has([name=shop_id]) .select2-selection');
        await waitFor(`document.querySelector('.select2-container--open .select2-search__field')`);
        await click('.select2-results__option:nth-child(2)');
        assert.ok(await evaluate(`${component}.filterValues.shop_id`));
        await click('.filter-field-wrap:has([name=shop_id]) .select2Reset');
        assert.equal(await evaluate(`${component}.filterValues.shop_id`), '');
        await evaluate(`$('[name=shop_id]').val('2').trigger('change');$('[name=category_id]').val('1').trigger('change');$('[name=product_id]').val('1').trigger('change');document.querySelector('[name=search]').value='Tea & ទឹកតែ';
            window.MDI={activeTabKey:'report',tabs:[{key:'report',url:'/admin/report/consolidated-product-quantity?shop_id=77&page=3&search=old'}]}`);
        const before = files().length;
        await click('.btn-excel-export');
        await waitFor(`${component}.exportLoading === false`);
        for (let attempt = 0; files().length === before && attempt < 100; attempt++) await new Promise(resolve => setTimeout(resolve, 30));
        assert.equal(files().length, before + 1);
        assert.deepEqual(requests.at(-1), { from_date: '2026-10-01', to_date: '2026-10-27', shop_id: '2', category_id: '1', product_id: '1', search: 'Tea & ទឹកតែ' });
        const workbook = new ExcelJS.Workbook();
        const downloaded = files().map(name => ({ name, time: fs.statSync(path.join(downloads, name)).mtimeMs })).sort((a,b) => b.time-a.time)[0].name;
        await workbook.xlsx.readFile(path.join(downloads, downloaded));
        const sheet = workbook.worksheets[0];
        assert.equal(sheet.columnCount, 7);
        assert.equal(sheet.getCell('C2').value, 'Tea & ទឹកតែ');
        assert.equal(sheet.getCell('F2').value, 2);
        assert.equal(sheet.getCell('G2').value, 12);
        assert.equal(sheet.getCell('G3').value, 12);
        assert.equal(sheet.getCell('G2').numFmt, '#,##0');
        assert.deepEqual(await evaluate('alerts'), []);
        responseStatus = 403;
        await click('.btn-excel-export');
        await waitFor(`alerts.length === 1 && ${component}.exportLoading === false`);
        responseStatus = 200;

        // Mount a fresh tab after Alpine has started; IDs, date constraints and filters stay local.
        await evaluate(`(async () => {
            const html = await fetch('/page/${locale}').then(response => response.text());
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const tab = document.createElement('div');tab.id='second';tab.className='workspace-tab-pane active';
            tab.innerHTML=parsed.querySelector('.consolidated-product-report').outerHTML;document.body.append(tab);
        })()`);
        await waitFor(`document.querySelectorAll('.hasDatepicker').length === 4 && document.querySelectorAll('.select2-container').length === 6`);
        assert.equal(await evaluate(`(() => {const ids=[...document.querySelectorAll('input[id],select[id]')].map(el=>el.id);return ids.length===new Set(ids).size})()`), true);
        await evaluate(`$('#second [name=from_date]').val('2026-10-20').trigger('change')`);
        assert.equal(await evaluate(`$('#first [name=from_date]').val()`), '2026-10-01');
        assert.equal(await evaluate(`$('#first [name=to_date]').datepicker('option','minDate').getDate()`), 1);
        await click('#second [name=from_date]');
        await click('#ui-datepicker-div td[data-handler="selectDay"] a');
        await evaluate(`document.querySelector('#second').remove()`);
        await waitFor(`document.querySelectorAll('.hasDatepicker').length === 2`);
        await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: false });
        assert.ok(await evaluate(`document.querySelector('.report-table-card').getBoundingClientRect().width <= 390`));
        await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
    }
    assert.deepEqual(errors, []);
    console.log('Consolidated quantity: calendars, Select2, tab isolation, mobile layout and native Excel downloads passed in English and Khmer.');
} finally {
    ws.close(); server.close(); fs.rmSync(fixtures, { recursive: true, force: true });
}
