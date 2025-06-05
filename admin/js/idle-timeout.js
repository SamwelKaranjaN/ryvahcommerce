class IdleTimeout {
    constructor(options = {}) {
        console.log('IdleTimeout initialized with options:', options);
        this.options = {
            idleTime: options.idleTime || 1800000, //  30 minutes in milliseconds
            warningTime: options.warningTime || 5000, // 5 seconds warning
            logoutUrl: options.logoutUrl || 'logout.php',
            warningMessage: options.warningMessage || 'You have been inactive for a while. Click anywhere or press "Cancel" to stay logged in. You will be logged out in 5 seconds.',
            ...options
        };

        this.timer = null;
        this.warningTimer = null;
        this.isWarningShown = false;
        this.lastActivity = Date.now();

        this.init();
    }

    init() {
        console.log('Initializing IdleTimeout...');
        // Create warning modal
        this.createWarningModal();

        // Add event listeners for user activity
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        events.forEach(event => {
            document.addEventListener(event, () => {
                console.log('Activity detected:', event);
                this.resetTimer();
            });
        });

        // Start the timer
        this.startTimer();
        console.log('IdleTimeout initialized successfully');
    }

    createWarningModal() {
        // Create modal container
        this.modal = document.createElement('div');
        this.modal.className = 'idle-timeout-modal';
        this.modal.style.cssText = `
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        `;

        // Create modal content
        const content = document.createElement('div');
        content.className = 'idle-timeout-content';
        content.style.cssText = `
            background-color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            text-align: center;
        `;

        // Create warning message
        const message = document.createElement('p');
        message.textContent = this.options.warningMessage;
        message.style.cssText = `
            margin-bottom: 1.5rem;
            color: #1f2937;
            font-size: 1rem;
            line-height: 1.5;
        `;

        // Create countdown display
        const countdown = document.createElement('div');
        countdown.className = 'idle-timeout-countdown';
        countdown.style.cssText = `
            font-size: 1.5rem;
            font-weight: bold;
            color: #ef4444;
            margin-bottom: 1.5rem;
        `;

        // Create cancel button
        const cancelButton = document.createElement('button');
        cancelButton.textContent = 'Cancel';
        cancelButton.className = 'idle-timeout-cancel';
        cancelButton.style.cssText = `
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
        `;
        cancelButton.onmouseover = () => cancelButton.style.backgroundColor = '#4338ca';
        cancelButton.onmouseout = () => cancelButton.style.backgroundColor = '#4f46e5';
        cancelButton.onclick = () => this.cancelLogout();

        // Assemble modal
        content.appendChild(message);
        content.appendChild(countdown);
        content.appendChild(cancelButton);
        this.modal.appendChild(content);
        document.body.appendChild(this.modal);

        // Store countdown element reference
        this.countdownElement = countdown;
    }

    startTimer() {
        console.log('Starting idle timer for', this.options.idleTime, 'ms');
        this.timer = setTimeout(() => {
            console.log('Idle time reached, showing warning');
            this.showWarning();
        }, this.options.idleTime);
    }

    resetTimer() {
        console.log('Resetting timer...');
        if (this.isWarningShown) {
            this.cancelLogout();
        } else {
            clearTimeout(this.timer);
            this.startTimer();
        }
    }

    showWarning() {
        console.log('Showing warning modal');
        this.isWarningShown = true;
        this.modal.style.display = 'flex';

        let timeLeft = this.options.warningTime / 1000;
        this.countdownElement.textContent = timeLeft;

        this.warningTimer = setInterval(() => {
            timeLeft--;
            this.countdownElement.textContent = timeLeft;
            console.log('Warning countdown:', timeLeft);

            if (timeLeft <= 0) {
                console.log('Warning time expired, logging out');
                this.logout();
            }
        }, 1000);
    }

    cancelLogout() {
        console.log('Cancelling logout');
        this.isWarningShown = false;
        clearInterval(this.warningTimer);
        this.modal.style.display = 'none';
        this.startTimer();
    }

    logout() {
        console.log('Logging out...');
        window.location.href = this.options.logoutUrl;
    }
}

// Make sure the class is available globally
window.IdleTimeout = IdleTimeout; 