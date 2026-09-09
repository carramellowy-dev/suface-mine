const CACHE_NAME = 'surface-mine-v5';
const APP_SHELL = [
    '/',
    '/login',
    '/admin/rekapan',
    '/spv/rekapan',
    '/pegawai',
    '/pegawai/ritasi/create',
    '/pegawai/ritasi/riwayat',
    '/pegawai/non-ritasi/create',
    '/pegawai/non-ritasi/riwayat',
    '/pegawai/general/create',
    '/pegawai/general/riwayat',
    '/pegawai/utilization/create',
    '/offline.html',
    '/manifest.json'
];

self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(APP_SHELL)));
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(caches.keys().then(keys => Promise.all(keys.map(key => key !== CACHE_NAME ? caches.delete(key) : null))));
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    if (url.pathname.startsWith('/build/') || /\.(png|jpg|jpeg|gif|svg|ico|webp)$/.test(url.pathname)) {
        event.respondWith(caches.match(event.request).then(cache => cache || fetch(event.request).then(response => {
            const copy = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
            return response;
        })));
        return;
    }

    event.respondWith(fetch(event.request).then(response => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
        return response;
    }).catch(() => caches.match(event.request).then(cache => cache || caches.match('/offline.html'))));
});

self.addEventListener('sync', event => {
    if (event.tag === 'absensi-sync' || event.tag === 'pemantauan-sync' || event.tag === 'ritasi-sync' || event.tag === 'non-ritasi-sync' || event.tag === 'general-sync' || event.tag === 'utilization-sync') {
        event.waitUntil(self.clients.matchAll().then(clients => {
            clients.forEach(client => client.postMessage({ type: 'SYNC_OUTBOX' }));
        }));
    }
});
