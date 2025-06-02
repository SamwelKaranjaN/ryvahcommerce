// Address Management
class AddressManager {
    constructor() {
        this.addresses = [];
        this.currentEditId = null;
        this.init();
    }

    init() {
        this.loadAddresses();
        this.bindEvents();
    }

    bindEvents() {
        // Add address form
        const addForm = document.getElementById('addAddressForm');
        if (addForm) {
            addForm.addEventListener('submit', (e) => this.handleAddAddress(e));
        }

        // Edit address form
        const editForm = document.getElementById('editAddressForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => this.handleEditAddress(e));
        }

        // Modal reset events
        const addModal = document.getElementById('addAddressModal');
        if (addModal) {
            addModal.addEventListener('hidden.bs.modal', () => this.resetAddForm());
        }

        const editModal = document.getElementById('editAddressModal');
        if (editModal) {
            editModal.addEventListener('hidden.bs.modal', () => this.resetEditForm());
        }
    }

    async loadAddresses() {
        try {
            const response = await fetch('../api/get_addresses.php');
            const data = await response.json();

            if (data.success) {
                this.addresses = data.addresses;
                this.renderAddresses();
            } else {
                throw new Error(data.message || 'Failed to load addresses');
            }
        } catch (error) {
            console.error('Error loading addresses:', error);
            this.showError('Failed to load addresses. Please refresh the page.');

            // Show empty state if loading fails
            const container = document.getElementById('addressList');
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load addresses. Please refresh the page.
                        <button class="btn btn-sm btn-outline-danger ms-2" onclick="location.reload()">
                            <i class="fas fa-refresh me-1"></i>Refresh
                        </button>
                    </div>
                `;
            }
        }
    }

    renderAddresses() {
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

        container.innerHTML = this.addresses.map(address => `
            <div class="address-card ${address.is_default ? 'default' : ''}">
                <div class="address-header">
                    <h5>${this.escapeHtml(address.label)}</h5>
                    ${address.is_default ? '<span class="badge bg-primary">Default</span>' : ''}
                </div>
                <div class="address-body">
                    <p><strong>${this.escapeHtml(address.street)}</strong></p>
                    <p>${this.escapeHtml(address.city)}, ${this.escapeHtml(address.state)} ${this.escapeHtml(address.postal_code)}</p>
                    <p>${this.escapeHtml(address.country)}</p>
                </div>
                <div class="address-actions">
                    <button class="btn btn-outline-primary btn-sm" onclick="addressManager.editAddress(${address.id})">
                        <i class="fas fa-edit me-1"></i>Edit
                    </button>
                    ${!address.is_default ? `
                        <button class="btn btn-outline-success btn-sm" onclick="addressManager.setDefault(${address.id})">
                            <i class="fas fa-star me-1"></i>Set Default
                        </button>
                    ` : ''}
                    <button class="btn btn-outline-danger btn-sm" onclick="addressManager.deleteAddress(${address.id})" 
                            ${address.is_default ? 'disabled title="Cannot delete default address"' : ''}>
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        `).join('');
    }

    async handleAddAddress(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');

        // Show loading
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('../api/add_address.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Address added successfully!');
                this.loadAddresses(); // Reload addresses

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
                if (modal) {
                    modal.hide();
                }
            } else {
                throw new Error(data.message || 'Failed to add address');
            }
        } catch (error) {
            console.error('Error adding address:', error);
            this.showError(error.message);
        } finally {
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    async handleEditAddress(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        formData.append('id', this.currentEditId);

        const submitBtn = form.querySelector('button[type="submit"]');

        // Show loading
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('../api/update_address.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Address updated successfully!');
                this.loadAddresses(); // Reload addresses

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editAddressModal'));
                if (modal) {
                    modal.hide();
                }
            } else {
                throw new Error(data.message || 'Failed to update address');
            }
        } catch (error) {
            console.error('Error updating address:', error);
            this.showError(error.message);
        } finally {
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    editAddress(id) {
        console.log('Editing address with ID:', id);
        console.log('Available addresses:', this.addresses);

        const address = this.addresses.find(addr => addr.id == id);
        if (!address) {
            console.error('Address not found for ID:', id);
            this.showError('Address not found');
            return;
        }

        console.log('Found address:', address);
        this.currentEditId = id;

        // Ensure form elements exist before populating
        const labelField = document.getElementById('editLabel');
        const streetField = document.getElementById('editStreet');
        const cityField = document.getElementById('editCity');
        const stateField = document.getElementById('editState');
        const postalCodeField = document.getElementById('editPostalCode');
        const countryField = document.getElementById('editCountry');
        const isDefaultField = document.getElementById('editIsDefault');

        if (!labelField || !streetField || !cityField || !stateField || !postalCodeField || !countryField || !isDefaultField) {
            console.error('One or more form fields not found');
            this.showError('Edit form not properly loaded');
            return;
        }

        // Populate edit form with address data
        labelField.value = address.label || '';
        streetField.value = address.street || '';
        cityField.value = address.city || '';
        stateField.value = address.state || '';
        postalCodeField.value = address.postal_code || '';
        countryField.value = address.country || '';

        // Handle is_default field (could be 1, "1", true, or boolean)
        isDefaultField.checked = Boolean(address.is_default == 1 || address.is_default === '1' || address.is_default === true);

        console.log('Form populated with values:', {
            label: labelField.value,
            street: streetField.value,
            city: cityField.value,
            state: stateField.value,
            postal_code: postalCodeField.value,
            country: countryField.value,
            is_default: isDefaultField.checked
        });

        // Show modal with fallback methods
        this.showEditModal();
    }

    showEditModal() {
        const modalElement = document.getElementById('editAddressModal');
        if (!modalElement) {
            console.error('Edit modal element not found');
            this.showError('Edit form not available');
            return;
        }

        try {
            // First try using Bootstrap Modal
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('Edit modal shown using Bootstrap');
            } else {
                // Fallback: directly manipulate the modal
                console.warn('Bootstrap not available, using fallback modal display');
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                modalElement.setAttribute('aria-hidden', 'false');

                // Add backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'edit-modal-backdrop';
                document.body.appendChild(backdrop);

                // Add close functionality
                const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"]');
                closeButtons.forEach(btn => {
                    btn.onclick = () => this.hideEditModal();
                });

                console.log('Edit modal shown using fallback method');
            }
        } catch (error) {
            console.error('Error showing modal:', error);
            this.showError('Failed to open edit form');
        }
    }

    hideEditModal() {
        const modalElement = document.getElementById('editAddressModal');
        if (!modalElement) return;

        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            } else {
                // Fallback: directly manipulate the modal
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                modalElement.setAttribute('aria-hidden', 'true');

                // Remove backdrop
                const backdrop = document.getElementById('edit-modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            }
        } catch (error) {
            console.error('Error hiding modal:', error);
        }
    }

    async setDefault(id) {
        try {
            const response = await fetch('../api/set_default_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Default address updated!');
                this.loadAddresses(); // Reload addresses
            } else {
                throw new Error(data.message || 'Failed to set default address');
            }
        } catch (error) {
            console.error('Error setting default address:', error);
            this.showError(error.message);
        }
    }

    async deleteAddress(id) {
        const address = this.addresses.find(addr => addr.id == id);
        if (address && address.is_default) {
            this.showError('Cannot delete default address. Set another address as default first.');
            return;
        }

        if (!confirm('Are you sure you want to delete this address?')) {
            return;
        }

        try {
            const response = await fetch('../api/delete_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Address deleted successfully!');
                this.loadAddresses(); // Reload addresses
            } else {
                throw new Error(data.message || 'Failed to delete address');
            }
        } catch (error) {
            console.error('Error deleting address:', error);
            this.showError(error.message);
        }
    }

    resetAddForm() {
        const form = document.getElementById('addAddressForm');
        if (form) {
            form.reset();
        }
    }

    resetEditForm() {
        const form = document.getElementById('editAddressForm');
        if (form) {
            form.reset();
        }
        this.currentEditId = null;
        console.log('Edit form reset');
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type) {
        // Remove existing notifications
        const existing = document.querySelectorAll('.notification-toast');
        existing.forEach(el => el.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed notification-toast`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
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

    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function (m) { return map[m]; });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.addressManager === 'undefined') {
        window.addressManager = new AddressManager();
        console.log('AddressManager initialized');
    }
}); 