<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Offline | <?= config('app.name', 'Pricetag') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .offline-container {
            text-align: center;
            background: white;
            padding: 48px 40px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 400px;
            width: 100%;
        }

        .offline-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            color: #4f46e5;
        }

        .offline-icon svg {
            width: 100%;
            height: 100%;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .offline-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn svg {
            width: 20px;
            height: 20px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #64748b;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            animation: pulse 2s infinite;
        }

        .status-dot.online {
            background: #22c55e;
            animation: none;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .cached-pages {
            margin-top: 24px;
            text-align: left;
        }

        .cached-pages h3 {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .cached-pages ul {
            list-style: none;
        }

        .cached-pages li {
            margin-bottom: 8px;
        }

        .cached-pages a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 8px;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s;
        }

        .cached-pages a:hover {
            background: #f1f5f9;
        }

        .cached-pages a svg {
            width: 16px;
            height: 16px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <line x1="1" y1="1" x2="23" y2="23"></line>
                <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path>
                <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path>
                <path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path>
                <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path>
                <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                <line x1="12" y1="20" x2="12.01" y2="20"></line>
            </svg>
        </div>

        <h1>You're Offline</h1>
        <p>It looks like you've lost your internet connection. Check your connection and try again.</p>

        <div class="offline-actions">
            <button class="btn btn-primary" onclick="location.reload()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                Try Again
            </button>
            <a href="/" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Go to Homepage
            </a>
        </div>

        <div id="cachedPages" class="cached-pages" style="display: none;">
            <h3>Available Offline:</h3>
            <ul id="cachedList"></ul>
        </div>

        <div class="status-indicator">
            <span class="status-dot" id="statusDot"></span>
            <span id="statusText">No connection</span>
        </div>
    </div>

    <script>
        // Check connection status
        function updateStatus() {
            const dot = document.getElementById('statusDot');
            const text = document.getElementById('statusText');

            if (navigator.onLine) {
                dot.classList.add('online');
                text.textContent = 'Connection restored';
                setTimeout(() => location.reload(), 1000);
            } else {
                dot.classList.remove('online');
                text.textContent = 'No connection';
            }
        }

        window.addEventListener('online', updateStatus);
        window.addEventListener('offline', updateStatus);
        updateStatus();

        // Show cached pages
        async function showCachedPages() {
            if ('caches' in window) {
                try {
                    const cache = await caches.open('pricetag-dynamic-v1');
                    const keys = await cache.keys();
                    const pages = keys.filter(req => {
                        const url = new URL(req.url);
                        return !url.pathname.includes('/assets/') &&
                               !url.pathname.includes('/api/') &&
                               url.pathname !== '/offline';
                    }).slice(0, 5);

                    if (pages.length > 0) {
                        const container = document.getElementById('cachedPages');
                        const list = document.getElementById('cachedList');

                        pages.forEach(req => {
                            const url = new URL(req.url);
                            const li = document.createElement('li');
                            li.innerHTML = `
                                <a href="${url.pathname}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    ${url.pathname === '/' ? 'Home' : url.pathname.replace(/\//g, ' ').trim()}
                                </a>
                            `;
                            list.appendChild(li);
                        });

                        container.style.display = 'block';
                    }
                } catch (e) {
                    console.log('Could not list cached pages');
                }
            }
        }

        showCachedPages();
    </script>
</body>
</html>
