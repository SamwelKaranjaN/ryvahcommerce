<?php
require_once __DIR__ . '/../bootstrap.php';

class OrderProcessor
{
    private $conn;
    private $user;
    private $order_data;

    public function __construct($conn, $user, $order_data)
    {
        $this->conn = $conn;
        $this->user = $user;
        $this->order_data = $order_data;
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
     * @param array $shipping_details Shipping information
     * @return array Order creation result
     */
    public function createPendingOrder($shipping_details)
    {
        try {
            // Generate invoice number
            $invoice_number = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);

            // Start transaction
            $this->conn->begin_transaction();

            // Create order
            $sql = "INSERT INTO orders (invoice_number, user_id, total_amount, payment_status, shipping_address, billing_address) 
                    VALUES (?, ?, ?, 'pending', ?, ?)";
            $stmt = $this->conn->prepare($sql);

            $shipping_address = json_encode($shipping_details);
            $billing_address = json_encode($shipping_details); // Using same address for both

            $stmt->bind_param(
                "sidds",
                $invoice_number,
                $this->user['id'],
                $this->order_data['total'],
                $shipping_address,
                $billing_address
            );
            $stmt->execute();
            $order_id = $this->conn->insert_id;

            // Create order items
            foreach ($this->order_data['items'] as $item) {
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $subtotal = $item['price'] * $item['quantity'];
                $stmt->bind_param(
                    "iiidd",
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                );
                $stmt->execute();

                // If it's an ebook, create user_purchases entry
                if ($item['type'] === 'ebook') {
                    $sql = "INSERT INTO user_purchases (user_id, product_id, order_id, download_count) 
                            VALUES (?, ?, ?, 0)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param(
                        "iii",
                        $this->user['id'],
                        $item['id'],
                        $order_id
                    );
                    $stmt->execute();
                }
            }

            // Add initial status history
            $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'pending', 'Order created')";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'order_id' => $order_id,
                'invoice_number' => $invoice_number
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error creating pending order: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Complete a pending order after successful payment
     * @param string $invoice_number Invoice number
     * @return array Order completion result
     */
    public function completeOrder($invoice_number)
    {
        try {
            // Start transaction
            $this->conn->begin_transaction();

            // Get order details
            $sql = "SELECT o.*, oi.*, p.type 
                    FROM orders o 
                    JOIN order_items oi ON o.id = oi.order_id 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE o.invoice_number = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $invoice_number);
            $stmt->execute();
            $result = $stmt->get_result();
            $order_items = $result->fetch_all(MYSQLI_ASSOC);

            // Update order status
            $sql = "UPDATE orders SET payment_status = 'completed' WHERE invoice_number = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $invoice_number);
            $stmt->execute();

            // Add status history
            $sql = "INSERT INTO order_status_history (order_id, status, notes) 
                    SELECT id, 'completed', 'Payment completed' 
                    FROM orders WHERE invoice_number = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $invoice_number);
            $stmt->execute();

            // Clear cart
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $this->user['id']);
            $stmt->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Order completed successfully',
                'order_items' => $order_items
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error completing order: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to complete order: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get order details
     * @param int $order_id Order ID
     * @return array|null Order details or null if not found
     */
    public function getOrderDetails($order_id)
    {
        $stmt = $this->conn->prepare("
            SELECT o.*, u.email, u.full_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->bind_param("ii", $order_id, $this->user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
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

            foreach ($this->order_data['items'] as $item) {
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