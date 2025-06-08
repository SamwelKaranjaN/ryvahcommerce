<?php

/**
 * Ryvah Commerce - Dependency Setup Script
 */

set_time_limit(300);
ini_set('memory_limit', '512M');

echo "Ryvah Commerce - Dependency Setup\n";
echo "==================================\n\n";

// Check PHP version
echo "1. Checking PHP Version...\n";
$phpVersion = phpversion();
echo "Current PHP version: $phpVersion\n";

if (version_compare($phpVersion, '7.4.0', '<')) {
    echo "ERROR: PHP version must be 7.4 or higher\n";
    exit(1);
}
echo "✓ PHP version is compatible\n\n";

// Check required extensions
echo "2. Checking PHP Extensions...\n";
$requiredExtensions = ['curl', 'json', 'openssl', 'mysqli', 'session'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ $ext extension loaded\n";
    } else {
        echo "✗ $ext extension missing\n";
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "ERROR: Missing required extensions: " . implode(', ', $missingExtensions) . "\n";
    exit(1);
}
echo "\n";

// Check/create composer.json
echo "3. Setting up Composer configuration...\n";
if (!file_exists('composer.json')) {
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
    echo "✓ Created composer.json\n";
} else {
    echo "✓ composer.json exists\n";
}

// Install dependencies
echo "4. Installing dependencies...\n";
echo "Running: composer install\n";

$output = [];
$returnCode = 0;
exec('composer install 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✓ Dependencies installed successfully\n";
} else {
    echo "Installation output:\n";
    echo implode("\n", $output) . "\n";

    if ($returnCode !== 0) {
        echo "WARNING: Composer install returned non-zero exit code\n";
    }
}

// Check autoloader
echo "\n5. Checking autoloader...\n";
if (file_exists('vendor/autoload.php')) {
    echo "✓ Autoloader found\n";

    require_once 'vendor/autoload.php';

    if (class_exists('PayPalCheckoutSdk\Core\ProductionEnvironment')) {
        echo "✓ PayPal Checkout SDK loaded\n";
    } else {
        echo "✗ PayPal Checkout SDK not found\n";
    }

    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✓ PHPMailer loaded\n";
    } else {
        echo "✗ PHPMailer not found\n";
    }
} else {
    echo "✗ Autoloader not found\n";
}

// Create directories
echo "\n6. Creating required directories...\n";
$directories = ['logs', 'uploads', 'tmp'];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ Created directory: $dir\n";
        } else {
            echo "✗ Failed to create directory: $dir\n";
        }
    } else {
        echo "✓ Directory exists: $dir\n";
    }
}

echo "\nSetup completed!\n";
echo "\nNext steps:\n";
echo "1. Configure your database connection in includes/bootstrap.php\n";
echo "2. Configure PayPal credentials in includes/paypal_config.php\n";
echo "3. Test the checkout system\n";