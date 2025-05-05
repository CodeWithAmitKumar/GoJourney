<?php
// Check if form is submitted
if(isset($_POST['update_settings'])) {
    $site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
    $site_email = mysqli_real_escape_string($conn, $_POST['site_email']);
    $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
    $contact_address = mysqli_real_escape_string($conn, $_POST['contact_address']);
    $currency = mysqli_real_escape_string($conn, $_POST['currency']);
    
    // Check if settings table exists
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
    if(mysqli_num_rows($check_table) == 0) {
        // Create settings table if it doesn't exist
        $create_table_sql = "CREATE TABLE settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $create_table_sql);
    }
    
    // Update or insert settings
    $settings = [
        'site_name' => $site_name,
        'site_email' => $site_email,
        'contact_phone' => $contact_phone,
        'contact_address' => $contact_address,
        'currency' => $currency
    ];
    
    foreach($settings as $key => $value) {
        $check_exists = mysqli_query($conn, "SELECT * FROM settings WHERE setting_key = '$key'");
        if(mysqli_num_rows($check_exists) > 0) {
            // Update existing setting
            mysqli_query($conn, "UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'");
        } else {
            // Insert new setting
            mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
        }
    }
    
    $success_message = "Settings updated successfully.";
}

// Get current settings
$settings = [
    'site_name' => 'GoJourney',
    'site_email' => 'info@gojourney.com',
    'contact_phone' => '+91 9876543210',
    'contact_address' => '123 Travel Street, Mumbai, India',
    'currency' => '₹'
];

// Check if settings table exists and retrieve values
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
if(mysqli_num_rows($check_table) > 0) {
    $result = mysqli_query($conn, "SELECT * FROM settings");
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}
?>

<div class="dashboard-title">
    <h2>System Settings</h2>
</div>

<?php if(isset($success_message)): ?>
    <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $success_message; ?>
    </div>
<?php endif; ?>

<div class="content-section">
    <form method="POST" action="">
        <div class="form-grid">
            <div class="form-group">
                <label for="site_name">Site Name</label>
                <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="site_email">Site Email</label>
                <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact_phone">Contact Phone</label>
                <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone']); ?>">
            </div>
            
            <div class="form-group">
                <label for="currency">Currency Symbol</label>
                <input type="text" id="currency" name="currency" value="<?php echo htmlspecialchars($settings['currency']); ?>" required>
            </div>
            
            <div class="form-group wide">
                <label for="contact_address">Contact Address</label>
                <textarea id="contact_address" name="contact_address" rows="3"><?php echo htmlspecialchars($settings['contact_address']); ?></textarea>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" name="update_settings" class="btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>

<div class="content-section">
    <h3>Database Information</h3>
    <div class="info-grid">
        <?php
        // Get MySQL version
        $version_result = mysqli_query($conn, "SELECT VERSION() as version");
        $mysql_version = mysqli_fetch_assoc($version_result)['version'];
        
        // Get database name - fixing undefined property error
        $db_result = mysqli_query($conn, "SELECT DATABASE() as db_name");
        $db_name = mysqli_fetch_assoc($db_result)['db_name'];
        
        // Get tables count
        $tables_result = mysqli_query($conn, "SHOW TABLES");
        $tables_count = mysqli_num_rows($tables_result);
        ?>
        
        <div class="info-item">
            <div class="info-label">MySQL Version</div>
            <div class="info-value"><?php echo $mysql_version; ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Database Name</div>
            <div class="info-value"><?php echo $db_name; ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Tables Count</div>
            <div class="info-value"><?php echo $tables_count; ?></div>
        </div>
    </div>
</div>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group.wide {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: var(--dark-color);
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
}

.form-actions {
    margin-top: 20px;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.info-item {
    background-color: #f8f9fa;
    border-radius: 5px;
    padding: 15px;
}

.info-label {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.info-value {
    font-weight: 600;
    color: var(--dark-color);
}
</style> 