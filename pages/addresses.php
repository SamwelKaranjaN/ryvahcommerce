<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=addresses');
    exit;
}

$page_title = 'Manage Addresses';
include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Addresses</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Manage Addresses</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="fas fa-plus me-2"></i>Add New Address
                </button>
            </div>
        </div>
    </div>

    <!-- Address List -->
    <div class="row">
        <div class="col">
            <div id="addressList" class="address-list">
                <!-- Addresses will be loaded here by JavaScript -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading addresses...</span>
                    </div>
                    <p class="mt-3">Loading your addresses...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAddressModalLabel">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAddressForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addLabel" class="form-label">Address Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addLabel" name="label" required
                            placeholder="e.g., Home, Work, etc." maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="addStreet" class="form-label">Street Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addStreet" name="street" required
                            placeholder="123 Main Street" maxlength="255">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="addCity" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addCity" name="city" required
                                placeholder="New York" maxlength="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="addState" class="form-label">State/Province <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addState" name="state" required
                                placeholder="NY" maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="addPostalCode" class="form-label">Postal Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addPostalCode" name="postal_code" required
                                placeholder="12345" maxlength="20">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="addCountry" class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="form-select" id="addCountry" name="country" required>
                                <option value="">Select Country</option>
                                <option value="United States">United States</option>
                                <option value="Canada">Canada</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="Australia">Australia</option>
                                <option value="Germany">Germany</option>
                                <option value="France">France</option>
                                <option value="Japan">Japan</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="addIsDefault" name="is_default">
                        <label class="form-check-label" for="addIsDefault">
                            Set as default address
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Address Modal -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAddressModalLabel">Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAddressForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editLabel" class="form-label">Address Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editLabel" name="label" required
                            placeholder="e.g., Home, Work, etc." maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="editStreet" class="form-label">Street Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editStreet" name="street" required
                            placeholder="123 Main Street" maxlength="255">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCity" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editCity" name="city" required
                                placeholder="New York" maxlength="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editState" class="form-label">State/Province <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editState" name="state" required
                                placeholder="NY" maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPostalCode" class="form-label">Postal Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editPostalCode" name="postal_code" required
                                placeholder="12345" maxlength="20">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCountry" class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="form-select" id="editCountry" name="country" required>
                                <option value="">Select Country</option>
                                <option value="United States">United States</option>
                                <option value="Canada">Canada</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="Australia">Australia</option>
                                <option value="Germany">Germany</option>
                                <option value="France">France</option>
                                <option value="Japan">Japan</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="editIsDefault" name="is_default">
                        <label class="form-check-label" for="editIsDefault">
                            Set as default address
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .address-list {
        display: grid;
        gap: 1rem;
    }

    .address-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        background: #fff;
        transition: all 0.2s ease;
        position: relative;
    }

    .address-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .address-card.default {
        border-color: #0d6efd;
        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
    }

    .address-card.default::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #0d6efd, #6610f2);
        border-radius: 8px 8px 0 0;
    }

    .address-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .address-header h5 {
        margin: 0;
        font-weight: 600;
        color: #212529;
        flex: 1;
    }

    .address-header .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }

    .address-body {
        margin-bottom: 1rem;
        color: #6c757d;
    }

    .address-body p {
        margin: 0.25rem 0;
        line-height: 1.4;
    }

    .address-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .address-actions .btn {
        flex: 1;
        min-width: auto;
    }

    @media (min-width: 768px) {
        .address-actions .btn {
            flex: none;
        }
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .modal-header {
        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        border-bottom: 1px solid #dee2e6;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    @media (max-width: 767px) {
        .address-card {
            padding: 1rem;
        }

        .address-actions {
            flex-direction: column;
        }

        .address-actions .btn {
            flex: 1;
        }
    }
</style>

<!-- Include JavaScript -->
<script src="../assets/js/address.js"></script>
<script>
    // Initialize address manager
    let addressManager;

    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing address manager...');
        addressManager = new AddressManager();
        
        // Debug: Check if Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not loaded!');
        } else {
            console.log('Bootstrap loaded successfully');
        }
        
        // Debug: Check if modals exist
        const addModal = document.getElementById('addAddressModal');
        const editModal = document.getElementById('editAddressModal');
        
        if (!addModal) {
            console.error('Add address modal not found');
        } else {
            console.log('Add address modal found');
        }
        
        if (!editModal) {
            console.error('Edit address modal not found');
        } else {
            console.log('Edit address modal found');
        }
    });

    // Helper functions for notifications
    function showSuccess(message) {
        showNotification(message, 'success');
    }

    function showError(message) {
        showNotification(message, 'error');
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        // Add to document
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    // Test function to manually trigger edit (for debugging)
    function testEditAddress(id) {
        console.log('Test edit called for ID:', id);
        if (window.addressManager) {
            window.addressManager.editAddress(id);
        } else {
            console.error('AddressManager not initialized');
        }
    }

    // Override the renderAddresses method to handle empty state
    const originalRenderAddresses = AddressManager.prototype.renderAddresses;
    AddressManager.prototype.renderAddresses = function() {
        const container = document.getElementById('addressList');
        if (!container) return;

        if (this.addresses.length === 0) {
            container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-map-marker-alt"></i>
                <h4>No addresses found</h4>
                <p>You haven't added any shipping addresses yet. Add your first address to get started.</p>
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="fas fa-plus me-2"></i>Add Your First Address
                </button>
            </div>
        `;
            return;
        }

        // Call original method
        originalRenderAddresses.call(this);
    };
</script>

<?php include '../includes/layouts/footer.php'; ?>