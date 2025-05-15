<?php
require_once 'header.php';
require_once 'php/db_connect.php';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settings = [
        'site_name' => $_POST['site_name'],
        'site_description' => $_POST['site_description'],
        'currency' => $_POST['currency'],
        'tax_rate' => $_POST['tax_rate'],
        'shipping_cost' => $_POST['shipping_cost'],
        'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0
    ];

    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE setting_key = ?");
        $stmt->bind_param("ss", $value, $key);
        $stmt->execute();
    }

    $success_message = "Settings updated successfully!";
}

// Fetch current settings
$query = "SELECT * FROM settings";
$result = $conn->query($query);
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['value'];
}
?>

<div class="main-content">
    <div class="page-header">
        <h2>System Settings</h2>
    </div>

    <?php if (isset($success_message)): ?>
    <div class="alert alert-success">
        <?php echo $success_message; ?>
    </div>
    <?php endif; ?>

    <div class="content-card">
        <form method="POST" class="settings-form">
            <div class="settings-section">
                <h3>General Settings</h3>
                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="site_name" 
                           value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="site_description">Site Description</label>
                    <textarea id="site_description" name="site_description" rows="3"><?php 
                        echo htmlspecialchars($settings['site_description'] ?? ''); 
                    ?></textarea>
                </div>
            </div>

            <div class="settings-section">
                <h3>E-commerce Settings</h3>
                <div class="form-group">
                    <label for="currency">Currency</label>
                    <select id="currency" name="currency">
                        <option value="USD" <?php echo ($settings['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                        <option value="EUR" <?php echo ($settings['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                        <option value="GBP" <?php echo ($settings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tax_rate">Tax Rate (%)</label>
                    <input type="number" id="tax_rate" name="tax_rate" step="0.01" min="0" max="100"
                           value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '0'); ?>">
                </div>
                <div class="form-group">
                    <label for="shipping_cost">Default Shipping Cost</label>
                    <input type="number" id="shipping_cost" name="shipping_cost" step="0.01" min="0"
                           value="<?php echo htmlspecialchars($settings['shipping_cost'] ?? '0'); ?>">
                </div>
            </div>

            <div class="settings-section">
                <h3>System Settings</h3>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="email_notifications" 
                               <?php echo ($settings['email_notifications'] ?? '') ? 'checked' : ''; ?>>
                        Enable Email Notifications
                    </label>
                </div>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="maintenance_mode"
                               <?php echo ($settings['maintenance_mode'] ?? '') ? 'checked' : ''; ?>>
                        Maintenance Mode
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<style>
.settings-form {
    max-width: 800px;
    margin: 0 auto;
}

.settings-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.settings-section h3 {
    margin-bottom: 20px;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #34495e;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.checkbox-group {
    display: flex;
    align-items: center;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.form-actions {
    margin-top: 30px;
    text-align: right;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
</style>

<?php require_once 'footer.php'; ?> 