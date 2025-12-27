const CACHE_NAME = 'inv-v2'; // Changed version to v2 to force update
const urlsToCache = [
  '.',               // Cache root
  'index.php',
  'login.php',
  'transaction.php', // Important: The PWA entry point often needs this
  'offline.html',
  'style.css',
  'manifest.json',
  'script.js',
  'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
  'https://code.jquery.com/jquery-3.6.0.min.js',
  'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
];

self.addEventListener('install', event => {
  self.skipWaiting(); // Force new SW to take over immediately
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
});

// Delete old caches (v1) when v2 activates
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request).then(response => {
                return response || caches.match('offline.html');
            });
        })
    );
});