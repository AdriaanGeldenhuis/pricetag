<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied | <?= e(config('app.name')) ?></title>
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
            color: var(--color-danger);
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
        .error-actions {
            display: flex;
            gap: var(--space-4);
            flex-wrap: wrap;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">403</div>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-message">
            You don't have permission to access this page. Please contact an administrator if you believe this is an error.
        </p>
        <div class="error-actions">
            <a href="<?= url('/') ?>" class="btn btn-primary btn-lg">Go Home</a>
            <?php if (!auth()): ?>
            <a href="<?= url('/login') ?>" class="btn btn-outline btn-lg">Log In</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
