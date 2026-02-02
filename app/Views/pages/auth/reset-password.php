<!-- Reset Password Page -->

<div class="container py-16">
    <div class="max-w-md mx-auto">
        <div class="card">
            <div class="card-body p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-semibold mb-2">Reset Password</h1>
                    <p class="text-muted">Enter your new password below.</p>
                </div>

                <?php if ($message = flash('error')): ?>
                <div class="alert alert-danger mb-6"><?= e($message) ?></div>
                <?php endif; ?>

                <form action="<?= url('/reset-password') ?>" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">

                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" id="password" name="password" class="form-input"
                               placeholder="Minimum 8 characters" required minlength="8">
                        <p class="form-text">Must contain at least one uppercase letter and one number.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input"
                               placeholder="Re-enter your password" required minlength="8">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Reset Password
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
