<!-- Contact Page -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs container">
    <div class="breadcrumb-item"><a href="<?= url('/') ?>" class="breadcrumb-link">Home</a></div>
    <div class="breadcrumb-item"><span class="breadcrumb-current">Contact Us</span></div>
</nav>

<div class="container py-8">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-semibold mb-2">Contact Us</h1>
            <p class="text-muted">Have a question or feedback? We'd love to hear from you.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 mb-12">
            <!-- Email -->
            <div class="card text-center">
                <div class="card-body p-6">
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Email</h3>
                    <a href="mailto:info@pricetag.co.za" class="text-primary hover:underline">info@pricetag.co.za</a>
                </div>
            </div>

            <!-- Phone -->
            <div class="card text-center">
                <div class="card-body p-6">
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Phone</h3>
                    <p class="text-muted">Coming soon</p>
                </div>
            </div>

            <!-- Hours -->
            <div class="card text-center">
                <div class="card-body p-6">
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Business Hours</h3>
                    <p class="text-muted text-sm">Mon - Fri: 9am - 5pm<br>Sat: 9am - 1pm</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="card">
            <div class="card-body p-8">
                <h2 class="text-xl font-semibold mb-6">Send us a Message</h2>

                <?php if ($message = flash('error')): ?>
                <div class="alert alert-danger mb-6"><?= e($message) ?></div>
                <?php endif; ?>

                <?php if ($message = flash('success')): ?>
                <div class="alert alert-success mb-6"><?= e($message) ?></div>
                <?php endif; ?>

                <form action="<?= url('/contact') ?>" method="POST">
                    <?= csrfField() ?>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label" for="name">Your Name *</label>
                            <input type="text" id="name" name="name" class="form-input"
                                   value="<?= e(old('name')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?= e(old('email')) ?>" required>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-input"
                                   value="<?= e(old('phone')) ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="subject">Subject</label>
                            <select id="subject" name="subject" class="form-select">
                                <option value="">Select a topic</option>
                                <option value="Order Inquiry">Order Inquiry</option>
                                <option value="Product Question">Product Question</option>
                                <option value="Returns & Refunds">Returns & Refunds</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Feedback">Feedback</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message">Message *</label>
                        <textarea id="message" name="message" rows="6" class="form-input"
                                  placeholder="How can we help you?" required><?= e(old('message')) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
