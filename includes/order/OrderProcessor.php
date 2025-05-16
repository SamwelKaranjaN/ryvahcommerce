<?php
class OrderProcessor
{
    private $conn;
    private $user;
    private $cart;

    public function __construct($conn, $user, $cart)
    {
        $this->conn = $conn;
        $this->user = $user;
        $this->cart = $cart;
    }

    /**
     * Generate a unique invoice number
     * Format: INV-YYYYMMDD-XXXXX where XXXXX is a random number
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $prefix . $random;
    }

    /**
     * Create a pending order
     * @param array $shippingDetails Shipping information
     * @return array Order creation result
     */
    public function createPendingOrder($shippingDetails)
    {
        try {
            // Validate input data
            if (empty($this->user['id'])) {
                throw new Exception("Invalid user data");
            }
            if (empty($this->cart['items'])) {
                throw new Exception("Cart is empty");
            }
            if (empty($shippingDetails)) {
                throw new Exception("Shipping details are required");
            }

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Start transaction
            if (!$this->conn->begin_transaction()) {
                throw new Exception("Failed to start transaction");
            }

            // Create pending order
            $stmt = $this->conn->prepare("
                INSERT INTO orders (
                    user_id, 
                    invoice_number,
                    total_amount, 
                    payment_status,
                    shipping_address,
                    created_at
                ) VALUES (?, ?, ?, 'pending', ?, NOW())
            ");

            if (!$stmt) {
                throw new Exception("Failed to prepare order statement: " . $this->conn->error);
            }

            $shippingAddress = sprintf(
                "%s\n%s, %s, %s %s\nPhone: %s",
                $shippingDetails['full_name'],
                $shippingDetails['address'],
                $shippingDetails['city'],
                $shippingDetails['state'],
                $shippingDetails['postal_code'],
                $shippingDetails['phone']
            );

            $stmt->bind_param(
                "isds",
                $this->user['id'],
                $invoiceNumber,
                $this->cart['total'],
                $shippingAddress
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create order: " . $stmt->error);
            }

            $orderId = $this->conn->insert_id;
            if (!$orderId) {
                throw new Exception("Failed to get order ID");
            }

            // Add order items
            if (!$this->addOrderItems($orderId)) {
                throw new Exception("Failed to add order items");
            }

            // Commit transaction
            if (!$this->conn->commit()) {
                throw new Exception("Failed to commit transaction");
            }

            return [
                'success' => true,
                'order_id' => $orderId,
                'invoice_number' => $invoiceNumber,
                'message' => 'Pending order created successfully'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->conn->inTransaction()) {
                $this->conn->rollback();
            }
            error_log("Order creation failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Complete a pending order after successful payment
     * @param string $invoiceNumber Invoice number
     * @return array Order completion result
     */
    public function completeOrder($invoiceNumber)
    {
        try {
            // Start transaction
            if (!$this->conn->begin_transaction()) {
                throw new Exception("Failed to start transaction");
            }

            // Update order status
            $stmt = $this->conn->prepare("
                UPDATE orders 
                SET payment_status = 'completed', 
                    updated_at = NOW()
                WHERE invoice_number = ? 
                AND user_id = ? 
                AND payment_status = 'pending'
            ");

            if (!$stmt) {
                throw new Exception("Failed to prepare order update statement: " . $this->conn->error);
            }

            $stmt->bind_param("si", $invoiceNumber, $this->user['id']);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update order: " . $stmt->error);
            }

            if ($stmt->affected_rows === 0) {
                throw new Exception("Order not found or already completed");
            }

            // Update inventory
            if (!$this->updateInventory()) {
                throw new Exception("Failed to update inventory");
            }

            // Clear cart
            $this->clearCart();

            // Commit transaction
            if (!$this->conn->commit()) {
                throw new Exception("Failed to commit transaction");
            }

            return [
                'success' => true,
                'message' => 'Order completed successfully'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->conn->inTransaction()) {
                $this->conn->rollback();
            }
            error_log("Order completion failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to complete order: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get order details
     * @param int $orderId Order ID
     * @return array|null Order details or null if not found
     */
    public function getOrderDetails($orderId)
    {
        $stmt = $this->conn->prepare("
            SELECT o.*, u.email, u.full_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->bind_param("ii", $orderId, $this->user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Add items to order
     * @param int $orderId Order ID
     * @return bool Success status
     */
    private function addOrderItems($orderId)
    {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO order_items (
                    order_id, product_id, quantity, price, subtotal
                ) VALUES (?, ?, ?, ?, ?)
            ");

            if (!$stmt) {
                throw new Exception("Failed to prepare order items statement: " . $this->conn->error);
            }

            foreach ($this->cart['items'] as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $stmt->bind_param(
                    "iiidd",
                    $orderId,
                    $item['id'],
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                );
                if (!$stmt->execute()) {
                    throw new Exception("Failed to add order item: " . $stmt->error);
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Error adding order items: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update product inventory
     * @return bool Success status
     */
    private function updateInventory()
    {
        try {
            $stmt = $this->conn->prepare("
                UPDATE products 
                SET stock = stock - ? 
                WHERE id = ?
            ");

            if (!$stmt) {
                throw new Exception("Failed to prepare inventory update statement: " . $this->conn->error);
            }

            foreach ($this->cart['items'] as $item) {
                $stmt->bind_param("ii", $item['quantity'], $item['id']);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update inventory: " . $stmt->error);
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Error updating inventory: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear user's cart
     */
    private function clearCart()
    {
        try {
            if (isset($this->user['id'])) {
                $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
                if (!$stmt) {
                    throw new Exception("Failed to prepare cart clear statement: " . $this->conn->error);
                }
                $stmt->bind_param("i", $this->user['id']);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to clear cart: " . $stmt->error);
                }
            }
            unset($_SESSION['cart']);
        } catch (Exception $e) {
            error_log("Error clearing cart: " . $e->getMessage());
        }
    }
}