-- Add invoice number and other fields to orders table
ALTER TABLE orders
ADD COLUMN invoice_number VARCHAR(20) UNIQUE AFTER id,
ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at,
ADD COLUMN payment_method VARCHAR(50) NULL DEFAULT NULL AFTER payment_status,
ADD COLUMN payment_reference VARCHAR(100) NULL DEFAULT NULL AFTER payment_method,
ADD COLUMN notes TEXT NULL DEFAULT NULL AFTER shipping_address;

-- Add index for faster lookups
CREATE INDEX idx_orders_invoice ON orders(invoice_number);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);

-- Update existing orders with generated invoice numbers
UPDATE orders 
SET invoice_number = CONCAT('INV-', DATE_FORMAT(created_at, '%Y%m%d'), '-', LPAD(id, 5, '0'))
WHERE invoice_number IS NULL;

-- Make invoice_number NOT NULL after populating existing records
ALTER TABLE orders
MODIFY COLUMN invoice_number VARCHAR(20) NOT NULL; 