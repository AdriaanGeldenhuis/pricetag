<!-- Register Page -->
<div class="container py-12">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold mb-2">Create Account</h1>
            <p class="text-muted">Join us and start shopping today</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?= url('/register') ?>" method="POST" data-validate>
                    <?= csrfField() ?>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="first_name" class="form-label required">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-input"
                                   value="<?= e(old('first_name')) ?>" minlength="2" required>
                        </div>

                        <div class="form-group">
                            <label for="last_name" class="form-label required">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-input"
                                   value="<?= e(old('last_name')) ?>" minlength="2" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label required">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input"
                               value="<?= e(old('email')) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" id="password" name="password" class="form-input"
                               minlength="8" required>
                        <span class="form-hint">At least 8 characters with uppercase and number</span>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label required">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input"
                               minlength="8" required>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="terms" value="1" class="form-check-input" required>
                            <span class="form-check-label">
                                I agree to the
                                <a href="<?= url('/page/terms') ?>" class="text-primary" target="_blank">Terms of Service</a>
                                and
                                <a href="<?= url('/page/privacy') ?>" class="text-primary" target="_blank">Privacy Policy</a>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Create Account
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-muted">
                        Already have an account?
                        <a href="<?= url('/login') ?>" class="text-primary font-medium">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
