/**
 * Session Timeout Handler for Ryvah Commerce
 * Handles user session timeout notifications and automatic logout
 */

(function () {
    'use strict';

    // Configuration
    const CONFIG = {
        sessionDuration: 30 * 60 * 1000, // 30 minutes in milliseconds
        warningTime: 5 * 60 * 1000,      // Show warning 5 minutes before timeout
        checkInterval: 60 * 1000,        // Check every minute
        extendUrl: '/api/extend-session.php',
        logoutUrl: '/logout.php'
    };

    let sessionTimer = null;
    let warningTimer = null;
    let lastActivity = Date.now();
    let warningShown = false;

    /**
     * Initialize session timeout handler
     */
    function init() {
        // Track user activity
        trackUserActivity();

        // Start session monitoring
        startSessionMonitoring();

        console.log('Session timeout handler initialized');
    }

    /**
     * Track user activity events
     */
    function trackUserActivity() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

        events.forEach(event => {
            document.addEventListener(event, updateLastActivity, true);
        });
    }

    /**
     * Update last activity timestamp
     */
    function updateLastActivity() {
        lastActivity = Date.now();
        warningShown = false;

        // Hide warning if shown
        hideWarning();
    }

    /**
     * Start session monitoring
     */
    function startSessionMonitoring() {
        sessionTimer = setInterval(checkSessionStatus, CONFIG.checkInterval);
    }

    /**
     * Check session status
     */
    function checkSessionStatus() {
        const timeSinceActivity = Date.now() - lastActivity;
        const timeUntilTimeout = CONFIG.sessionDuration - timeSinceActivity;

        // Show warning if approaching timeout
        if (timeUntilTimeout <= CONFIG.warningTime && !warningShown) {
            showWarning(Math.ceil(timeUntilTimeout / 1000 / 60));
            warningShown = true;
        }

        // Handle timeout
        if (timeUntilTimeout <= 0) {
            handleSessionTimeout();
        }
    }

    /**
     * Show session timeout warning
     */
    function showWarning(minutesRemaining) {
        console.log(`Session expires in ${minutesRemaining} minutes`);
        // Simple alert for now - can be enhanced with modal
        if (confirm(`Your session will expire in ${minutesRemaining} minutes. Continue?`)) {
            updateLastActivity();
        }
    }

    /**
     * Hide session timeout warning
     */
    function hideWarning() {
        const warning = document.getElementById('session-timeout-warning');
        if (warning) {
            warning.remove();
        }
    }

    /**
     * Extend session
     */
    function extendSession() {
        fetch(CONFIG.extendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateLastActivity();
                    hideWarning();
                    showNotification('Session extended successfully', 'success');
                } else {
                    showNotification('Failed to extend session', 'error');
                }
            })
            .catch(error => {
                console.error('Failed to extend session:', error);
                showNotification('Network error occurred', 'error');
            });
    }

    /**
     * Handle session timeout
     */
    function handleSessionTimeout() {
        clearInterval(sessionTimer);
        alert('Your session has expired. Please login again.');
        // Redirect to login or refresh page
        window.location.reload();
    }

    /**
     * Manual logout
     */
    function logout() {
        window.location.href = CONFIG.logoutUrl;
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            z-index: 10001;
            animation: slideInRight 0.3s ease;
        `;

        // Set background color based on type
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        notification.style.backgroundColor = colors[type] || colors.info;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * Public API
     */
    window.sessionTimeout = {
        init: init,
        extendSession: extendSession,
        logout: logout,
        updateActivity: updateLastActivity
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})(); 