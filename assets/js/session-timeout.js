// Session Timeout Handler
class SessionTimeout {
    constructor(options = {}) {
        this.options = {
            timeout: 30 * 60 * 1000, // 30 minutes
            warningTime: 5 * 60 * 1000, // 5 minutes
            checkInterval: 60 * 1000, // 1 minute
            ...options
        };

        this.lastActivity = Date.now();
        this.warningShown = false;
        this.timeoutId = null;
        this.warningId = null;

        this.init();
    }

    init() {
        // Update last activity on user interaction
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, () => this.updateLastActivity());
        });

        // Start checking for timeout
        this.startTimeoutCheck();
    }

    updateLastActivity() {
        this.lastActivity = Date.now();
        this.warningShown = false;
        this.resetTimers();
    }

    startTimeoutCheck() {
        this.timeoutId = setInterval(() => this.checkTimeout(), this.options.checkInterval);
    }

    checkTimeout() {
        const now = Date.now();
        const timeSinceLastActivity = now - this.lastActivity;

        // Show warning if approaching timeout
        if (timeSinceLastActivity >= (this.options.timeout - this.options.warningTime) && !this.warningShown) {
            this.showWarning();
        }

        // Logout if timeout reached
        if (timeSinceLastActivity >= this.options.timeout) {
            this.logout();
        }
    }

    showWarning() {
        this.warningShown = true;

        // Create warning modal if it doesn't exist
        if (!document.getElementById('timeout-warning-modal')) {
            const modal = document.createElement('div');
            modal.id = 'timeout-warning-modal';
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Session Timeout Warning</h5>
                        </div>
                        <div class="modal-body">
                            <p>Your session will expire in ${this.options.warningTime / 60000} minutes due to inactivity.</p>
                            <p>Would you like to stay logged in?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Logout</button>
                            <button type="button" class="btn btn-primary" id="stay-logged-in">Stay Logged In</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Add event listeners
            const stayLoggedInBtn = document.getElementById('stay-logged-in');
            stayLoggedInBtn.addEventListener('click', () => {
                this.updateLastActivity();
                const modal = bootstrap.Modal.getInstance(document.getElementById('timeout-warning-modal'));
                modal.hide();
            });
        }

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('timeout-warning-modal'));
        modal.show();
    }

    logout() {
        // Clear timers
        this.resetTimers();

        // Redirect to logout page
        window.location.href = '/pages/logout';
    }

    resetTimers() {
        if (this.timeoutId) {
            clearInterval(this.timeoutId);
        }
        if (this.warningId) {
            clearTimeout(this.warningId);
        }
        this.startTimeoutCheck();
    }
}

// Initialize session timeout handler
document.addEventListener('DOMContentLoaded', () => {
    new SessionTimeout();
}); 