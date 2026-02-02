<!-- Account - Addresses -->
<div class="account-page">
    <div class="container py-8">
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <?php include APP_PATH . '/Views/pages/account/_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="account-main">
                <!-- Page Header -->
                <div class="account-page-header">
                    <div>
                        <h1 class="account-page-title">My Addresses</h1>
                        <p class="text-muted text-sm">Manage your shipping and billing addresses</p>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="openAddressModal()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Address
                    </button>
                </div>

                <!-- Addresses Grid -->
                <div class="addresses-grid">
                    <?php if (empty($addresses)): ?>
                    <div class="card col-span-full">
                        <div class="card-body">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <h3>No addresses saved</h3>
                                <p class="text-muted">Add your first address for faster checkout.</p>
                                <button type="button" class="btn btn-primary" onclick="openAddressModal()">
                                    Add Address
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($addresses as $address): ?>
                    <div class="address-card <?= $address['is_default'] ? 'is-default' : '' ?>">
                        <?php if ($address['is_default']): ?>
                        <span class="address-card-badge">Default</span>
                        <?php endif; ?>
                        <p class="address-card-type"><?= ucfirst($address['type']) ?></p>
                        <p class="address-card-name"><?= e($address['first_name'] . ' ' . $address['last_name']) ?></p>
                        <p class="address-card-line"><?= e($address['address_line_1']) ?></p>
                        <?php if ($address['address_line_2']): ?>
                        <p class="address-card-line"><?= e($address['address_line_2']) ?></p>
                        <?php endif; ?>
                        <p class="address-card-line"><?= e($address['city']) ?>, <?= e($address['province']) ?> <?= e($address['postal_code']) ?></p>
                        <?php if ($address['phone']): ?>
                        <p class="address-card-line"><?= e($address['phone']) ?></p>
                        <?php endif; ?>

                        <div class="address-card-actions">
                            <button type="button" class="btn btn-sm btn-ghost" onclick="editAddress(<?= htmlspecialchars(json_encode($address), ENT_QUOTES) ?>)">
                                Edit
                            </button>
                            <?php if (!$address['is_default']): ?>
                            <form action="<?= url('/account/addresses') ?>" method="POST" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="address_id" value="<?= $address['id'] ?>">
                                <input type="hidden" name="is_default" value="1">
                                <input type="hidden" name="type" value="<?= e($address['type']) ?>">
                                <input type="hidden" name="first_name" value="<?= e($address['first_name']) ?>">
                                <input type="hidden" name="last_name" value="<?= e($address['last_name']) ?>">
                                <input type="hidden" name="address_line_1" value="<?= e($address['address_line_1']) ?>">
                                <input type="hidden" name="address_line_2" value="<?= e($address['address_line_2'] ?? '') ?>">
                                <input type="hidden" name="city" value="<?= e($address['city']) ?>">
                                <input type="hidden" name="province" value="<?= e($address['province']) ?>">
                                <input type="hidden" name="postal_code" value="<?= e($address['postal_code']) ?>">
                                <button type="submit" class="btn btn-sm btn-ghost">Set as Default</button>
                            </form>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-ghost text-danger" onclick="deleteAddress(<?= $address['id'] ?>)">
                                Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Add New Address Card -->
                    <button type="button" class="address-card-add" onclick="openAddressModal()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Add New Address</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div class="modal-overlay" id="address-modal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="address-modal-title">Add Address</h2>
            <button type="button" class="modal-close" onclick="closeAddressModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form action="<?= url('/account/addresses') ?>" method="POST" id="address-form">
            <?= csrfField() ?>
            <input type="hidden" name="address_id" id="address-id">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Address Type</label>
                    <div class="flex gap-4">
                        <label class="form-check">
                            <input type="radio" name="type" value="shipping" class="form-check-input" checked>
                            <span class="form-check-label">Shipping</span>
                        </label>
                        <label class="form-check">
                            <input type="radio" name="type" value="billing" class="form-check-input">
                            <span class="form-check-label">Billing</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="first_name" class="form-label required">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="form-label required">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="company" class="form-label">Company (Optional)</label>
                    <input type="text" id="company" name="company" class="form-input">
                </div>

                <div class="form-group">
                    <label for="address_line_1" class="form-label required">Street Address</label>
                    <input type="text" id="address_line_1" name="address_line_1" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="address_line_2" class="form-label">Apartment, Suite, etc.</label>
                    <input type="text" id="address_line_2" name="address_line_2" class="form-input">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="form-group">
                        <label for="city" class="form-label required">City</label>
                        <input type="text" id="city" name="city" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="province" class="form-label required">Province</label>
                        <select id="province" name="province" class="form-select" required>
                            <option value="">Select...</option>
                            <option value="Eastern Cape">Eastern Cape</option>
                            <option value="Free State">Free State</option>
                            <option value="Gauteng">Gauteng</option>
                            <option value="KwaZulu-Natal">KwaZulu-Natal</option>
                            <option value="Limpopo">Limpopo</option>
                            <option value="Mpumalanga">Mpumalanga</option>
                            <option value="North West">North West</option>
                            <option value="Northern Cape">Northern Cape</option>
                            <option value="Western Cape">Western Cape</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="postal_code" class="form-label required">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-input" pattern="[0-9]{4}" maxlength="4" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input">
                </div>

                <div class="form-group mb-0">
                    <label class="form-check">
                        <input type="checkbox" name="is_default" value="1" class="form-check-input">
                        <span class="form-check-label">Set as default address</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeAddressModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Address</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Delete Address</h2>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this address? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
            <form action="" method="POST" id="delete-form" class="inline">
                <?= csrfField() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<script>
function openAddressModal() {
    document.getElementById('address-modal-title').textContent = 'Add Address';
    document.getElementById('address-form').reset();
    document.getElementById('address-id').value = '';
    document.getElementById('address-modal').classList.add('open');
}

function closeAddressModal() {
    document.getElementById('address-modal').classList.remove('open');
}

function editAddress(address) {
    document.getElementById('address-modal-title').textContent = 'Edit Address';
    document.getElementById('address-id').value = address.id;
    document.getElementById('first_name').value = address.first_name;
    document.getElementById('last_name').value = address.last_name;
    document.getElementById('company').value = address.company || '';
    document.getElementById('address_line_1').value = address.address_line_1;
    document.getElementById('address_line_2').value = address.address_line_2 || '';
    document.getElementById('city').value = address.city;
    document.getElementById('province').value = address.province;
    document.getElementById('postal_code').value = address.postal_code;
    document.getElementById('phone').value = address.phone || '';

    // Set type
    document.querySelector(`input[name="type"][value="${address.type}"]`).checked = true;

    // Set default checkbox
    document.querySelector('input[name="is_default"]').checked = address.is_default;

    document.getElementById('address-modal').classList.add('open');
}

function deleteAddress(id) {
    document.getElementById('delete-form').action = '<?= url('/account/addresses') ?>/' + id;
    document.getElementById('delete-modal').classList.add('open');
}

function closeDeleteModal() {
    document.getElementById('delete-modal').classList.remove('open');
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('open');
        }
    });
});

// Close modals on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(modal => {
            modal.classList.remove('open');
        });
    }
});
</script>
