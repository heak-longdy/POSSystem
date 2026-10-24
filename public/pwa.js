(() => {
    // Installation uses the manifest. Only update an existing legacy worker;
    // fresh installations do not register a worker or cache application data.
    if (!window.isSecureContext || !('serviceWorker' in navigator)) return;

    const legacyWorkerUrl = new URL('service-worker.js', document.currentScript.src).href;

    navigator.serviceWorker.getRegistrations().then(registrations => {
        return Promise.all(registrations.filter(registration => {
            return [registration.active, registration.waiting, registration.installing]
                .some(worker => worker && worker.scriptURL === legacyWorkerUrl);
        }).map(registration => registration.update()));
    }).catch(error => {
        console.warn('Could not update the legacy POS service worker.', error);
    });
})();
