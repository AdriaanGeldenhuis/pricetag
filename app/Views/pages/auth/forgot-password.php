<!-- Forgot Password Page -->

<div class="container py-16">
    <div class="max-w-md mx-auto">
        <div class="card">
            <div class="card-body p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-semibold mb-2">Forgot Password</h1>
                    <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <?php if ($message = flash('error')): ?>
                <div class="alert alert-danger mb-6"><?= e($message) ?></div>
                <?php endif; ?>

                <?php if ($message = flash('success')): ?>
                <div class="alert alert-success mb-6"><?= e($message) ?></div>
                <?php endif; ?>

                <form action="<?= url('/forgot-password') ?>" method="POST">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input"
                               placeholder="you@example.com" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Send Reset Link
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="<?= url('/login') ?>" class="text-primary hover:underline">
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
