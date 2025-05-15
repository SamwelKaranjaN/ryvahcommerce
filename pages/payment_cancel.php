<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="mb-4">Payment Cancelled</h2>
                    <p class="text-muted mb-4">
                        Your payment was cancelled. No charges have been made to your account.
                        You can try again or choose a different payment method.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="payment.php" class="btn btn-primary">Try Again</a>
                        <a href="cart.php" class="btn btn-outline-primary">Return to Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/layouts/footer.php'; ?> 