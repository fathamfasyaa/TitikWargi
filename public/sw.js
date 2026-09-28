/*
 * TitikWargi service worker.
 *
 * It only caches static files: the built CSS/JS/fonts (/build/) and the icons (/icons/).
 * Pages, report data, photos and map tiles always come from the network,
 * so people never see an old report list.
 *
 * Change CACHE_NAME when this file changes, so old caches are removed.
 */
const CACHE_NAME = 'titikwargi-static-v1';

const CACHED_PATHS = ['/build/', '/icons/'];

self.addEventListener('install', () => {
    // Use the new service worker right away.
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    // Remove caches from older versions of this file.
    event.waitUntil(
        caches
            .keys()
            .then((names) => Promise.all(names.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    const isStaticFile =
        request.method === 'GET' &&
        url.origin === self.location.origin &&
        CACHED_PATHS.some((path) => url.pathname.startsWith(path));

    if (!isStaticFile) {
        // Everything else goes to the network as usual.
        return;
    }

    // Cache first: files in /build/ have a hash in their name, so they never change.
    event.respondWith(
        caches.open(CACHE_NAME).then(async (cache) => {
            const cached = await cache.match(request);

            if (cached) {
                return cached;
            }

            const response = await fetch(request);

            if (response.ok) {
                cache.put(request, response.clone());
            }

            return response;
        }),
    );
});
