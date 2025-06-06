<!-- Session Timeout Modal -->
<div class="modal fade" id="timeoutModal" tabindex="-1" aria-labelledby="timeoutModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="timeoutModalLabel">Session Timeout</h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-clock fa-3x text-warning"></i>
                </div>
                <h5 class="mb-3">Your session has expired</h5>
                <p class="text-muted">You have been inactive for too long. For security reasons, you have been logged
                    out.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <a href="../pages/login" class="btn btn-primary">Login Again</a>
            </div>
        </div>
    </div>
</div>

<style>
#timeoutModal .modal-content {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

#timeoutModal .modal-header {
    padding: 1.5rem 1.5rem 0.5rem;
}

#timeoutModal .modal-body {
    padding: 1.5rem;
}

#timeoutModal .modal-footer {
    padding: 1rem 1.5rem 1.5rem;
}

#timeoutModal .btn-primary {
    padding: 0.6rem 2rem;
    font-weight: 600;
    border-radius: 8px;
}

#timeoutModal .text-warning {
    color: #ffc107 !important;
}
</style>