# PayPal Configuration Setup Guide

## Environment Variables Setup

Your PayPal configuration now uses environment variables for security. You need to set these environment variables on your server.

### Required Environment Variables

```bash
# PayPal Production Credentials (REQUIRED)
PAYPAL_PRODUCTION_CLIENT_ID=your_production_client_id_here
PAYPAL_PRODUCTION_CLIENT_SECRET=your_production_client_secret_here

# PayPal Sandbox Credentials (OPTIONAL - for testing)
PAYPAL_SANDBOX_CLIENT_ID=your_sandbox_client_id_here
PAYPAL_SANDBOX_CLIENT_SECRET=your_sandbox_client_secret_here
```

### How to Set Environment Variables

#### Option 1: Using .htaccess (Apache)
Create or update your `.htaccess` file in your root directory:

```apache
# PayPal Environment Variables
SetEnv PAYPAL_PRODUCTION_CLIENT_ID "your_production_client_id_here"
SetEnv PAYPAL_PRODUCTION_CLIENT_SECRET "your_production_client_secret_here"
```

#### Option 2: Using .env file with PHP
Create a `.env` file in your root directory:

```env
PAYPAL_PRODUCTION_CLIENT_ID=your_production_client_id_here
PAYPAL_PRODUCTION_CLIENT_SECRET=your_production_client_secret_here
```

Then load it in your application bootstrap:

```php
// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}
```

#### Option 3: Server-level Environment Variables
Contact your hosting provider to set server-level environment variables.

### Configuration Changes Made

1. **Environment switched to production** - Your site now uses live PayPal API
2. **Domain updated** - Changed from localhost to https://ryvahcommerce.com
3. **Sandbox credentials commented out** - Still available for testing if needed
4. **Environment variables** - Credentials now read from environment variables
5. **SSL bypass disabled** - Proper SSL verification for production

### Getting Your PayPal Credentials

1. Log in to your PayPal Developer account: https://developer.paypal.com/
2. Go to "My Apps & Credentials"
3. Under "Live" tab, create a new app or use existing app
4. Copy your Client ID and Client Secret
5. Set them as environment variables using one of the methods above

### Security Notes

- Never commit your actual credentials to version control
- Use environment variables to keep credentials secure
- The sandbox credentials are commented out but available for testing
- SSL verification is properly enabled for production

### Testing the Configuration

After setting up the environment variables, you can test the configuration by accessing your PayPal checkout functionality. Check your server logs for any PayPal-related errors. 