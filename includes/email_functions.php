<?php

/**
 * Email Functions for Ryvah Commerce
 * Contains functions for sending various types of emails
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/paypal_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send order notification email to admin
 * Only sends for non-ebook orders
 */
function sendOrderNotificationEmail($order_id)
{
    global $conn;

    try {
        // Fetch complete order details
        $order_data = getOrderDetailsForEmail($order_id);

        if (!$order_data) {
            logPayPalError('Order data not found for email notification', ['order_id' => $order_id]);
            return false;
        }

        // Check if order contains only ebooks - if so, don't send email
        if (isOnlyEbookOrder($order_data['items'])) {
            logPayPalError('Skipping email notification for ebook-only order', ['order_id' => $order_id]);
            return true; // Return true as this is expected behavior
        }

        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress(ADMIN_EMAIL);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Order Notification - Order #' . $order_data['order']['invoice_number'];
        $mail->Body = generateOrderNotificationHtml($order_data);
        $mail->AltBody = generateOrderNotificationText($order_data);

        $mail->send();

        // Log successful email
        logPayPalError('Order notification email sent successfully', [
            'order_id' => $order_id,
            'invoice_number' => $order_data['order']['invoice_number'],
            'recipient' => ADMIN_EMAIL
        ]);

        return true;
    } catch (Exception $e) {
        logPayPalError('Failed to send order notification email: ' . $e->getMessage(), [
            'order_id' => $order_id,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Get complete order details for email
 */
function getOrderDetailsForEmail($order_id)
{
    global $conn;

    try {
        // Get order details
        $stmt = $conn->prepare("
            SELECT o.id, o.invoice_number, o.total_amount, o.tax_amount, o.shipping_address, 
                   o.created_at, o.currency, o.paypal_order_id,
                   u.full_name, u.email, u.phone
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.payment_status = 'completed'
        ");

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) {
            return null;
        }

        // Get order items
        $stmt = $conn->prepare("
            SELECT oi.product_id, oi.quantity, oi.price, oi.subtotal, oi.tax_amount,
                   p.name, p.type, p.author, p.description
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
            ORDER BY p.name
        ");

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Parse shipping address
        $shipping_address = json_decode($order['shipping_address'], true);

        return [
            'order' => $order,
            'items' => $items,
            'shipping_address' => $shipping_address
        ];
    } catch (Exception $e) {
        logPayPalError('Error fetching order details for email: ' . $e->getMessage(), [
            'order_id' => $order_id
        ]);
        return null;
    }
}

/**
 * Check if order contains only ebooks
 */
function isOnlyEbookOrder($items)
{
    foreach ($items as $item) {
        if ($item['type'] !== 'ebook') {
            return false;
        }
    }
    return true;
}

/**
 * Generate HTML email content for order notification
 */
function generateOrderNotificationHtml($order_data)
{
    $order = $order_data['order'];
    $items = $order_data['items'];
    $address = $order_data['shipping_address'];

    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>New Order Notification - Ryvah Commerce</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                background-color: #f8f9fa;
            }
            .email-container { 
                max-width: 650px; 
                margin: 0 auto; 
                background-color: #ffffff;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .header { 
                background: linear-gradient(135deg, #007bff, #0056b3); 
                color: white; 
                padding: 40px 30px; 
                text-align: center;
                border-radius: 0;
            }
            .header h1 { 
                font-size: 28px; 
                margin-bottom: 10px;
                font-weight: 600;
            }
            .header p { 
                font-size: 18px; 
                opacity: 0.9;
                margin: 0;
            }
            .alert-badge {
                display: inline-block;
                background: #28a745;
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
                margin-top: 15px;
            }
            .content { 
                padding: 40px 30px; 
            }
            .section { 
                margin-bottom: 35px; 
            }
            .section h2 { 
                font-size: 20px; 
                margin-bottom: 20px; 
                color: #007bff;
                border-bottom: 2px solid #f1f3f4;
                padding-bottom: 10px;
            }
            .order-details { 
                background: #f8f9fa; 
                padding: 25px; 
                border-radius: 8px; 
                border-left: 4px solid #007bff;
            }
            .detail-row { 
                display: flex; 
                justify-content: space-between; 
                margin-bottom: 12px; 
            }
            .detail-row:last-child { 
                margin-bottom: 0; 
            }
            .detail-label { 
                font-weight: 600; 
                color: #495057;
            }
            .detail-value { 
                color: #212529;
            }
            .items-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px;
            }
            .items-table th, 
            .items-table td { 
                padding: 15px 12px; 
                text-align: left; 
                border-bottom: 1px solid #dee2e6;
            }
            .items-table th { 
                background-color: #f1f3f4; 
                font-weight: 600;
                color: #495057;
            }
            .items-table tbody tr:hover { 
                background-color: #f8f9fa; 
            }
            .total-row { 
                font-weight: 600; 
                background-color: #e9ecef !important;
                color: #495057;
            }
            .address-block { 
                background: #ffffff; 
                border: 1px solid #dee2e6; 
                border-radius: 6px; 
                padding: 20px;
                margin-top: 15px;
            }
            .footer { 
                background: #f1f3f4; 
                padding: 30px; 
                text-align: center; 
                border-top: 1px solid #dee2e6;
            }
            .footer p { 
                margin: 8px 0; 
                color: #6c757d;
            }
            .badge {
                display: inline-block;
                padding: 4px 8px;
                background: #17a2b8;
                color: white;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <h1>🛒 New Order Received!</h1>
                <p>Order #' . htmlspecialchars($order['invoice_number']) . '</p>
                <div class="alert-badge">Action Required</div>
            </div>
            
            <div class="content">
                <div class="section">
                    <h2>📋 Order Information</h2>
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">#' . htmlspecialchars($order['invoice_number']) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Order Date:</span>
                            <span class="detail-value">' . date('F j, Y \a\t g:i A', strtotime($order['created_at'])) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Customer:</span>
                            <span class="detail-value">' . htmlspecialchars($order['full_name']) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">' . htmlspecialchars($order['email']) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value">PayPal</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">PayPal Order ID:</span>
                            <span class="detail-value">' . htmlspecialchars($order['paypal_order_id']) . '</span>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h2>📦 Order Items</h2>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>';

    foreach ($items as $item) {
        $html .= '
                            <tr>
                                <td>
                                    <strong>' . htmlspecialchars($item['name']) . '</strong>';
        if (!empty($item['author'])) {
            $html .= '<br><small>by ' . htmlspecialchars($item['author']) . '</small>';
        }
        $html .= '</td>
                                <td>
                                    <span class="badge">' . ucfirst(htmlspecialchars($item['type'])) . '</span>
                                </td>
                                <td>' . intval($item['quantity']) . '</td>
                                <td>$' . number_format($item['price'], 2) . '</td>
                                <td>$' . number_format($item['subtotal'], 2) . '</td>
                            </tr>';
    }

    $html .= '
                            <tr class="total-row">
                                <td colspan="4"><strong>Total Amount:</strong></td>
                                <td><strong>$' . number_format($order['total_amount'], 2) . '</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="section">
                    <h2>🏠 Shipping Address</h2>
                    <div class="address-block">
                        <strong>' . htmlspecialchars($order['full_name']) . '</strong><br>
                        ' . htmlspecialchars($address['street']) . '<br>
                        ' . htmlspecialchars($address['city']) . ', ' . htmlspecialchars($address['state']) . ' ' . htmlspecialchars($address['postal_code']) . '<br>
                        ' . htmlspecialchars($address['country']) . '
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p><strong>Next Steps:</strong></p>
                <p>• Process and prepare the order for shipment</p>
                <p>• Update order status in the admin panel</p>
                <p>• Send tracking information to customer when shipped</p>
                <br>
                <p style="font-size: 12px; color: #6c757d;">
                    This is an automated notification from Ryvah Commerce.<br>
                    Please do not reply to this email.
                </p>
            </div>
        </div>
    </body>
    </html>';

    return $html;
}

/**
 * Generate plain text email content for order notification
 */
function generateOrderNotificationText($order_data)
{
    $order = $order_data['order'];
    $items = $order_data['items'];
    $address = $order_data['shipping_address'];

    $text = "NEW ORDER RECEIVED\n";
    $text .= "===================\n\n";

    $text .= "Order Information:\n";
    $text .= "- Order ID: #" . $order['invoice_number'] . "\n";
    $text .= "- Order Date: " . date('F j, Y \a\t g:i A', strtotime($order['created_at'])) . "\n";
    $text .= "- Customer: " . $order['full_name'] . "\n";
    $text .= "- Email: " . $order['email'] . "\n";
    $text .= "- Payment Method: PayPal\n";
    $text .= "- PayPal Order ID: " . $order['paypal_order_id'] . "\n\n";

    $text .= "Order Items:\n";
    $text .= "-------------\n";
    foreach ($items as $item) {
        $text .= "• " . $item['name'];
        if (!empty($item['author'])) {
            $text .= " by " . $item['author'];
        }
        $text .= " (" . ucfirst($item['type']) . ")\n";
        $text .= "  Quantity: " . $item['quantity'] . " | Price: $" . number_format($item['price'], 2) . " | Total: $" . number_format($item['subtotal'], 2) . "\n\n";
    }

    $text .= "TOTAL AMOUNT: $" . number_format($order['total_amount'], 2) . "\n\n";

    $text .= "Shipping Address:\n";
    $text .= "-----------------\n";
    $text .= $order['full_name'] . "\n";
    $text .= $address['street'] . "\n";
    $text .= $address['city'] . ", " . $address['state'] . " " . $address['postal_code'] . "\n";
    $text .= $address['country'] . "\n\n";

    $text .= "Next Steps:\n";
    $text .= "- Process and prepare the order for shipment\n";
    $text .= "- Update order status in the admin panel\n";
    $text .= "- Send tracking information to customer when shipped\n\n";

    $text .= "---\n";
    $text .= "This is an automated notification from Ryvah Commerce.\n";
    $text .= "Please do not reply to this email.";

    return $text;
}
