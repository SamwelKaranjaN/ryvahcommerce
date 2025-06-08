<?php

/**
 * Ryvah Commerce - Dependency Installer and Checker
 * This script checks and installs all required dependencies for the checkout system
 */

set_time_limit(300); // 5 minutes
ini_set('memory_limit', '512M');

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Ryvah Commerce - Dependency Installer</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}</style>";
echo "</head><body>";

echo "<h1>Ryvah Commerce - Dependency Installer</h1>";

// Check PHP version
echo "<h2>1. Checking PHP Version</h2>";
$phpVersion = phpversion();
echo "<p>Current PHP version: <strong>$phpVersion</strong></p>";

if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "<p class='success'>✓ PHP version is compatible</p>";
} else {
    echo "<p class='error'>✗ PHP version must be 7.4 or higher</p>";
    exit;
}

// Check required PHP extensions
echo "<h2>2. Checking PHP Extensions</h2>";
$requiredExtensions = ['curl', 'json', 'openssl', 'mysqli', 'session'];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✓ $ext extension is loaded</p>";
    } else {
        echo "<p class='error'>✗ $ext extension is required but not loaded</p>";
    }
}

// Check if Composer is installed
echo "<h2>3. Checking Composer</h2>";
$composerPath = null;

// Check common composer locations
$composerPaths = [
    'composer',
    'composer.phar',
    '/usr/local/bin/composer',
    '/usr/bin/composer',
    'C:\\ProgramData\\ComposerSetup\\bin\\composer.bat',
    'C:\\Users\\' . get_current_user() . '\\AppData\\Roaming\\Composer\\vendor\\bin\\composer'
];

foreach ($composerPaths as $path) {
    exec("$path --version 2>&1", $output, $returnCode);
    if ($returnCode === 0) {
        $composerPath = $path;
        echo "<p class='success'>✓ Composer found at: $path</p>";
        echo "<p class='info'>" . implode(' ', $output) . "</p>";
        break;
    }
    $output = [];
}

if (!$composerPath) {
    echo "<p class='warning'>⚠ Composer not found in common locations</p>";
    echo "<p>Please install Composer from <a href='https://getcomposer.org'>https://getcomposer.org</a></p>";
} else {
    // Check if composer.json exists
    echo "<h2>4. Checking composer.json</h2>";
    if (file_exists('composer.json')) {
        echo "<p class='success'>✓ composer.json found</p>";

        // Read and display composer.json
        $composerJson = json_decode(file_get_contents('composer.json'), true);
        echo "<pre>" . json_encode($composerJson, JSON_PRETTY_PRINT) . "</pre>";

        // Install dependencies
        echo "<h2>5. Installing Dependencies</h2>";
        echo "<p class='info'>Running: $composerPath install</p>";

        exec("$composerPath install 2>&1", $installOutput, $installReturnCode);

        echo "<pre style='background:#f5f5f5;padding:10px;border-radius:5px;'>";
        echo implode("\n", $installOutput);
        echo "</pre>";

        if ($installReturnCode === 0) {
            echo "<p class='success'>✓ Dependencies installed successfully</p>";
        } else {
            echo "<p class='error'>✗ Failed to install dependencies</p>";
            echo "<p>Try running manually: <code>$composerPath install</code></p>";
        }
    } else {
        echo "<p class='error'>✗ composer.json not found</p>";
        echo "<p>Creating composer.json...</p>";

        $composerConfig = [
            "name" => "ryvahcommerce/ecommerce",
            "description" => "Ryvah Commerce e-commerce application",
            "require" => [
                "php" => ">=7.4",
                "paypal/paypal-server-sdk" => "^1.1",
                "phpmailer/phpmailer" => "^6.8",
                "paypal/paypal-checkout-sdk" => "^1.0"
            ],
            "autoload" => [
                "psr-4" => [
                    "RyvahCommerce\\" => "includes/"
                ]
            ]
        ];

        file_put_contents('composer.json', json_encode($composerConfig, JSON_PRETTY_PRINT));
        echo "<p class='success'>✓ composer.json created</p>";

        // Install dependencies
        echo "<p class='info'>Installing dependencies...</p>";
        exec("$composerPath install 2>&1", $installOutput, $installReturnCode);

        echo "<pre style='background:#f5f5f5;padding:10px;border-radius:5px;'>";
        echo implode("\n", $installOutput);
        echo "</pre>";

        if ($installReturnCode === 0) {
            echo "<p class='success'>✓ Dependencies installed successfully</p>";
        } else {
            echo "<p class='error'>✗ Failed to install dependencies</p>";
        }
    }
}

// Check if vendor/autoload.php exists
echo "<h2>6. Checking Autoloader</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "<p class='success'>✓ Composer autoloader found</p>";

    // Test loading PayPal SDK
    try {
        require_once 'vendor/autoload.php';

        if (class_exists('PayPalCheckoutSdk\Core\ProductionEnvironment')) {
            echo "<p class='success'>✓ PayPal Checkout SDK loaded successfully</p>";
        } else {
            echo "<p class='error'>✗ PayPal Checkout SDK not found</p>";
        }

        if (class_exists('PayPal\Api\Payment')) {
            echo "<p class='success'>✓ PayPal Server SDK loaded successfully</p>";
        } else {
            echo "<p class='warning'>⚠ PayPal Server SDK not found (optional)</p>";
        }

        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            echo "<p class='success'>✓ PHPMailer loaded successfully</p>";
        } else {
            echo "<p class='error'>✗ PHPMailer not found</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error loading dependencies: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>✗ Composer autoloader not found</p>";
    echo "<p>Please run: <code>composer install</code></p>";
}

// Check database connection
echo "<h2>7. Testing Database Connection</h2>";
try {
    if (file_exists('includes/bootstrap.php')) {
        require_once 'includes/bootstrap.php';

        if (isset($conn) && $conn instanceof mysqli) {
            if ($conn->ping()) {
                echo "<p class='success'>✓ Database connection successful</p>";

                // Test required tables
                $requiredTables = [
                    'users',
                    'products',
                    'cart',
                    'orders',
                    'order_items',
                    'addresses',
                    'tax_settings',
                    'shipping_fees'
                ];

                foreach ($requiredTables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->num_rows > 0) {
                        echo "<p class='success'>✓ Table '$table' exists</p>";
                    } else {
                        echo "<p class='warning'>⚠ Table '$table' not found</p>";
                    }
                }
            } else {
                echo "<p class='error'>✗ Database connection failed</p>";
            }
        } else {
            echo "<p class='error'>✗ Database connection object not found</p>";
        }
    } else {
        echo "<p class='error'>✗ bootstrap.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection error: " . $e->getMessage() . "</p>";
}

// Check PayPal configuration
echo "<h2>8. Checking PayPal Configuration</h2>";
try {
    if (file_exists('includes/paypal_config.php')) {
        require_once 'includes/paypal_config.php';

        if (function_exists('validatePayPalConfig')) {
            if (validatePayPalConfig()) {
                echo "<p class='success'>✓ PayPal configuration is valid</p>";
            } else {
                echo "<p class='error'>✗ PayPal configuration is invalid</p>";
            }
        }

        // Check constants
        $paypalConstants = [
            'PAYPAL_CLIENT_ID',
            'PAYPAL_CLIENT_SECRET',
            'PAYPAL_ENVIRONMENT',
            'SITE_DOMAIN',
            'PAYPAL_RETURN_URL',
            'PAYPAL_CANCEL_URL'
        ];

        foreach ($paypalConstants as $constant) {
            if (defined($constant)) {
                echo "<p class='success'>✓ $constant is defined</p>";
            } else {
                echo "<p class='error'>✗ $constant is not defined</p>";
            }
        }
    } else {
        echo "<p class='error'>✗ paypal_config.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ PayPal configuration error: " . $e->getMessage() . "</p>";
}

// Check file permissions
echo "<h2>9. Checking File Permissions</h2>";
$checkPaths = [
    'logs' => 'directory',
    'vendor' => 'directory',
    'checkout' => 'directory',
    'checkout/calculate_totals.php' => 'file',
    'checkout/simple_create_order.php' => 'file',
    'checkout/simple_capture.php' => 'file'
];

foreach ($checkPaths as $path => $type) {
    if (file_exists($path)) {
        $perms = fileperms($path);
        $readable = is_readable($path);
        $writable = is_writable($path);

        echo "<p class='success'>✓ $path exists";
        if ($type === 'directory') {
            echo " (readable: " . ($readable ? 'yes' : 'no') . ", writable: " . ($writable ? 'yes' : 'no') . ")";
        } else {
            echo " (readable: " . ($readable ? 'yes' : 'no') . ")";
        }
        echo "</p>";
    } else {
        echo "<p class='warning'>⚠ $path not found</p>";

        if ($type === 'directory') {
            echo "<p class='info'>Creating directory: $path</p>";
            if (mkdir($path, 0755, true)) {
                echo "<p class='success'>✓ Directory created successfully</p>";
            } else {
                echo "<p class='error'>✗ Failed to create directory</p>";
            }
        }
    }
}

// Final summary
echo "<h2>10. Installation Summary</h2>";
echo "<p class='info'>Installation check completed. Please review any errors or warnings above.</p>";

if (!file_exists('logs')) {
    mkdir('logs', 0755, true);
    echo "<p class='success'>✓ Created logs directory</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Ensure all required PHP extensions are installed</li>";
echo "<li>Run <code>composer install</code> if dependencies are not installed</li>";
echo "<li>Configure your database connection in <code>includes/bootstrap.php</code></li>";
echo "<li>Configure PayPal credentials in <code>includes/paypal_config.php</code></li>";
echo "<li>Test the checkout flow: <a href='checkout/simple_checkout.php'>Go to Checkout</a></li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>PayPal SDK Test:</strong></p>";

if (file_exists('vendor/autoload.php')) {
    try {
        require_once 'vendor/autoload.php';

        echo "<p class='info'>Testing PayPal SDK initialization...</p>";

        // Test PayPal environment creation
        if (defined('PAYPAL_CLIENT_ID') && defined('PAYPAL_CLIENT_SECRET')) {
            $environment = new \PayPalCheckoutSdk\Core\ProductionEnvironment(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET);
            $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);

            echo "<p class='success'>✓ PayPal SDK initialized successfully</p>";
            echo "<p class='info'>Environment: " . (PAYPAL_ENVIRONMENT ?? 'production') . "</p>";
        } else {
            echo "<p class='error'>✗ PayPal credentials not configured</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ PayPal SDK test failed: " . $e->getMessage() . "</p>";
    }
}

echo "</body></html>";