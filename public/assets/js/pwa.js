/**
 * PWA Registration and Features
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

// Register service worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js', {
                scope: '/'
            });

            console.log('[PWA] Service Worker registered:', registration.scope);

            // Check for updates periodically
            setInterval(() => {
                registration.update();
            }, 60 * 60 * 1000); // Every hour

            // Handle updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        showUpdateNotification();
                    }
                });
            });

        } catch (error) {
            console.error('[PWA] Service Worker registration failed:', error);
        }
    });

    // Handle controller change (refresh when new SW takes over)
    let refreshing = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (!refreshing) {
            refreshing = true;
            window.location.reload();
        }
    });
}

// Show update notification
function showUpdateNotification() {
    const notification = document.createElement('div');
    notification.className = 'pwa-update-notification';
    notification.innerHTML = `
        <div class="pwa-update-content">
            <span>A new version is available!</span>
            <button onclick="updateApp()">Update Now</button>
        </div>
    `;
    document.body.appendChild(notification);

    // Add styles
    const style = document.createElement('style');
    style.textContent = `
        .pwa-update-notification {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            animation: slideUp 0.3s ease;
        }
        .pwa-update-content {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .pwa-update-notification button {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        .pwa-update-notification button:hover {
            background: #4338ca;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translate(-50%, 20px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
    `;
    document.head.appendChild(style);
}

// Update app
function updateApp() {
    if (navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
    }
}

// Install prompt handling
let deferredPrompt;
const installButton = document.getElementById('pwa-install-btn');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;

    // Show install button if exists
    if (installButton) {
        installButton.style.display = 'flex';
    }

    // Show install banner after 30 seconds on page
    setTimeout(() => {
        if (deferredPrompt) {
            showInstallBanner();
        }
    }, 30000);
});

// Show install banner
function showInstallBanner() {
    if (localStorage.getItem('pwa-install-dismissed')) {
        return;
    }

    const banner = document.createElement('div');
    banner.className = 'pwa-install-banner';
    banner.innerHTML = `
        <div class="pwa-install-content">
            <div class="pwa-install-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <div>
                    <strong>Install Pricetag App</strong>
                    <span>Add to home screen for quick access</span>
                </div>
            </div>
            <div class="pwa-install-actions">
                <button class="pwa-install-dismiss" onclick="dismissInstallBanner()">Not now</button>
                <button class="pwa-install-accept" onclick="installPWA()">Install</button>
            </div>
        </div>
    `;
    document.body.appendChild(banner);

    // Add styles
    if (!document.getElementById('pwa-banner-styles')) {
        const style = document.createElement('style');
        style.id = 'pwa-banner-styles';
        style.textContent = `
            .pwa-install-banner {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                padding: 16px;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
                z-index: 10000;
                animation: slideUpBanner 0.3s ease;
            }
            .pwa-install-content {
                max-width: 600px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            }
            .pwa-install-info {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .pwa-install-info svg {
                width: 32px;
                height: 32px;
                color: #4f46e5;
                flex-shrink: 0;
            }
            .pwa-install-info strong {
                display: block;
                font-size: 14px;
                color: #1e293b;
            }
            .pwa-install-info span {
                font-size: 12px;
                color: #64748b;
            }
            .pwa-install-actions {
                display: flex;
                gap: 8px;
            }
            .pwa-install-dismiss {
                background: none;
                border: none;
                color: #64748b;
                padding: 10px 16px;
                cursor: pointer;
                font-size: 14px;
            }
            .pwa-install-accept {
                background: #4f46e5;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
            }
            .pwa-install-accept:hover {
                background: #4338ca;
            }
            @keyframes slideUpBanner {
                from { transform: translateY(100%); }
                to { transform: translateY(0); }
            }
            @media (max-width: 480px) {
                .pwa-install-content {
                    flex-direction: column;
                    text-align: center;
                }
                .pwa-install-info {
                    flex-direction: column;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Install PWA
async function installPWA() {
    if (!deferredPrompt) return;

    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;

    console.log('[PWA] Install prompt outcome:', outcome);

    deferredPrompt = null;
    dismissInstallBanner();

    if (outcome === 'accepted') {
        // Track installation
        if (typeof gtag !== 'undefined') {
            gtag('event', 'app_install', { method: 'browser_prompt' });
        }
    }
}

// Dismiss install banner
function dismissInstallBanner() {
    const banner = document.querySelector('.pwa-install-banner');
    if (banner) {
        banner.remove();
    }
    localStorage.setItem('pwa-install-dismissed', Date.now());
}

// Handle app installed
window.addEventListener('appinstalled', () => {
    console.log('[PWA] App was installed');
    deferredPrompt = null;

    if (installButton) {
        installButton.style.display = 'none';
    }
});

// Online/Offline status
window.addEventListener('online', () => {
    document.body.classList.remove('offline');
    showToast('Back online!', 'success');
});

window.addEventListener('offline', () => {
    document.body.classList.add('offline');
    showToast('You are offline. Some features may not work.', 'warning');
});

// Toast notification helper
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `pwa-toast pwa-toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    // Add styles if not exists
    if (!document.getElementById('pwa-toast-styles')) {
        const style = document.createElement('style');
        style.id = 'pwa-toast-styles';
        style.textContent = `
            .pwa-toast {
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                padding: 12px 24px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 500;
                z-index: 10001;
                animation: toastIn 0.3s ease;
            }
            .pwa-toast-success { background: #10b981; color: white; }
            .pwa-toast-warning { background: #f59e0b; color: white; }
            .pwa-toast-info { background: #3b82f6; color: white; }
            @keyframes toastIn {
                from { opacity: 0; transform: translate(-50%, -20px); }
                to { opacity: 1; transform: translate(-50%, 0); }
            }
        `;
        document.head.appendChild(style);
    }

    setTimeout(() => toast.remove(), 3000);
}

// Background sync for cart
async function syncCartItem(action, productId, quantity) {
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
        const reg = await navigator.serviceWorker.ready;

        // Store pending item in IndexedDB
        const db = await openDB();
        await db.add('pending-cart', {
            action,
            productId,
            quantity,
            url: `/cart/${action}`,
            data: { product_id: productId, quantity },
            timestamp: Date.now()
        });

        await reg.sync.register('cart-sync');
    } else {
        // Fall back to normal fetch
        await fetch(`/cart/${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity })
        });
    }
}

// IndexedDB helper
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('pricetag-pwa', 1);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve({
            add: (store, data) => new Promise((res, rej) => {
                const tx = request.result.transaction(store, 'readwrite');
                const req = tx.objectStore(store).add(data);
                req.onsuccess = () => res(req.result);
                req.onerror = () => rej(req.error);
            })
        });
        request.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('pending-cart')) {
                db.createObjectStore('pending-cart', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

// Export for use
window.PWA = {
    install: installPWA,
    syncCartItem,
    showToast
};
