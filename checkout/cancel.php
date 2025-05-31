<?php
require_once '../includes/bootstrap.php';
require_once '../includes/security.php';

// Log the cancellation
if (isset($_SESSION['user_id'])) {
    logSecurityEvent('payment_cancelled', [
        'user_id' => $_SESSION['user_id'],
        'reason' => $_GET['reason'] ?? 'User cancelled'
    ]);
}

// Clear pending order from session
if (isset($_SESSION['pending_order'])) {
    unset($_SESSION['pending_order']);
}

// Set error message
$_SESSION['error_message'] = "Your payment was cancelled. Please try again.";

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger fa-4x"></i>
                    </div>
                    <h2 class="mb-3">Payment Cancelled</h2>
                    <p class="text-muted mb-4">Your payment was cancelled. No charges were made to your account.</p>

                    <div class="d-grid gap-2">
                        <a href="checkout.php" class="btn btn-primary">
                            <i class="fas fa-redo me-2"></i>Try Again
                        </a>
                        <a href="../cart.php" class="btn btn-outline-primary">
                            <i class="fas fa-shopping-cart me-2"></i>Return to Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 1rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .fa-times-circle {
        animation: scale-in 0.5s ease-out;
    }

    @keyframes scale-in {
        0% {
            transform: scale(0);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
</style>

<?php include '../includes/layouts/footer.php'; ?>