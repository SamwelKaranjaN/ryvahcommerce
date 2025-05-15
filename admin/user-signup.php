<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ryva E-commerce - Registration</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/user-signup.css">
    <style>
        .role-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 5px;
            font-family: 'Roboto', sans-serif;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 15px;
        }
        
        .signup-link a {
            color: #4a90e2;
            text-decoration: none;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="signup-container">
        <h2>Create Account</h2>
        <form id="signupForm" action="php/process-signup.php" method="POST">
            <div class="input-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone">
            </div>
            <div class="input-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="role-select" required>
                    <option value="">Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="Employee">Employee</option>
                </select>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="password-toggle" id="togglePassword">👁️</span>
            </div>
            <div class="input-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span class="password-toggle" id="toggleConfirmPassword">👁️</span>
            </div>
            <div class="error" id="error-message"></div>
            <button type="submit">Create Account</button>
        </form>
        <div class="signup-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirm_password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });

        // Form validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const role = document.getElementById('role').value;
            const errorMessage = document.getElementById('error-message');

            if (!fullName || !email || !password || !confirmPassword || !role) {
                e.preventDefault();
                errorMessage.style.display = 'block';
                errorMessage.textContent = 'Please fill in all required fields';
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                errorMessage.style.display = 'block';
                errorMessage.textContent = 'Passwords do not match';
                return;
            }

            if (password.length < 8) {
                e.preventDefault();
                errorMessage.style.display = 'block';
                errorMessage.textContent = 'Password must be at least 8 characters long';
                return;
            }
        });
    </script>
</body>

</html>
