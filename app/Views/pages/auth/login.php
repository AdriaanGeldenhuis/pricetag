<!-- Login Page -->
<div class="container py-12">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold mb-2">Welcome Back</h1>
            <p class="text-muted">Sign in to your account to continue</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?= url('/login') ?>" method="POST" data-validate>
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label for="email" class="form-label required">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input"
                               value="<?= e(old('email')) ?>" required autofocus>
                    </div>

                    <div class="form-group">
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="form-label required">Password</label>
                            <a href="<?= url('/forgot-password') ?>" class="text-sm text-primary">Forgot password?</a>
                        </div>
                        <input type="password" id="password" name="password" class="form-input"
                               minlength="6" required>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="remember" value="1" class="form-check-input">
                            <span class="form-check-label">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-muted">
                        Don't have an account?
                        <a href="<?= url('/register') ?>" class="text-primary font-medium">Create one</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Benefits -->
        <div class="mt-8 text-center text-sm text-muted">
            <p class="mb-4">Why create an account?</p>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <svg class="mx-auto mb-2" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <span>Track orders</span>
                </div>
                <div>
                    <svg class="mx-auto mb-2" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    <span>Save wishlist</span>
                </div>
                <div>
                    <svg class="mx-auto mb-2" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>Faster checkout</span>
                </div>
            </div>
        </div>
    </div>
</div>
