// Retire the former cache-first worker at its original URL. Keep this file
// available after deployment so existing registrations can receive the update.
// No fetch handler: every request uses the browser's normal network behavior.
self.addEventListener('install', event => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        // Delete only the cache created by the former worker, not other apps'
        // caches, cookies, sessions, IndexedDB, or workspace preferences.
        await caches.delete('my-cache-v1');
        await self.clients.claim();
        await self.registration.unregister();
    })());
});
