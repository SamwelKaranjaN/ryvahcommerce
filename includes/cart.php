<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function debug($data, $label = '')
{
    error_log(($label ? $label . ': ' : '') . print_r($data, true));
}

require_once 'bootstrap.php';

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
            $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
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

    debug('Transferring session cart to database for user: ' . $user_id);
    debug($_SESSION['cart'] ?? [], 'Session cart before transfer');

    $result = ['success' => true, 'transferred' => 0, 'errors' => []];

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            try {
                // Validate product exists
                $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $product_result = $stmt->get_result();

                if ($product_result->num_rows === 0) {
                    $error_msg = "Product ID {$product_id} not found, skipping transfer";
                    debug($error_msg);
                    $result['errors'][] = $error_msg;
                    continue;
                }

                // Check if item already exists in cart
                $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                $cart_result = $stmt->get_result();

                if ($cart_result->num_rows > 0) {
                    // Update quantity
                    $row = $cart_result->fetch_assoc();
                    $new_quantity = $row['quantity'] + $quantity;
                    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
                    debug("Updating existing cart item: product_id={$product_id}, old_qty={$row['quantity']}, new_qty={$new_quantity}");
                } else {
                    // Insert new item
                    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                    debug("Inserting new cart item: product_id={$product_id}, quantity={$quantity}");
                }

                if ($stmt->execute()) {
                    $result['transferred']++;
                    debug("Successfully transferred product {$product_id}");
                } else {
                    $error_msg = "Error transferring product {$product_id}: " . $stmt->error;
                    debug($error_msg);
                    $result['errors'][] = $error_msg;
                    $result['success'] = false;
                }
            } catch (Exception $e) {
                $error_msg = "Exception transferring product {$product_id}: " . $e->getMessage();
                debug($error_msg);
                $result['errors'][] = $error_msg;
                $result['success'] = false;
            }
        }

        // Clear session cart only if transfer was successful
        if ($result['success'] || $result['transferred'] > 0) {
            $_SESSION['cart'] = [];
            debug('Session cart cleared after transfer');
        }
    } else {
        debug('No items in session cart to transfer');
    }

    debug($result, 'Transfer result');
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
    $response = ['success' => false, 'message' => 'Invalid action'];

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $response = addToCart($_POST['product_id'], $_POST['quantity'] ?? 1);
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
                        $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            $row['quantity'] = $_SESSION['cart'][$row['product_id']];
                            $cart_items[] = $row;
                            $total += $row['price'] * $row['quantity'];
                        }
                    }
                }
                echo json_encode(['success' => true, 'items' => $cart_items, 'total' => $total]);
                break;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
