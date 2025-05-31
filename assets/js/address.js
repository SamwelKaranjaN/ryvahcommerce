// Address Management
class AddressManager {
    constructor() {
        this.addresses = [];
        this.selectedAddressId = null;
        this.init();
    }

    async init() {
        await this.loadAddresses();
        this.setupEventListeners();
    }

    async loadAddresses() {
        try {
            const response = await fetch('/includes/address.php');
            const data = await response.json();

            if (data.success) {
                this.addresses = data.addresses;
                this.renderAddresses();
                if (this.addresses.length > 0) {
                    const defaultAddress = this.addresses.find(addr => addr.is_default === '1');
                    this.selectedAddressId = defaultAddress ? defaultAddress.id : this.addresses[0].id;
                    this.updateSelectedAddress();
                }
            } else {
                showError('Failed to load addresses: ' + data.error);
            }
        } catch (error) {
            showError('Error loading addresses: ' + error.message);
        }
    }

    setupEventListeners() {
        // Add Address Form Submit
        document.getElementById('addAddressForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.addAddress();
        });

        // Edit Address Form Submit
        document.getElementById('editAddressForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.updateAddress();
        });

        // Delete Address Button
        document.addEventListener('click', async (e) => {
            if (e.target.matches('.delete-address')) {
                const addressId = e.target.dataset.id;
                if (confirm('Are you sure you want to delete this address?')) {
                    await this.deleteAddress(addressId);
                }
            }
        });

        // Edit Address Button
        document.addEventListener('click', (e) => {
            if (e.target.matches('.edit-address')) {
                const addressId = e.target.dataset.id;
                this.showEditForm(addressId);
            }
        });

        // Set Default Address Button
        document.addEventListener('click', async (e) => {
            if (e.target.matches('.set-default-address')) {
                const addressId = e.target.dataset.id;
                await this.setDefaultAddress(addressId);
            }
        });
    }

    renderAddresses() {
        const container = document.getElementById('addressList');
        if (!container) return;

        container.innerHTML = this.addresses.map(address => `
            <div class="address-card ${address.is_default === '1' ? 'default' : ''}" data-id="${address.id}">
                <div class="address-header">
                    <h5>${address.label}</h5>
                    ${address.is_default === '1' ? '<span class="badge bg-primary">Default</span>' : ''}
                </div>
                <div class="address-body">
                    <p>${address.street}</p>
                    <p>${address.city}, ${address.state} ${address.postal_code}</p>
                    <p>${address.country}</p>
                </div>
                <div class="address-actions">
                    <button class="btn btn-sm btn-outline-primary edit-address" data-id="${address.id}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-address" data-id="${address.id}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    ${address.is_default !== '1' ? `
                        <button class="btn btn-sm btn-outline-success set-default-address" data-id="${address.id}">
                            <i class="fas fa-star"></i> Set as Default
                        </button>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    async addAddress() {
        const form = document.getElementById('addAddressForm');
        const formData = new FormData(form);

        try {
            const response = await fetch('/includes/address.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                this.addresses.push(data.address);
                this.renderAddresses();
                form.reset();
                $('#addAddressModal').modal('hide');
                showSuccess('Address added successfully');
            } else {
                showError('Failed to add address: ' + data.error);
            }
        } catch (error) {
            showError('Error adding address: ' + error.message);
        }
    }

    async updateAddress() {
        const form = document.getElementById('editAddressForm');
        const formData = new FormData(form);
        formData.append('id', form.dataset.addressId);

        try {
            const response = await fetch('/includes/address.php', {
                method: 'PUT',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                const index = this.addresses.findIndex(addr => addr.id === data.address.id);
                if (index !== -1) {
                    this.addresses[index] = data.address;
                }
                this.renderAddresses();
                $('#editAddressModal').modal('hide');
                showSuccess('Address updated successfully');
            } else {
                showError('Failed to update address: ' + data.error);
            }
        } catch (error) {
            showError('Error updating address: ' + error.message);
        }
    }

    async deleteAddress(addressId) {
        try {
            const response = await fetch(`/includes/address.php?id=${addressId}`, {
                method: 'DELETE'
            });
            const data = await response.json();

            if (data.success) {
                this.addresses = this.addresses.filter(addr => addr.id !== addressId);
                this.renderAddresses();
                showSuccess('Address deleted successfully');
            } else {
                showError('Failed to delete address: ' + data.error);
            }
        } catch (error) {
            showError('Error deleting address: ' + error.message);
        }
    }

    async setDefaultAddress(addressId) {
        const address = this.addresses.find(addr => addr.id === addressId);
        if (!address) return;

        const formData = new FormData();
        formData.append('id', address.id);
        formData.append('label', address.label);
        formData.append('street', address.street);
        formData.append('city', address.city);
        formData.append('state', address.state);
        formData.append('postal_code', address.postal_code);
        formData.append('country', address.country);
        formData.append('is_default', '1');

        try {
            const response = await fetch('/includes/address.php', {
                method: 'PUT',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                this.addresses = this.addresses.map(addr => ({
                    ...addr,
                    is_default: addr.id === addressId ? '1' : '0'
                }));
                this.renderAddresses();
                showSuccess('Default address updated successfully');
            } else {
                showError('Failed to update default address: ' + data.error);
            }
        } catch (error) {
            showError('Error updating default address: ' + error.message);
        }
    }

    showEditForm(addressId) {
        const address = this.addresses.find(addr => addr.id === addressId);
        if (!address) return;

        const form = document.getElementById('editAddressForm');
        form.dataset.addressId = address.id;
        form.querySelector('[name="label"]').value = address.label;
        form.querySelector('[name="street"]').value = address.street;
        form.querySelector('[name="city"]').value = address.city;
        form.querySelector('[name="state"]').value = address.state;
        form.querySelector('[name="postal_code"]').value = address.postal_code;
        form.querySelector('[name="country"]').value = address.country;
        form.querySelector('[name="is_default"]').checked = address.is_default === '1';

        $('#editAddressModal').modal('show');
    }

    updateSelectedAddress() {
        const address = this.addresses.find(addr => addr.id === this.selectedAddressId);
        if (!address) return;

        // Update shipping address display
        const shippingAddress = document.getElementById('shippingAddress');
        if (shippingAddress) {
            shippingAddress.innerHTML = `
                <p><strong>${address.label}</strong></p>
                <p>${address.street}</p>
                <p>${address.city}, ${address.state} ${address.postal_code}</p>
                <p>${address.country}</p>
            `;
        }

        // Update hidden input for form submission
        const addressInput = document.getElementById('selectedAddressId');
        if (addressInput) {
            addressInput.value = this.selectedAddressId;
        }
    }
}

// Initialize address manager when document is ready
document.addEventListener('DOMContentLoaded', () => {
    window.addressManager = new AddressManager();
}); 