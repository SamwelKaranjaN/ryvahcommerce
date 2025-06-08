# Ryvah Commerce - Revamped Checkout System

## Overview

This is a complete checkout system with PayPal integration, featuring:

- **Secure PayPal Payments**: Production-ready PayPal Checkout SDK integration
- **Automatic Tax Calculation**: Database-driven tax calculation based on location and product type
- **Flexible Shipping**: Product-type specific shipping fees with breakdown display
- **Comprehensive Security**: CSRF protection, session security, input validation
- **Real-time Totals**: Dynamic calculation of tax and shipping when address is selected
- **Error Handling**: Robust error handling with user-friendly messages
- **Mobile Responsive**: Bootstrap 5 responsive design

## File Structure

```
checkout/
├── simple_checkout.php          # Main checkout page
├── simple_create_order.php      # PayPal order creation API
├── simple_capture.php           # PayPal payment capture API
├── simple_success.php           # Order success page
├── calculate_totals.php         # Tax and shipping calculation API
└── shipping_calculator.php     # Shipping calculation helper

Root Files:
├── setup_dependencies.php      # Dependency installer script
├── test_checkout.php           # Comprehensive test suite
├── composer.json               # Composer dependencies
└── CHECKOUT_README.md          # This file
```

## Quick Setup

1. **Install Dependencies**:
   ```bash
   php setup_dependencies.php
   ```

2. **Fix SSL Issues (IMPORTANT)**:
   ```bash
   php fix_ssl_permanently.php
   ```

3. **Test SSL Connection**:
   ```bash
   # Open in browser:
   http://your-domain.com/test_ssl_fix.php
   ```

4. **Test System**:
   ```bash
   # Open in browser:
   http://your-domain.com/test_checkout.php
   ```

5. **Configure PayPal**:
   - Edit `includes/paypal_config.php`
   - Update PayPal credentials and environment settings

6. **Test Checkout**:
   ```bash
   # Open in browser:
   http://your-domain.com/checkout/simple_checkout.php
   ```

## Dependencies

The system requires these PHP packages (automatically installed):

- **paypal/paypal-checkout-sdk**: PayPal Checkout SDK for order creation and capture
- **paypal/paypal-server-sdk**: Additional PayPal functionality
- **phpmailer/phpmailer**: Email notifications

### Required PHP Extensions

- curl (for PayPal API calls)
- json (for data processing)
- openssl (for secure connections)
- mysqli (for database operations)
- session (for user session management)

## Database Requirements

The checkout system requires these database tables:

- `users` - User accounts
- `products` - Product catalog
- `cart` - Shopping cart items
- `orders` - Order records
- `order_items` - Order line items
- `addresses` - User shipping addresses
- `tax_settings` - Tax configuration by product type
- `shipping_fees` - Shipping fees by product type
- `ebook_downloads` - Digital download links

## Configuration

### PayPal Configuration (`includes/paypal_config.php`)

```php
// Environment (sandbox or production)
define('PAYPAL_ENVIRONMENT', 'production');

// PayPal Credentials
define('PAYPAL_CLIENT_ID', 'your-paypal-client-id');
define('PAYPAL_CLIENT_SECRET', 'your-paypal-client-secret');

// Site Configuration
define('SITE_DOMAIN', 'https://your-domain.com');
define('PAYPAL_RETURN_URL', SITE_DOMAIN . '/checkout/simple_success.php');
define('PAYPAL_CANCEL_URL', SITE_DOMAIN . '/checkout/simple_checkout.php');
```

### Database Configuration (`includes/bootstrap.php`)

Ensure your database connection is properly configured with:
- Host, username, password, database name
- UTF-8 charset
- Error reporting enabled

## Features

### 1. Secure Payment Processing

- **PayPal Checkout SDK**: Official PayPal integration
- **CSRF Protection**: All forms protected with CSRF tokens
- **Session Security**: Session regeneration and validation
- **Input Validation**: Comprehensive server-side validation

### 2. Dynamic Total Calculation

- **Real-time Updates**: Totals update when shipping address is selected
- **Tax Calculation**: Database-driven tax rates by location and product type
- **Shipping Calculation**: Product-type specific shipping fees
- **Breakdown Display**: Detailed breakdown of shipping charges

### 3. User Experience

- **Responsive Design**: Mobile-friendly Bootstrap 5 interface
- **Loading Indicators**: Visual feedback during processing
- **Error Messages**: Clear, actionable error messages
- **Progress Indication**: Clear checkout flow with progress indicators

### 4. Order Management

- **Order Tracking**: Complete order lifecycle tracking
- **Digital Downloads**: Automatic download link generation for eBooks
- **Email Notifications**: Order confirmation emails
- **Success Page**: Comprehensive order confirmation

## Testing

### Automated Tests

Run the comprehensive test suite:

```bash
# Open in browser:
http://your-domain.com/test_checkout.php
```

This tests:
- PayPal configuration
- Database connectivity
- Required files and functions
- Network connectivity
- File permissions

### Manual Testing Steps

1. **Login**: Ensure user authentication works
2. **Add to Cart**: Add products to shopping cart
3. **Checkout**: Navigate to checkout page
4. **Address Selection**: Select shipping address
5. **Total Calculation**: Verify tax and shipping calculate correctly
6. **PayPal Payment**: Complete payment through PayPal
7. **Success Page**: Verify order confirmation displays correctly

### Test Accounts

For sandbox testing, use PayPal's test accounts:
- **Buyer Account**: Use PayPal sandbox buyer credentials
- **Seller Account**: Use your PayPal sandbox business account

## API Endpoints

### POST `/checkout/calculate_totals.php`

Calculates tax and shipping based on selected address.

**Request**:
```json
{
    "address_id": 123,
    "csrf_token": "token"
}
```

**Response**:
```json
{
    "success": true,
    "subtotal": 100.00,
    "tax_amount": 8.25,
    "shipping_amount": 15.00,
    "total": 123.25,
    "shipping_breakdown": [...]
}
```

### POST `/checkout/simple_create_order.php`

Creates PayPal order.

**Request**:
```json
{
    "total": 123.25,
    "subtotal": 100.00,
    "tax_amount": 8.25,
    "shipping_amount": 15.00,
    "address_id": 123,
    "csrf_token": "token",
    "currency": "USD"
}
```

**Response**:
```json
{
    "id": "PAYPAL_ORDER_ID",
    "status": "success"
}
```

### POST `/checkout/simple_capture.php`

Captures PayPal payment.

**Request**:
```json
{
    "orderID": "PAYPAL_ORDER_ID",
    "csrf_token": "token"
}
```

**Response**:
```json
{
    "success": true,
    "order_id": 456,
    "success_token": "token",
    "message": "Payment completed successfully"
}
```

## Security Features

### 1. CSRF Protection
- All forms include CSRF tokens
- Server-side token validation on all requests

### 2. Session Security
- Session regeneration on sensitive operations
- Session-based authentication validation

### 3. Input Validation
- Server-side validation of all user inputs
- SQL injection prevention with prepared statements
- XSS prevention with proper output escaping

### 4. PayPal Security
- Official PayPal SDK usage
- Secure API credentials handling
- Network connectivity validation

## Error Handling

### Client-Side
- User-friendly error messages
- Automatic retry suggestions
- Network connectivity guidance

### Server-Side
- Comprehensive error logging
- Graceful error recovery
- Detailed error context for debugging

## Troubleshooting

### Common Issues

1. **SSL Certificate Problems (FIXED)**
   - **SOLUTION**: Run `php fix_ssl_permanently.php` to fix SSL issues
   - This script downloads CA certificates and configures SSL for development
   - SSL verification is automatically disabled for WAMP/XAMPP environments

2. **PayPal SDK Not Loading**
   - Check composer dependencies: `composer install`
   - Verify autoloader: `require_once 'vendor/autoload.php'`

3. **Payment System Unavailable**
   - Check PayPal credentials in `includes/paypal_config.php`
   - Run SSL test: `php test_ssl_fix.php`
   - Check error logs in `/logs/paypal.log`

4. **Tax/Shipping Not Calculating**
   - Verify database tables exist
   - Check address data
   - Review calculate_totals.php logs

5. **Database Connection Issues**
   - Verify connection in bootstrap.php
   - Check database server status
   - Review database credentials

### Debug Mode

Enable debug mode by setting in `includes/paypal_config.php`:
```php
define('PAYPAL_LOG_ENABLED', true);
```

Check logs in `/logs/paypal.log` for detailed error information.

## Performance Optimization

### 1. Database Optimization
- Index on frequently queried columns
- Optimize tax and shipping queries
- Use connection pooling if available

### 2. PayPal API Optimization
- Implement request caching where appropriate
- Use connection keep-alive
- Monitor API response times

### 3. Frontend Optimization
- Minimize JavaScript payload
- Use CDN for external resources
- Implement proper caching headers

## Maintenance

### Regular Tasks

1. **Update Dependencies**: Monthly composer updates
2. **Monitor Logs**: Review error logs weekly
3. **Test Payments**: Weekly payment system testing
4. **Security Updates**: Apply security patches promptly

### Monitoring

Monitor these metrics:
- Payment success rate
- API response times
- Error frequency
- User experience metrics

## Support

For issues or questions:
1. Check the test suite: `test_checkout.php`
2. Review error logs in `/logs/`
3. Verify configuration with the setup script
4. Test individual components manually

## License

This checkout system is part of Ryvah Commerce and follows the project's licensing terms. 