/**
 * Frontend Shipping Calculator
 * Calculates shipping costs based on product types and quantities
 */

class ShippingCalculator {
    constructor() {
        this.shippingRates = {};
        this.loadShippingRates();
    }

    /**
     * Load shipping rates from the server
     */
    async loadShippingRates() {
        try {
            const response = await fetch('/api/get-shipping-rates.php');
            if (response.ok) {
                this.shippingRates = await response.json();
            } else {
                console.error('Failed to load shipping rates');
                // Fallback to default rates
                this.shippingRates = {
                    'book': { fee: 7.00, applies_after_tax: true },
                    'ebook': { fee: 0.00, applies_after_tax: true },
                    'paint': { fee: 5.50, applies_after_tax: true }
                };
            }
        } catch (error) {
            console.error('Error loading shipping rates:', error);
            // Use default rates as fallback
            this.shippingRates = {
                'book': { fee: 7.00, applies_after_tax: true },
                'ebook': { fee: 0.00, applies_after_tax: true },
                'paint': { fee: 5.50, applies_after_tax: true }
            };
        }
    }

    /**
     * Calculate shipping for a single item
     */
    calculateItemShipping(productType, quantity, price) {
        const rate = this.shippingRates[productType];
        if (!rate || rate.fee <= 0) {
            return 0;
        }

        // No shipping for digital products
        if (productType === 'ebook') {
            return 0;
        }

        let shippingFee = rate.fee;

        // Apply quantity discounts for multiple items
        if (quantity > 1) {
            if (productType === 'book') {
                // 30% additional fee for each additional book
                const additionalFee = (quantity - 1) * (rate.fee * 0.3);
                shippingFee += additionalFee;
            } else if (productType === 'paint') {
                // 50% additional fee for each additional paint item
                const additionalFee = (quantity - 1) * (rate.fee * 0.5);
                shippingFee += additionalFee;
            }
        }

        return Math.round(shippingFee * 100) / 100; // Round to 2 decimal places
    }

    /**
     * Calculate total shipping for cart items
     */
    calculateCartShipping(cartItems) {
        let totalShipping = 0;
        const breakdown = [];

        cartItems.forEach(item => {
            const shipping = this.calculateItemShipping(
                item.type || 'book',
                parseInt(item.quantity) || 1,
                parseFloat(item.price) || 0
            );

            if (shipping > 0) {
                totalShipping += shipping;
                breakdown.push({
                    product_id: item.id,
                    product_name: item.name,
                    product_type: item.type || 'book',
                    quantity: item.quantity,
                    shipping_fee: shipping
                });
            }
        });

        return {
            total_shipping: Math.round(totalShipping * 100) / 100,
            breakdown: breakdown
        };
    }

    /**
     * Get shipping description for a product type
     */
    getShippingDescription(productType) {
        const rate = this.shippingRates[productType];
        if (!rate) {
            return 'Shipping information not available';
        }

        if (rate.fee <= 0) {
            return 'Free shipping';
        }

        switch (productType) {
            case 'ebook':
                return 'Digital download - no shipping required';
            case 'book':
                return `Standard shipping: $${rate.fee.toFixed(2)} (additional books have reduced shipping)`;
            case 'paint':
                return `Shipping: $${rate.fee.toFixed(2)} (additional items have reduced shipping)`;
            default:
                return `Shipping: $${rate.fee.toFixed(2)}`;
        }
    }

    /**
     * Format shipping amount for display
     */
    formatShippingAmount(amount) {
        if (amount <= 0) {
            return 'FREE';
        }
        return `$${amount.toFixed(2)}`;
    }

    /**
     * Update shipping display in the UI
     */
    updateShippingDisplay(cartItems, containerId = 'shipping-summary') {
        const shippingResult = this.calculateCartShipping(cartItems);
        const container = document.getElementById(containerId);
        
        if (!container) {
            console.warn(`Shipping display container '${containerId}' not found`);
            return shippingResult;
        }

        let html = '<div class="shipping-summary">';
        
        if (shippingResult.total_shipping <= 0) {
            html += '<div class="shipping-free">✅ FREE SHIPPING</div>';
        } else {
            html += `<div class="shipping-total">Shipping: ${this.formatShippingAmount(shippingResult.total_shipping)}</div>`;
            
            if (shippingResult.breakdown.length > 0) {
                html += '<div class="shipping-breakdown">';
                shippingResult.breakdown.forEach(item => {
                    html += `<div class="shipping-item">
                        ${item.product_name} (${item.quantity}x): ${this.formatShippingAmount(item.shipping_fee)}
                    </div>`;
                });
                html += '</div>';
            }
        }
        
        html += '</div>';
        container.innerHTML = html;
        
        return shippingResult;
    }

    /**
     * Calculate complete order total including shipping
     */
    calculateOrderTotal(subtotal, taxAmount, shippingAmount) {
        const total = parseFloat(subtotal) + parseFloat(taxAmount) + parseFloat(shippingAmount);
        return Math.round(total * 100) / 100;
    }
}

// Global shipping calculator instance
window.shippingCalculator = new ShippingCalculator();

// CSS for shipping display (add to your stylesheet)
const shippingCSS = `
    .shipping-summary {
        margin: 10px 0;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    
    .shipping-free {
        color: #28a745;
        font-weight: bold;
        text-align: center;
    }
    
    .shipping-total {
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .shipping-breakdown {
        font-size: 0.9em;
        color: #666;
        margin-top: 5px;
    }
    
    .shipping-item {
        margin: 2px 0;
    }
`;

// Inject CSS if not already present
if (!document.getElementById('shipping-calculator-css')) {
    const style = document.createElement('style');
    style.id = 'shipping-calculator-css';
    style.textContent = shippingCSS;
    document.head.appendChild(style);
} 