// Service Worker Minimaliste - PWA Inventaire v2
const CACHE_VERSION = 'v1';

// Installation du service worker
self.addEventListener('install', event => {
    console.log('[SW] Installation');
    self.skipWaiting();
});

// Activation du service worker
self.addEventListener('activate', event => {
    console.log('[SW] Activation');
    self.clients.claim();
});

// Gestion des requêtes (Network First)
self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request)
            .then(response => response)
            .catch(() => {
                // Mode offline - retourner une page simple
                return new Response('Mode offline', { status: 503 });
            })
    );
});
