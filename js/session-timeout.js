class SessionTimeout {
    constructor() {
        this.checkInterval = 30000; // Check every 30 seconds
        this.timeoutModal = new bootstrap.Modal(document.getElementById('timeoutModal'));
        this.lastActivity = Date.now();
        this.setupEventListeners();
        this.startTimeoutCheck();
    }

    setupEventListeners() {
        // Update last activity on user interaction
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        events.forEach(event => {
            document.addEventListener(event, () => this.updateLastActivity());
        });
    }

    updateLastActivity() {
        this.lastActivity = Date.now();
    }

    async checkSession() {
        try {
            const response = await fetch('php/login/session_timeout.php');
            const data = await response.json();

            if (data.logged_out) {
                this.showTimeoutModal();
            }
        } catch (error) {
            console.error('Error checking session:', error);
        }
    }

    showTimeoutModal() {
        this.timeoutModal.show();
    }

    startTimeoutCheck() {
        setInterval(() => this.checkSession(), this.checkInterval);
    }
}

// Initialize session timeout handler when document is ready
document.addEventListener('DOMContentLoaded', () => {
    new SessionTimeout();
}); 