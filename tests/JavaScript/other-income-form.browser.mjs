// Run with a disposable Chromium browser at BROWSER_DEBUG_URL (default :9235).
// Real Blade, Alpine, Select2 and native validation; intercept submit without saving business data.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../../', import.meta.url));
const fixtures = fs.mkdtempSync(path.join(os.tmpdir(), 'pos-other-income-'));
execFileSync('php', [path.join(root, 'tests/Fixtures/other-income-form.php'), fixtures], { cwd: root });
let origin;
const server = http.createServer((req, res) => {
    const url = new URL(req.url, origin);
    if (url.pathname.startsWith('/page/')) {
        const locale = url.pathname.split('/').pop();
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        const source = fs.readFileSync(path.join(fixtures, locale + '.html'), 'utf8').replaceAll('http://localhost', origin);
        return res.end(`<!doctype html><html lang="${locale}"><head><meta charset="utf-8">
            <link rel="stylesheet" href="/admin-public/css/app.css"><link rel="stylesheet" href="/admin-public/css/select2.min.css">
            <script src="/admin-public/js/app.js"></script><script src="/admin-public/js/select2.min.js"></script>
            <script src="/admin-public/js/jqueryUi.js"></script></head><body>${source}
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
const component = `Alpine.$data(document.querySelector('[x-data="xOtherIncomeStore"]'))`;
const submitButton = '.form-button button[type="submit"]';
const invalidControls = `Array.from(document.querySelector('form').elements).filter(el=>el.willValidate&&!el.validity.valid).map(el=>({name:el.name,model:el.getAttribute('x-model'),value:el.value,stepMismatch:el.validity.stepMismatch}))`;
const submit = async (selector = submitButton) => {
    const count = await evaluate('submissions.length');
    await click(selector);
    assert.equal(await evaluate('submissions.length'), count + 1, `Submission blocked: ${JSON.stringify(await evaluate(invalidControls))}`);
    assert.deepEqual(validationErrors, [], 'Hidden controls must not block validation');
    return evaluate('submissions.at(-1)');
};
try {
    await send('Runtime.enable'); await send('Page.enable'); await send('Log.clear'); await send('Log.enable');
    await send('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });
    for (const locale of ['en', 'km']) {
        await send('Page.navigate', { url: `${origin}/page/${locale}` });
        await waitFor(`document.documentElement.lang === '${locale}' && window.Alpine && document.querySelectorAll('.select2-container').length >= 5`);
        assert.equal(await evaluate(`document.querySelectorAll('[name=expected_amount],[name=initial_payment_amount],[name=attachment],[name=reference_no]').length`), 0);
        await evaluate(`$('#category_id').val('1').trigger('change');document.querySelector('[name=title]').value='Supplier incentive';document.querySelector('[name=received_amount]').value='100';`);

        const initial = await submit();
        assert.equal(initial.category_id, '1'); assert.equal(initial.received_amount, '100');
        assert.equal(initial.payment_method, 'Cash');
        assert.equal((await submit('[name=save_opt]')).save_opt, 'save_new');

        await click('.calc-panel-header button');
        await submit(); // Default percentage rate must not invalidate the hidden fixed-target field.
        for (const [model, rate, bonus] of [['per_unit', 0.25, 0], ['fixed', 25000, 500], ['tiered', 25000, 500], ['percentage', 2.5, 0]]) {
            await evaluate(`${component}.calc.model=${JSON.stringify(model)};${component}.calc.rate_or_target=${rate};${component}.calc.fixed_bonus=${bonus};Alpine.nextTick()`);
            const payload = await submit();
            assert.equal(payload.bonus_rate_percent, String(rate));
            assert.ok(payload.calculation_period_start); assert.ok(payload.calculation_period_end);
        }

        await evaluate(`${component}.calc.total_purchases=5000;${component}.calc.has_data=true;${component}.runCalculation();Alpine.nextTick()`);
        await click('.calc-action-bar button');
        assert.equal((await submit()).received_amount, '125.00');
        assert.equal(await evaluate('alerts.length'), 1);

        await evaluate('document.querySelector("[name=received_amount]").value=""');
        const missingAmountCount = await evaluate('submissions.length');
        await click(submitButton);
        assert.equal(await evaluate('submissions.length'), missingAmountCount);
        assert.equal(await evaluate('document.activeElement.name'), 'received_amount');
        await evaluate('document.querySelector("[name=received_amount]").value="125.00"');

        // Active controls retain native validation and can receive focus.
        await evaluate(`${component}.calc.rate_or_target=101;Alpine.nextTick()`);
        const count = await evaluate('submissions.length');
        await click(submitButton);
        assert.equal(await evaluate('submissions.length'), count);
        assert.equal(await evaluate('document.activeElement.getAttribute("x-model")'), 'calc.rate_or_target');
        assert.deepEqual(validationErrors, []);

        // Collapsing the optional calculator excludes its invalid inputs but retains audit values.
        await click('.calc-panel-header button');
        const collapsed = await submit();
        assert.equal(collapsed.bonus_rate_percent, '101');
        assert.ok(collapsed.calculation_period_start);

        // Required income fields must still prevent submission.
        await evaluate('document.querySelector("[name=title]").value=""');
        const before = await evaluate('submissions.length');
        await click(submitButton);
        assert.equal(await evaluate('submissions.length'), before);
        assert.equal(await evaluate('document.activeElement.name'), 'title');
        assert.deepEqual(validationErrors, []);
    }
    assert.deepEqual(errors, [], 'No JavaScript runtime errors');
    console.log('PASS: Khmer/English received-amount form, calculator autofill, Submit and Save & New; inactive calculator fields excluded and required validation preserved.');
} finally {
    ws.close(); server.close(); fs.rmSync(fixtures, { recursive: true, force: true });
}
