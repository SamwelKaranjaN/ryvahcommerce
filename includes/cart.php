<?php
// Disable error display for AJAX requests
if (isset($_POST['action'])) {
    error_reporting(0);
    ini_set('display_errors', 0);
}

function debug($data, $label = '')
{
    // Only log to error log, never display
    error_log(($label ? $label . ': ' : '') . print_r($data, true));
}

// Start output buffering to catch any unwanted output
if (isset($_POST['action'])) {
    ob_start();
}

require_once 'bootstrap.php';

// Clean any unwanted output for AJAX requests
if (isset($_POST['action']) && ob_get_level()) {
    ob_clean();
}

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Get cart items from session or database
 * @return array Cart data including items and total
 */
function getCartItems()
{
    global $conn;

    debug('Getting cart items');

    $items = [];
    $total = 0;

    if (isset($_SESSION['user_id'])) {
        // Clean cart first (remove orphaned items)
        verifyAndCleanCart($_SESSION['user_id']);

        // Get items from database for logged-in users
        $stmt = $conn->prepare("
            SELECT c.*, p.name, p.price, p.thumbs, p.stock, p.type 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'id' => $row['product_id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'quantity' => $row['quantity'],
                'stock' => $row['stock'],
                'thumbs' => $row['thumbs'],
                'type' => $row['type']
            ];
            $total += $row['price'] * $row['quantity'];
        }
    } else {
        // Get items from session for guests
        $cart = $_SESSION['cart'] ?? [];
        if (!empty($cart)) {
            $product_ids = array_keys($cart);
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';

            $sql = "SELECT id, name, price, stock, thumbs, type FROM products WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);

            // Fix for bind_param with spread operator issue
            $types = str_repeat('i', count($product_ids));
            $stmt->bind_param($types, ...$product_ids);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($product = $result->fetch_assoc()) {
                $quantity = $cart[$product['id']];
                $items[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'stock' => $product['stock'],
                    'thumbs' => $product['thumbs'],
                    'type' => $product['type']
                ];
                $total += $product['price'] * $quantity;
            }
        }
    }

    debug(['items' => $items, 'total' => $total], 'Cart Data');

    return [
        'items' => $items,
        'total' => $total
    ];
}

/**
 * Add item to cart
 * @param int $product_id Product ID
 * @param int $quantity Quantity to add
 * @return array Response with success status and message
 */
function addToCart($product_id, $quantity = 1)
{
    global $conn;

    debug(['product_id' => $product_id, 'quantity' => $quantity], 'Adding to cart');

    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        return ['success' => false, 'message' => 'Product not found'];
    }

    if (!isset($_SESSION['user_id'])) {
        // Handle guest cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }

        return ['success' => true, 'message' => 'Product added to cart', 'is_guest' => true];
    }

    $user_id = $_SESSION['user_id'];

    // Check if item already exists in cart
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;

        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
    } else {
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    }

    $success = $stmt->execute();
    debug(['success' => $success], 'Add to cart result');

    return ['success' => $success, 'message' => 'Product added to cart', 'is_guest' => false];
}

/**
 * Update cart item quantity
 * @param int $product_id Product ID
 * @param int $quantity New quantity
 * @return array Response with success status and message
 */
function updateCartQuantity($product_id, $quantity)
{
    global $conn;

    debug(['product_id' => $product_id, 'quantity' => $quantity], 'Updating cart quantity');

    if (!isset($_SESSION['user_id'])) {
        debug('No user ID in session');
        return ['success' => false, 'message' => 'User not logged in'];
    }

    $user_id = $_SESSION['user_id'];

    if ($quantity < 1) {
        return removeFromCart($product_id);
    }

    if ($quantity <= 0) {
        // Remove item
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    }

    $success = $stmt->execute();
    debug(['success' => $success], 'Update cart result');

    return ['success' => $success, 'message' => 'Cart updated'];
}

/**
 * Remove item from cart
 * @param int $product_id Product ID
 * @return array Response with success status and message
 */
function removeFromCart($product_id)
{
    global $conn;

    debug(['product_id' => $product_id], 'Removing from cart');

    if (!isset($_SESSION['user_id'])) {
        debug('No user ID in session');
        return ['success' => false, 'message' => 'User not logged in'];
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);

    $success = $stmt->execute();
    debug(['success' => $success], 'Remove from cart result');

    return ['success' => $success, 'message' => 'Product removed from cart'];
}

/**
 * Clear the cart
 * @return void
 */
function clearCart()
{
    global $conn;

    debug('Clearing cart');

    if (!isset($_SESSION['user_id'])) {
        debug('No user ID in session');
        return;
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    unset($_SESSION['cart']);
}

/**
 * Get cart item count
 * @return int Number of items in cart
 */
function getCartItemCount()
{
    global $conn;
    $count = 0;

    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'] ?? 0;
    } else {
        $count = array_sum($_SESSION['cart'] ?? []);
    }

    return $count;
}

/**
 * Check if cart is empty
 * @return bool True if cart is empty
 */
function isCartEmpty()
{
    global $conn;

    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] == 0;
    }

    return empty($_SESSION['cart']);
}

/**
 * Transfer session cart to database when user logs in
 * @param int $user_id User ID
 * @return array Result with success status and message
 */
function transferSessionCartToDatabase($user_id)
{
    global $conn;

    debug('=== STARTING CART TRANSFER FOR USER: ' . $user_id . ' ===');
    debug('Transferring session cart to database for user: ' . $user_id);
    debug($_SESSION['cart'] ?? [], 'Session cart before transfer');
    debug($_SESSION['checkout_cart'] ?? [], 'Session checkout_cart before transfer');
    debug($_SESSION['temp_cart'] ?? [], 'Session temp_cart before transfer');

    $result = ['success' => true, 'transferred' => 0, 'errors' => [], 'merged' => 0];

    // Check for both regular session cart and temporary checkout cart
    $session_carts = [];
    if (!empty($_SESSION['cart'])) {
        $session_carts['cart'] = $_SESSION['cart'];
    }
    if (!empty($_SESSION['checkout_cart'])) {
        $session_carts['checkout_cart'] = $_SESSION['checkout_cart'];
    }
    if (!empty($_SESSION['temp_cart'])) {
        $session_carts['temp_cart'] = $_SESSION['temp_cart'];
    }

    if (!empty($session_carts)) {
        // Begin transaction for data integrity
        $conn->begin_transaction();

        try {
            foreach ($session_carts as $cart_type => $cart_items) {
                debug("Processing {$cart_type} with items: " . print_r($cart_items, true));

                foreach ($cart_items as $product_id => $quantity) {
                    // Handle different cart formats
                    if (is_array($quantity)) {
                        // For temp_cart format: [{'id': x, 'quantity': y}, ...]
                        if (isset($quantity['id']) && isset($quantity['quantity'])) {
                            $product_id = $quantity['id'];
                            $quantity = $quantity['quantity'];
                        } else {
                            continue; // Skip invalid format
                        }
                    }

                    // Validate product exists and has adequate stock
                    $stmt = $conn->prepare("SELECT id, name, stock_quantity FROM products WHERE id = ?");
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $product_result = $stmt->get_result();

                    if ($product_result->num_rows === 0) {
                        $error_msg = "Product ID {$product_id} not found, skipping transfer";
                        debug($error_msg);
                        $result['errors'][] = $error_msg;
                        continue;
                    }

                    $product = $product_result->fetch_assoc();

                    // Validate quantity
                    if ($quantity <= 0) {
                        $error_msg = "Invalid quantity {$quantity} for product {$product_id}, skipping";
                        debug($error_msg);
                        $result['errors'][] = $error_msg;
                        continue;
                    }

                    // Check stock availability
                    if ($product['stock_quantity'] < $quantity) {
                        $error_msg = "Insufficient stock for product {$product['name']} (requested: {$quantity}, available: {$product['stock_quantity']})";
                        debug($error_msg);
                        $result['errors'][] = $error_msg;
                        // Adjust quantity to available stock
                        $quantity = $product['stock_quantity'];
                        if ($quantity <= 0) continue;
                    }

                    // Check if item already exists in user's database cart
                    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $user_id, $product_id);
                    $stmt->execute();
                    $cart_result = $stmt->get_result();

                    if ($cart_result->num_rows > 0) {
                        // Update existing quantity (merge)
                        $row = $cart_result->fetch_assoc();
                        $new_quantity = $row['quantity'] + $quantity;

                        // Check if merged quantity exceeds stock
                        if ($new_quantity > $product['stock_quantity']) {
                            $new_quantity = $product['stock_quantity'];
                            $result['errors'][] = "Merged quantity for {$product['name']} limited to available stock ({$product['stock_quantity']})";
                        }

                        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                        $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
                        debug("Merging cart item: product_id={$product_id}, old_qty={$row['quantity']}, session_qty={$quantity}, new_qty={$new_quantity}");
                        $result['merged']++;
                    } else {
                        // Insert new item
                        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                        debug("Adding new cart item: user_id={$user_id}, product_id={$product_id}, quantity={$quantity}");
                    }

                    if ($stmt->execute()) {
                        $result['transferred']++;
                        debug("Successfully processed product {$product_id} from {$cart_type}");
                    } else {
                        $error_msg = "Database error processing product {$product_id}: " . $stmt->error;
                        debug($error_msg);
                        $result['errors'][] = $error_msg;
                        throw new Exception($error_msg);
                    }
                }
            }

            // Commit transaction
            $conn->commit();

            // Clear all session carts only if transfer was successful
            if ($result['transferred'] > 0) {
                unset($_SESSION['cart']);
                unset($_SESSION['checkout_cart']);
                unset($_SESSION['temp_cart']);
                debug('All session carts cleared after successful transfer');
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error_msg = "Transaction failed during cart transfer: " . $e->getMessage();
            debug($error_msg);
            $result['errors'][] = $error_msg;
            $result['success'] = false;
        }
    } else {
        debug('No items in any session cart to transfer');
    }

    debug($result, 'Transfer result');
    return $result;
}

/**
 * Store current session cart as checkout cart when user goes to login from checkout
 * This preserves the cart during the login process
 */
function preserveCartForCheckout()
{
    if (!empty($_SESSION['cart'])) {
        $_SESSION['checkout_cart'] = $_SESSION['cart'];
        $_SESSION['redirect_after_login'] = 'checkout';
        debug('Cart preserved for checkout login process');
        return true;
    }
    return false;
}

/**
 * Enhanced cart merge for checkout process
 * Specifically designed for the checkout -> login -> checkout flow
 */
function mergeCheckoutCart($user_id)
{
    global $conn;

    $result = ['success' => true, 'merged' => 0, 'errors' => []];

    if (!empty($_SESSION['checkout_cart'])) {
        debug('Merging checkout cart for user: ' . $user_id);

        // Use the enhanced transfer function
        $transfer_result = transferSessionCartToDatabase($user_id);

        // Clear checkout cart after merge
        unset($_SESSION['checkout_cart']);

        return $transfer_result;
    }

    return $result;
}

/**
 * Verify and clean cart items (remove items referencing non-existent products)
 * @param int $user_id User ID
 * @return int Number of items cleaned
 */
function verifyAndCleanCart($user_id = null)
{
    global $conn;

    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }

    if (!$user_id) {
        return 0;
    }

    // Remove any cart items that reference non-existent products
    $stmt = $conn->prepare("DELETE c FROM cart c LEFT JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND p.id IS NULL");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $deleted = $stmt->affected_rows;
    if ($deleted > 0) {
        debug("Cleaned {$deleted} orphaned cart items for user {$user_id}");
    }

    return $deleted;
}

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !defined('PAYPAL_ORDER_PROCESSING')) {
    // Set JSON header at the beginning
    header('Content-Type: application/json');

    $response = ['success' => false, 'message' => 'Invalid action'];

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $response = addToCart($_POST['product_id'], $_POST['quantity'] ?? 1);
                // Add cart count to response
                $response['cart_count'] = getCartItemCount();
                break;
            case 'update':
                if (!isset($_SESSION['user_id'])) {
                    // Handle guest cart update
                    if (isset($_SESSION['cart'][$_POST['product_id']])) {
                        $_SESSION['cart'][$_POST['product_id']] = $_POST['quantity'];
                        $response = ['success' => true, 'message' => 'Cart updated', 'is_guest' => true];
                    }
                } else {
                    $response = updateCartQuantity($_POST['product_id'], $_POST['quantity']);
                }
                // Add cart count to response
                $response['cart_count'] = getCartItemCount();
                break;
            case 'remove':
                if (!isset($_SESSION['user_id'])) {
                    // Handle guest cart removal
                    if (isset($_SESSION['cart'][$_POST['product_id']])) {
                        unset($_SESSION['cart'][$_POST['product_id']]);
                        $response = ['success' => true, 'message' => 'Product removed from cart', 'is_guest' => true];
                    }
                } else {
                    $response = removeFromCart($_POST['product_id']);
                }
                // Add cart count to response
                $response['cart_count'] = getCartItemCount();
                break;
            case 'get':
                $cart_items = [];
                $total = 0;
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $cart_items[] = $row;
                        $total += $row['price'] * $row['quantity'];
                    }
                } else {
                    // For guest users, retrieve from session
                    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                        $product_ids = array_keys($_SESSION['cart']);
                        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';

                        $sql = "SELECT id as product_id, name, price FROM products WHERE id IN ($placeholders)";
                        $stmt = $conn->prepare($sql);

                        // Fix for bind_param with spread operator issue
                        $types = str_repeat('i', count($product_ids));
                        $stmt->bind_param($types, ...$product_ids);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            $row['quantity'] = $_SESSION['cart'][$row['product_id']];
                            $cart_items[] = $row;
                            $total += $row['price'] * $row['quantity'];
                        }
                    }
                }
                $response = ['success' => true, 'items' => $cart_items, 'total' => $total];
                break;
            case 'get_totals':
                if (!isset($_SESSION['user_id'])) {
                    $response = ['success' => false, 'message' => 'User not logged in'];
                    break;
                }

                $cart_data = getCartItems();
                $cart_items = $cart_data['items'] ?? [];

                if (empty($cart_items)) {
                    $response = [
                        'success' => true,
                        'subtotal' => 0,
                        'tax_amount' => 0,
                        'shipping_amount' => 0,
                        'grand_total' => 0,
                        'shipping_breakdown' => []
                    ];
                    break;
                }

                // Calculate subtotal
                $subtotal = 0;
                foreach ($cart_items as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }

                // Get user's default address for tax calculation
                $user_id = $_SESSION['user_id'];
                $stmt = $conn->prepare("SELECT state, country FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC LIMIT 1");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $address_result = $stmt->get_result();
                $default_address = $address_result->fetch_assoc();

                // Calculate tax for each item (ebooks are not taxed)
                $tax_amount = 0;
                if ($default_address) {
                    require_once '../includes/paypal_config.php'; // For getTaxRate function
                    foreach ($cart_items as $item) {
                        $tax_rate = getTaxRate($default_address['state'], $default_address['country'], $item['type']);
                        $item_tax = ($item['price'] * $item['quantity']) * $tax_rate;
                        $tax_amount += $item_tax;
                    }
                }

                // Calculate shipping using existing function
                require_once '../checkout/shipping_calculator.php';
                $shipping_result = calculateTotalShipping($cart_items);
                $shipping_amount = $shipping_result['total_shipping'];

                $grand_total = $subtotal + $tax_amount + $shipping_amount;

                $response = [
                    'success' => true,
                    'subtotal' => $subtotal,
                    'tax_amount' => $tax_amount,
                    'shipping_amount' => $shipping_amount,
                    'grand_total' => $grand_total,
                    'shipping_breakdown' => $shipping_result['breakdown'] ?? []
                ];
                break;
        }
    }

    // Clean output buffer to ensure no extra content
    if (ob_get_level()) {
        ob_clean();
    }

    echo json_encode($response);
    exit;
}