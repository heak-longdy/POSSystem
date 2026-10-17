// Page components own Select2 options and bindings. Only manage shared teardown
// here; automatically enhancing every select would discard AJAX configuration.
const $ = window.jQuery;

$(document).on('select2:open.workspaceSelect2', () => {
    window.$select2FocusInputSearch();
});

window.addEventListener('workspace:tab-change', () => {
    $('select.select2-hidden-accessible').each(function () {
        const instance = $(this).data('select2');
        if (instance && instance.isOpen()) instance.close();
    });
});

// Covers closed/refreshed tabs, validation replacements and Alpine x-if content.
// A moved node remains connected and must retain its selection and handlers.
new MutationObserver(records => {
    records.forEach(record => record.removedNodes.forEach(node => {
        if (node.nodeType !== 1 || node.isConnected) return;
        $(node).find('select.select2-hidden-accessible')
            .add($(node).filter('select.select2-hidden-accessible'))
            .each(function () {
                const instance = $(this).data('select2');
                if (instance) {
                    // Select2 4.0 removes its detached dropdown wrapper on close.
                    if (instance.isOpen()) instance.close();
                    instance.destroy();
                }
            });
    }));
}).observe(document.body, { childList: true, subtree: true });
