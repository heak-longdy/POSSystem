const assert = require('node:assert/strict');
const { test } = require('node:test');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../resources/admin/ts/workspace/mdi.js'), 'utf8')
    .replace(/^import .*;\r?\n/gm, '');
const bundle = fs.readFileSync(path.join(__dirname, '../../public/admin-public/js/app.js'), 'utf8');
const moduleStart = bundle.indexOf('/***/ "./resources/admin/ts/workspace/mdi.js":');
const compiledSource = bundle.slice(bundle.indexOf('=> {', moduleStart) + 4, bundle.indexOf('\n/***/ }),', moduleStart));

function workspace({ scope = 'cashier', storedScope = scope, dashboard = true, currentPageAllowed = true, saved = [], compiled = false } = {}) {
    const storage = new Map([
        ['pos_workspace_tabs', JSON.stringify(saved)],
        ['pos_workspace_scope', storedScope],
    ]);
    const document = {
        title: 'ADMIN | Orders', addEventListener() {},
        querySelector: () => null, getElementById: () => null,
    };
    const window = {
        addEventListener() {}, workspaceAccess: { scope, dashboard, currentPageAllowed },
        location: { pathname: '/admin/order/list/1', href: 'http://localhost/admin/order/list/1', origin: 'http://localhost' },
    };
    const context = { window, document, URL, console, clearTimeout, setTimeout,
        fetch: async () => ({ status: 403, ok: false }),
        sessionStorage: { getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value) },
    };
    context.__webpack_exports__ = {};
    context.__webpack_require__ = name => name.includes('regenerator') ? require('@babel/runtime/regenerator') : {};
    context.__webpack_require__.r = () => {};
    context.__webpack_require__.n = module => () => module;
    vm.runInNewContext(compiled ? compiledSource : source, context);
    const instance = window.workspaceMdi();
    for (const method of ['setupKeyboardShortcuts', 'setupDirtyTracking', 'setupLinkInterceptor',
        'setupFormInterceptor', 'checkScrollState']) instance[method] = () => {};
    instance.$nextTick = callback => callback();
    instance.$el = { querySelector: () => null };
    instance.init();
    return { instance, storage };
}

const oldTab = { key: 'admin-user-list', url: '/admin/user/list/1', title: 'User Management' };

for (const [label, storedScope] of [['legacy tabs', undefined], ['another user', 'manager'], ['revoked permissions', 'old-grants']]) {
    test(`restoration discards ${label}`, () => {
        const { instance } = workspace({ storedScope: storedScope ?? '', saved: [oldTab] });
        assert.deepEqual(Array.from(instance.tabs, tab => tab.key), ['admin-dashboard', 'admin-order-list']);
    });
}

test('restoration preserves tabs for the same user and permission scope', () => {
    const saved = [{ key: 'admin-customer-list', url: '/admin/customer/list/1', title: 'Customer' }];
    const { instance, storage } = workspace({ saved });
    assert.ok(instance.tabs.some(tab => tab.key === 'admin-customer-list'));
    assert.equal(storage.get('pos_workspace_scope'), 'cashier');
});

test('Dashboard is neither restored nor recreated without permission', async () => {
    const saved = [{ key: 'admin-dashboard', url: '/admin/dashboard', isPinned: true }];
    const { instance } = workspace({ dashboard: false, saved });
    assert.deepEqual(Array.from(instance.tabs, tab => tab.key), ['admin-order-list']);
    await instance.closeAllTabs();
    assert.equal(instance.tabs.length, 0);
    assert.equal(instance.activeTabKey, '');
});

test('a forbidden AJAX response removes the unauthorized tab', async () => {
    const { instance } = workspace();
    instance.tabs.push({ ...oldTab });
    await instance.fetchAndMountTab(instance.tabs.find(tab => tab.key === oldTab.key));
    assert.equal(instance.tabs.some(tab => tab.key === oldTab.key), false);
});

test('a directly opened forbidden page is not added as a workspace tab', () => {
    const { instance } = workspace({ currentPageAllowed: false });
    assert.deepEqual(Array.from(instance.tabs, tab => tab.key), ['admin-dashboard']);
    assert.equal(instance.activeTabKey, '');
});

test('the served compiled bundle applies the same permission checks', async () => {
    const { instance } = workspace({ compiled: true, storedScope: 'manager', dashboard: false, saved: [oldTab] });
    assert.deepEqual(Array.from(instance.tabs, tab => tab.key), ['admin-order-list']);
    instance.tabs.push({ ...oldTab });
    await instance.fetchAndMountTab(instance.tabs.find(tab => tab.key === oldTab.key));
    assert.equal(instance.tabs.some(tab => tab.key === oldTab.key), false);
});
