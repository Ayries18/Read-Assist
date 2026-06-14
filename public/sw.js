self.addEventListener('fetch', function(event) {
    // Cache-first for assets, network-first for everything else
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                if (response) {
                    return response;
                }
                return fetch(event.request);
            })
    );
});

self.addEventListener('install', function(event) {
    self.skipWaiting();
});
