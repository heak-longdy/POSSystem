// Run against disposable Chromium at BROWSER_DEBUG_URL (default :9241), after building app.js.
// Real Blade tab bar, Laravel language middleware and compiled workspace; no database writes.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import http from 'node:http';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../../', import.meta.url));
const fixtures = JSON.parse(execFileSync('php', ['tests/Fixtures/workspace-translations.php'], { cwd: root, encoding: 'utf8' }));
assert.equal(fixtures.km.titles['/admin/dashboard'], 'ផ្ទាំងគ្រប់គ្រង');
assert.equal(fixtures.en.titles['/admin/dashboard'], 'Dashboard');
let locale = 'km', origin;
const requests = [];
const page = pathname => {
    const data = fixtures[locale], title = data.titles[pathname];
    const formPage = /\/(create|edit)/.test(pathname);
    return `<!doctype html><html lang="${locale}"><head><meta charset="utf-8"><title>ADMIN | ${title}</title>
        <link rel="stylesheet" href="/admin-public/css/app.css"><script src="/admin-public/js/app.js"></script>
        <script src="/admin-public/js/feather.min.js"></script></head><body>
        <div id="sidebar" hidden></div><button class="toggle-sidebar" hidden></button>
        <div id="content">${data.bar.replaceAll('http://localhost', origin)}
        <div id="workspace-viewport"><div id="tab-pane-initial" class="workspace-tab-pane active">
        <div class="header-name-meta" data-header-name="${formPage ? '' : title}"></div>
        ${formPage ? `<form method="POST" action="/admin/user/save/1"><div class="form-header"><h3>${title}</h3></div>
            <input name="draft" value="Original"><input name="language_preference" value="${locale}"><button type="submit">Save</button></form>` : `<p>${title}</p>`}
        <script>window.mountCounts=window.mountCounts||{};mountCounts[${JSON.stringify(pathname)}]=(mountCounts[${JSON.stringify(pathname)}]||0)+1;</script>
        </div></div></div><script src="/admin-public/js/body.js"></script>
        <script>window.initialTitles=MDI.tabs.map(t=>({key:t.key,title:t.title}));</script></body></html>`;
};
const server = http.createServer(async (req, res) => {
    const url = new URL(req.url, origin);
    if (req.method === 'POST' && url.pathname === '/admin/user/save/1') {
        let body = ''; for await (const chunk of req) body += chunk;
        locale = /name="language_preference"\r\n\r\nkm/.test(body) ? 'km' : 'en';
        res.writeHead(302, { Location: '/admin/user/list/1' }).end(); return;
    }
    if (fixtures[locale].titles[url.pathname]) {
        requests.push({ pathname: url.pathname, locale });
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        res.end(page(url.pathname)); return;
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (file.startsWith(path.join(root, 'public') + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
        res.setHeader('Content-Type', file.endsWith('.js') ? 'text/javascript' : file.endsWith('.css') ? 'text/css' : 'application/octet-stream');
        res.end(fs.readFileSync(file)); return;
    }
    res.writeHead(404).end();
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
origin = `http://127.0.0.1:${server.address().port}`;
const targets = await fetch(`${process.env.BROWSER_DEBUG_URL || 'http://127.0.0.1:9241'}/json/list`).then(r => r.json());
const ws = new WebSocket(targets.find(t => t.type === 'page').webSocketDebuggerUrl);
await new Promise(resolve => ws.addEventListener('open', resolve, { once: true }));
let sequence = 0;
const pending = new Map(), errors = [];
ws.addEventListener('message', event => {
    const msg = JSON.parse(event.data);
    if (msg.id) {
        const p = pending.get(msg.id); pending.delete(msg.id);
        msg.error ? p.reject(msg.error) : p.resolve(msg.result);
    }
    if (msg.method === 'Runtime.exceptionThrown') errors.push(msg.params.exceptionDetails);
});
const send = (method, params = {}) => new Promise((resolve, reject) => {
    const id = ++sequence; pending.set(id, { resolve, reject }); ws.send(JSON.stringify({ id, method, params }));
});
const evaluate = async expression => {
    const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    assert.ok(!result.exceptionDetails, JSON.stringify(result.exceptionDetails)); return result.result.value;
};
const waitFor = async expression => {
    for (let i = 0; i < 160; i++) {
        if (await evaluate(`Boolean(${expression})`)) return;
        await new Promise(resolve => setTimeout(resolve, 30));
    }
    assert.fail(`Timed out: ${expression}\n${JSON.stringify(errors)}`);
};
const navigate = async pathname => {
    await send('Page.navigate', { url: origin + pathname });
    await waitFor(`window.AlpineStarted && window.initialTitles && MDI.titleLocale === '${locale}'`);
};
const assertTitles = async () => {
    await waitFor(`MDI.tabs.every(t=>t.titleLocale==='${locale}' && !t.isLoading)`);
    const tabs = await evaluate('MDI.tabs.map(t=>({key:t.key,title:t.title,url:t.url}))');
    for (const tab of tabs) assert.equal(tab.title, fixtures[locale].titles[new URL(tab.url, origin).pathname], tab.key);
    const visible = await evaluate(`Array.from(document.querySelectorAll('.mdi-tab-title'), el=>el.textContent)`);
    assert.deepEqual(visible, tabs.map(t => {
        const record = new URL(t.url, origin).pathname.match(/\/(?:detail|details|view|edit)\/(\d+)\/?$/);
        return t.title + (record ? ` (${record[1]})` : '');
    }));
    const stored = await evaluate(`JSON.parse(sessionStorage.getItem('pos_workspace_tabs')).map(t=>t.title)`);
    assert.deepEqual(stored, tabs.map(t => t.title));
};
const open = async pathname => {
    await evaluate(`MDI.openUrlInTab(${JSON.stringify(pathname)})`);
    await waitFor(`MDI.activeTabKey===MDI.generateKey(${JSON.stringify(pathname)}) && !MDI.tabs.find(t=>t.key===MDI.activeTabKey).isLoading`);
};

try {
    await send('Runtime.enable'); await send('Page.enable');
    await navigate('/admin/dashboard');
    assert.deepEqual(await evaluate('initialTitles'), [{ key: 'admin-dashboard', title: 'ផ្ទាំងគ្រប់គ្រង' }]);
    await assertTitles();

    // New listing and form titles use translated headers, including detached form headings.
    await open('/admin/product/list/1');
    await open('/admin/customer/create');
    await open('/admin/customer/edit/42');
    await assertTitles();
    assert.equal(await evaluate(`workspaceTabLabel(MDI.tabs.find(t=>t.key==='admin-customer-edit-42'))`), fixtures.km.titles['/admin/customer/edit/42'] + ' (42)');

    // Legacy titles without locale metadata must be refreshed without mounting inactive pages.
    await evaluate(`const saved=JSON.parse(sessionStorage.getItem('pos_workspace_tabs')); saved.forEach(t=>{t.title='Old English title';delete t.titleLocale;}); sessionStorage.setItem('pos_workspace_tabs',JSON.stringify(saved));`);
    await navigate('/admin/dashboard');
    assert.equal((await evaluate('initialTitles'))[0].title, 'ផ្ទាំងគ្រប់គ្រង');
    await assertTitles();
    assert.deepEqual(await evaluate('mountCounts'), { '/admin/dashboard': 1 });

    // English preference on the next login/load preserves all tabs and updates labels immediately.
    locale = 'en';
    await navigate('/admin/dashboard');
    assert.equal((await evaluate('initialTitles'))[0].title, 'Dashboard');
    await assertTitles();
    assert.equal(await evaluate('MDI.tabs.length'), 4);
    const restoredRequestCount = requests.length;
    await navigate('/admin/dashboard'); await assertTitles();
    assert.equal(requests.length, restoredRequestCount + 1, 'same-language restoration needs no title requests');

    // Save a changed user language through the existing AJAX form handler.
    // A separate unsaved customer form and its cached pane must remain untouched.
    await open('/admin/customer/create');
    await evaluate(`window.draftPane=document.getElementById('tab-pane-admin-customer-create');
        draftPane.querySelector('[name=draft]').focus();draftPane.querySelector('[name=draft]').select();`);
    await send('Input.insertText', { text: 'Unsaved customer' });
    assert.equal(await evaluate(`MDI.tabs.find(t=>t.key==='admin-customer-create').isDirty`), true);
    await open('/admin/user/edit/1');
    const beforeMounts = await evaluate('mountCounts["/admin/customer/create"]');
    await evaluate(`const f=document.querySelector('#tab-pane-admin-user-edit-1 form');f.querySelector('[name=language_preference]').value='km';f.requestSubmit(f.querySelector('button'));`);
    await waitFor(`MDI.titleLocale==='km' && MDI.activeTabKey==='admin-user-list'`);
    assert.equal(locale, 'km'); await assertTitles();
    assert.equal(await evaluate(`draftPane===document.getElementById('tab-pane-admin-customer-create')`), true);
    assert.equal(await evaluate(`draftPane.querySelector('[name=draft]').value`), 'Unsaved customer');
    assert.equal(await evaluate(`MDI.tabs.find(t=>t.key==='admin-customer-create').isDirty`), true);
    assert.equal(await evaluate('mountCounts["/admin/customer/create"]'), beforeMounts);
    await open('/admin/customer/create'); await assertTitles();
    assert.equal(await evaluate('mountCounts["/admin/customer/create"]'), beforeMounts);

    // A regular AJAX page response also detects a changed preference without a full reload.
    locale = 'en';
    await evaluate(`MDI.refreshTab('admin-product-list')`);
    await waitFor(`MDI.titleLocale==='en'`); await assertTitles();
    assert.equal(await evaluate(`draftPane.querySelector('[name=draft]').value`), 'Unsaved customer');
    assert.equal(errors.length, 0, JSON.stringify(errors));
    console.log('PASS: first-render Khmer/English Dashboard, new and legacy tabs, locale restoration, AJAX language changes, cached panes and unsaved forms.');
} finally {
    ws.close(); server.closeAllConnections(); await new Promise(resolve => server.close(resolve));
}
