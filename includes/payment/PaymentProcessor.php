<?php
require_once __DIR__ . '/../bootstrap.php';

class PaymentProcessor
{
    private $conn;
    private $user;
    private $order_data;
    private $shipping_details;
    private $invoice_number;
    private $is_development;

    public function __construct($conn, $user, $order_data, $shipping_details)
    {
        $this->conn = $conn;
        $this->user = $user;
        $this->order_data = $order_data;
        $this->shipping_details = $shipping_details;
        $this->is_development = defined('ENVIRONMENT') && ENVIRONMENT === 'development';
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
            $error_message = $this->is_development ?
                'Failed to create pending order: ' . $e->getMessage() :
                'Unable to create order. Please try again or contact support.';

            error_log("Error creating pending order: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $error_message
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
                SELECT o.*, u.email, u.full_name 
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.invoice_number = ? 
                AND o.user_id = ? 
                AND o.payment_status = 'pending'
            ");

            if (!$stmt) {
                throw new Exception('Database error: Unable to verify order');
            }

            $stmt->bind_param("si", $invoice_number, $this->user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();

            if (!$order) {
                throw new Exception('Order not found or already processed');
            }

            // Verify amount matches
            if ($order['total_amount'] != $this->order_data['total']) {
                throw new Exception('Order amount mismatch. Please refresh and try again.');
            }

            // Start transaction
            if (!$this->conn->begin_transaction()) {
                throw new Exception('Database error: Unable to start transaction');
            }

            // Complete the order
            $orderProcessor = new OrderProcessor($this->conn, $this->user, $this->order_data);
            $result = $orderProcessor->completeOrder($invoice_number);

            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            // Commit transaction
            if (!$this->conn->commit()) {
                throw new Exception('Database error: Unable to complete transaction');
            }

            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'order_id' => $order['id']
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->conn->inTransaction()) {
                $this->conn->rollback();
            }

            $error_message = $this->is_development ?
                'Payment processing failed: ' . $e->getMessage() :
                'Unable to process payment. Please try again or contact support.';

            error_log("Payment processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $error_message,
                'error_code' => $e->getCode(),
                'debug_info' => $this->is_development ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
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

        // Validate payment method if provided
        if (isset($this->order_data['payment_method'])) {
            $valid_methods = ['paypal', 'stripe', 'credit_card'];
            if (!in_array($this->order_data['payment_method'], $valid_methods)) {
                $errors[] = 'Invalid payment method';
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
