<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: var(--space-8);
        }
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: var(--color-primary);
            line-height: 1;
            margin-bottom: var(--space-4);
        }
        .error-title {
            font-size: var(--text-2xl);
            font-weight: 600;
            margin-bottom: var(--space-2);
        }
        .error-message {
            color: var(--color-text-muted);
            margin-bottom: var(--space-8);
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">
            The page you're looking for doesn't exist or has been moved.
        </p>
        <a href="<?= url('/') ?>" class="btn btn-primary btn-lg">Go Home</a>
    </div>
</body>
</html>
