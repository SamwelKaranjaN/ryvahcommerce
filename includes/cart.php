<?php
session_start();
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
    $items = [];
    $total = 0;

    if (isset($_SESSION['user_id'])) {
        // Get items from database for logged-in users
        $stmt = $conn->prepare("
            SELECT c.*, p.name, p.price, p.thumbs, p.stock 
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
                'thumbs' => $row['thumbs']
            ];
            $total += $row['price'] * $row['quantity'];
        }
    } else {
        // Get items from session for guests
        $cart = $_SESSION['cart'] ?? [];
        if (!empty($cart)) {
            $product_ids = array_keys($cart);
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';

            $sql = "SELECT id, name, price, stock, thumbs FROM products WHERE id IN ($placeholders)";
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
                    'thumbs' => $product['thumbs']
                ];
                $total += $product['price'] * $quantity;
            }
        }
    }

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

    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        return ['success' => false, 'message' => 'Product not found'];
    }

    if (isset($_SESSION['user_id'])) {
        // Handle logged-in user cart
        $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_item = $result->fetch_assoc();

        $current_quantity = $cart_item ? $cart_item['quantity'] : 0;
        $new_quantity = $current_quantity + $quantity;

        if ($new_quantity > $product['stock']) {
            return ['success' => false, 'message' => 'Not enough stock available'];
        }

        if ($cart_item) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $new_quantity, $_SESSION['user_id'], $product_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $_SESSION['user_id'], $product_id, $quantity);
        }
        $stmt->execute();
    } else {
        // Handle guest cart
        $current_quantity = $_SESSION['cart'][$product_id] ?? 0;
        $new_quantity = $current_quantity + $quantity;

        if ($new_quantity > $product['stock']) {
            return ['success' => false, 'message' => 'Not enough stock available'];
        }

        $_SESSION['cart'][$product_id] = $new_quantity;
    }

    return ['success' => true, 'message' => 'Product added to cart'];
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

    if ($quantity < 1) {
        return removeFromCart($product_id);
    }

    // Check stock availability
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product || $quantity > $product['stock']) {
        return ['success' => false, 'message' => 'Not enough stock available'];
    }

    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $_SESSION['user_id'], $product_id);
        $stmt->execute();
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    return ['success' => true, 'message' => 'Cart updated'];
}

/**
 * Remove item from cart
 * @param int $product_id Product ID
 * @return array Response with success status and message
 */
function removeFromCart($product_id)
{
    global $conn;

    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
        $stmt->execute();
    } else {
        unset($_SESSION['cart'][$product_id]);
    }

    return ['success' => true, 'message' => 'Product removed from cart'];
}

/**
 * Clear the cart
 * @return void
 */
function clearCart()
{
    global $conn;

    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
    }
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
 * @return void
 */
function transferSessionCartToDatabase($user_id)
{
    global $conn;

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
            $stmt->execute();
        }
        // Clear session cart
        $_SESSION['cart'] = [];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Skip cart operations if this is a payment request
    if (isset($_POST['is_payment']) && $_POST['is_payment'] === '1') {
        exit;
    }

    $response = ['success' => false, 'message' => 'Invalid action'];

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $response = addToCart($_POST['product_id'], $_POST['quantity'] ?? 1);
                break;
            case 'update':
                $response = updateCartQuantity($_POST['product_id'], $_POST['quantity']);
                break;
            case 'remove':
                $response = removeFromCart($_POST['product_id']);
                break;
            case 'get':
                $response = getCartItems();
                break;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}