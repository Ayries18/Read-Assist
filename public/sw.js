var CACHE_VERSION = 'read-assist-v1';
var STATIC_CACHE = CACHE_VERSION + '-static';
var PAGE_CACHE = CACHE_VERSION + '-pages';

var STATIC_ASSET = /\.(css|js|png|jpe?g|gif|svg|webp|ico|woff2?|ttf|mp3|ogg|wav)$/i;

self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(STATIC_CACHE).then(function(cache) {
            // Pre-cache shell + icon
            return cache.addAll([
                '/logo.png',
                '/manifest.json'
            ]).catch(function() {});
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(keys) {
            return Promise.all(
                keys.filter(function(key) {
                    return key.indexOf(CACHE_VERSION) !== 0;
                }).map(function(key) {
                    return caches.delete(key);
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', function(event) {
    var request = event.request;

    // Hanya tangani GET yang aman.
    if (request.method !== 'GET') {
        return;
    }

    var url = new URL(request.url);

    // Jangan cache request API / dinamis (progress sync, audio stream, dsb).
    if (url.pathname.indexOf('/progress/') === 0 ||
        url.pathname.indexOf('/audio-stream/') === 0) {
        return;
    }

    // 1. Static assets → cache-first (fast, offline OK).
    if (STATIC_ASSET.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then(function(cached) {
                if (cached) {
                    return cached;
                }
                return fetch(request).then(function(response) {
                    if (response && response.status === 200) {
                        var copy = response.clone();
                        caches.open(STATIC_CACHE).then(function(cache) {
                            cache.put(request, copy);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // 2. Navigasi halaman (HTML) → network-first, fallback ke cache saat offline.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).then(function(response) {
                if (response && response.status === 200) {
                    var copy = response.clone();
                    caches.open(PAGE_CACHE).then(function(cache) {
                        cache.put(request, copy);
                    });
                }
                return response;
            }).catch(function() {
                return caches.match(request).then(function(cached) {
                    return cached || caches.match('/');
                });
            })
        );
        return;
    }

    // 3. Request lain (produk lainnya) → network-only, jangan cache dinamis.
    return;
});
