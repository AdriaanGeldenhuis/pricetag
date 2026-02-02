<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error | <?= e(config('app.name', 'Pricetag')) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            color: #111827;
            text-align: center;
            padding: 2rem;
        }
        .error-page { max-width: 400px; }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #ef4444;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .error-message {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
        }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">500</div>
        <h1 class="error-title">Something Went Wrong</h1>
        <p class="error-message">
            We're experiencing technical difficulties. Please try again later.
        </p>
        <a href="/" class="btn">Go Home</a>
    </div>
</body>
</html>
