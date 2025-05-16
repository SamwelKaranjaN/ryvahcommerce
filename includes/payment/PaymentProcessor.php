<?php
require_once __DIR__ . '/../bootstrap.php';

class PaymentProcessor
{
    private $conn;
    private $user;
    private $order_data;
    private $shipping_details;
    private $invoice_number;

    public function __construct($conn, $user, $order_data, $shipping_details)
    {
        $this->conn = $conn;
        $this->user = $user;
        $this->order_data = $order_data;
        $this->shipping_details = $shipping_details;
    }

    /**
     * Create a pending order and return invoice number
     */
    public function createPendingOrder()
    {
        try {
            // Validate input data
            if (empty($this->user) || empty($this->order_data) || empty($this->shipping_details)) {
                throw new Exception('Missing required data for order creation');
            }

            // Create pending order
            $orderProcessor = new OrderProcessor($this->conn, $this->user, $this->order_data);
            $result = $orderProcessor->createPendingOrder($this->shipping_details);

            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            $this->invoice_number = $result['invoice_number'];
            return $result;
        } catch (Exception $e) {
            error_log("Error creating pending order: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create pending order: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process payment for a pending order
     * @param string $invoice_number Invoice number to process
     */
    public function processPayment($invoice_number)
    {
        try {
            // Validate invoice number
            if (empty($invoice_number)) {
                throw new Exception('Invoice number is required');
            }

            // Verify order exists and is pending
            $stmt = $this->conn->prepare("
                SELECT total_amount 
                FROM orders 
                WHERE invoice_number = ? 
                AND user_id = ? 
                AND payment_status = 'pending'
            ");

            if (!$stmt) {
                throw new Exception('Failed to prepare order verification statement');
            }

            $stmt->bind_param("si", $invoice_number, $this->user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();

            if (!$order) {
                throw new Exception('Invalid or already processed invoice number');
            }

            // Verify amount matches
            if ($order['total_amount'] != $this->order_data['total']) {
                throw new Exception('Order amount mismatch');
            }

            // Start transaction
            if (!$this->conn->begin_transaction()) {
                throw new Exception('Failed to start transaction');
            }

            // Complete the order
            $orderProcessor = new OrderProcessor($this->conn, $this->user, $this->order_data);
            $result = $orderProcessor->completeOrder($invoice_number);

            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            // Commit transaction
            if (!$this->conn->commit()) {
                throw new Exception('Failed to commit transaction');
            }

            return [
                'success' => true,
                'message' => 'Payment processed successfully'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            error_log("Payment processing error: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }

    public function validatePaymentData()
    {
        $errors = [];

        // Validate user data
        if (empty($this->user['id']) || empty($this->user['email'])) {
            $errors[] = 'Invalid user data';
        }

        // Validate order data
        if (empty($this->order_data['items']) || empty($this->order_data['total'])) {
            $errors[] = 'Invalid order data';
        }

        // Validate shipping details
        $required_fields = ['full_name', 'email', 'phone', 'address', 'city', 'state', 'postal_code'];
        foreach ($required_fields as $field) {
            if (empty($this->shipping_details[$field])) {
                $errors[] = "Missing shipping field: $field";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }
}
