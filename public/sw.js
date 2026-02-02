/**
 * Service Worker for Pricetag PWA
 * Handles caching, offline support, and background sync
 */

const CACHE_NAME = 'pricetag-v1';
const STATIC_CACHE = 'pricetag-static-v1';
const DYNAMIC_CACHE = 'pricetag-dynamic-v1';
const IMAGE_CACHE = 'pricetag-images-v1';

// Static assets to cache on install
const STATIC_ASSETS = [
    '/',
    '/offline',
    '/assets/css/main.css',
    '/assets/js/main.js',
    '/assets/js/cart.js',
    '/assets/images/logo.svg',
    '/assets/images/placeholder.jpg',
    '/manifest.json'
];

// Cache strategies
const CACHE_STRATEGIES = {
    static: ['css', 'js', 'woff2', 'woff', 'ttf'],
    image: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'],
    network: ['api', 'checkout', 'cart/add', 'cart/remove', 'login', 'logout']
};

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => {
                return Promise.all(
                    keys.filter(key => {
                        return key !== STATIC_CACHE &&
                               key !== DYNAMIC_CACHE &&
                               key !== IMAGE_CACHE;
                    }).map(key => {
                        console.log('[SW] Removing old cache:', key);
                        return caches.delete(key);
                    })
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    const path = url.pathname;

    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip external requests
    if (url.origin !== location.origin) {
        return;
    }

    // Network-first for dynamic content
    if (shouldUseNetworkFirst(path)) {
        event.respondWith(networkFirst(event.request));
        return;
    }

    // Cache-first for images
    if (isImage(path)) {
        event.respondWith(cacheFirstImage(event.request));
        return;
    }

    // Cache-first for static assets
    if (isStaticAsset(path)) {
        event.respondWith(cacheFirst(event.request));
        return;
    }

    // Stale-while-revalidate for pages
    event.respondWith(staleWhileRevalidate(event.request));
});

// Check if URL should use network-first
function shouldUseNetworkFirst(path) {
    return CACHE_STRATEGIES.network.some(pattern => path.includes(pattern));
}

// Check if URL is an image
function isImage(path) {
    const ext = path.split('.').pop().toLowerCase();
    return CACHE_STRATEGIES.image.includes(ext);
}

// Check if URL is a static asset
function isStaticAsset(path) {
    const ext = path.split('.').pop().toLowerCase();
    return CACHE_STRATEGIES.static.includes(ext);
}

// Cache-first strategy
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        return caches.match('/offline');
    }
}

// Cache-first for images with separate cache
async function cacheFirstImage(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(IMAGE_CACHE);
            cache.put(request, response.clone());

            // Limit image cache size
            limitCacheSize(IMAGE_CACHE, 50);
        }
        return response;
    } catch (error) {
        // Return placeholder for failed images
        return caches.match('/assets/images/placeholder.jpg');
    }
}

// Network-first strategy
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        return caches.match('/offline');
    }
}

// Stale-while-revalidate strategy
async function staleWhileRevalidate(request) {
    const cached = await caches.match(request);

    const fetchPromise = fetch(request)
        .then(response => {
            if (response.ok) {
                const cache = caches.open(DYNAMIC_CACHE);
                cache.then(c => c.put(request, response.clone()));
            }
            return response;
        })
        .catch(() => null);

    return cached || fetchPromise || caches.match('/offline');
}

// Limit cache size
async function limitCacheSize(cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();

    if (keys.length > maxItems) {
        // Remove oldest items
        const deleteCount = keys.length - maxItems;
        for (let i = 0; i < deleteCount; i++) {
            await cache.delete(keys[i]);
        }
    }
}

// Background sync for cart operations
self.addEventListener('sync', event => {
    if (event.tag === 'cart-sync') {
        event.waitUntil(syncCart());
    }
});

async function syncCart() {
    const db = await openDB();
    const pendingItems = await db.getAll('pending-cart');

    for (const item of pendingItems) {
        try {
            await fetch(item.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(item.data)
            });
            await db.delete('pending-cart', item.id);
        } catch (error) {
            console.log('[SW] Cart sync failed, will retry');
        }
    }
}

// Push notifications
self.addEventListener('push', event => {
    if (!event.data) return;

    const data = event.data.json();

    const options = {
        body: data.body || 'You have a new notification',
        icon: '/assets/icons/icon-192x192.png',
        badge: '/assets/icons/badge.png',
        vibrate: [100, 50, 100],
        data: data.data || {},
        actions: data.actions || [
            { action: 'view', title: 'View' },
            { action: 'dismiss', title: 'Dismiss' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'Pricetag', options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'view' && event.notification.data.url) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    } else if (event.action === 'dismiss') {
        // Just close
    } else {
        // Default action - open app
        event.waitUntil(
            clients.matchAll({ type: 'window' })
                .then(clientList => {
                    for (const client of clientList) {
                        if (client.url === '/' && 'focus' in client) {
                            return client.focus();
                        }
                    }
                    return clients.openWindow('/');
                })
        );
    }
});

// Simple IndexedDB helper
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('pricetag-sw', 1);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            resolve({
                getAll: (store) => new Promise((res, rej) => {
                    const tx = db.transaction(store, 'readonly');
                    const req = tx.objectStore(store).getAll();
                    req.onsuccess = () => res(req.result);
                    req.onerror = () => rej(req.error);
                }),
                delete: (store, key) => new Promise((res, rej) => {
                    const tx = db.transaction(store, 'readwrite');
                    const req = tx.objectStore(store).delete(key);
                    req.onsuccess = () => res();
                    req.onerror = () => rej(req.error);
                })
            });
        };

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pending-cart')) {
                db.createObjectStore('pending-cart', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

console.log('[SW] Service Worker loaded');
