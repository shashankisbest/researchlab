<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get assignments count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE user_id = ?");
$stmt->execute([$user_id]);
$assign_count = $stmt->fetchColumn();

// Fetch only assigned experiments
$stmt = $pdo->prepare("
    SELECT e.* FROM experiments e 
    JOIN assignments a ON e.id = a.experiment_id 
    WHERE a.user_id = ?
");
$stmt->execute([$user_id]);
$my_experiments = $stmt->fetchAll();

// ============================================
// VULNERABILITY #1: Insecure File Upload
// NO validation, NO security checks
// ============================================
$upload_message = '';
$upload_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['research_file'])) {
    
    // Create uploads directory with WEAK permissions
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        // 0777 is dangerous - world writable!
        mkdir($upload_dir, 0777, true);
    }
    
    // VULNERABILITY: Using original filename (no sanitization)
    $filename = $_FILES['research_file']['name'];
    $target_path = $upload_dir . $filename;
    
    // VULNERABILITY: No file type checking
    // VULNERABILITY: No file size limits
    // VULNERABILITY: No content verification
    
    // Try to upload the file
    if (move_uploaded_file($_FILES['research_file']['tmp_name'], $target_path)) {
        $upload_message = "File uploaded successfully: " . htmlspecialchars($filename);
        
        // VULNERABILITY #2: SQL Injection in upload logging
        $exp_id = $_POST['exp_id'] ?? 0;
        
        // Using mysqli directly (vulnerable) instead of PDO
        $log_query = "INSERT INTO upload_logs (user_id, filename, experiment_id, upload_time) 
                      VALUES ($user_id, '$filename', $exp_id, NOW())";
        mysqli_query($conn, $log_query);
        
        // FLAG #1: Hidden in successful upload
        // FLAG{file_upload_success_basic}
        
        // If it's a PHP file, give a hint (for students)
        if (pathinfo($filename, PATHINFO_EXTENSION) == 'php') {
            $upload_message .= " <br><small style='color:orange;'>⚠️ PHP file detected! Access at: <a href='uploads/$filename' target='_blank'>uploads/$filename</a></small>";
            // FLAG #2: PHP upload hint
            // FLAG{php_upload_detected}
        }
    } else {
        $upload_error = "Upload failed! Check permissions.";
    }
}

// ============================================
// VULNERABILITY #3: Directory Listing Exposed
// ============================================
$recent_uploads = [];
if (file_exists('uploads/')) {
    $files = scandir('uploads/');
    $recent_uploads = array_slice(array_diff($files, ['.', '..']), -10);
    
    // FLAG #3: Hidden in directory listing
    // FLAG{uploads_directory_browsable}
}

// ============================================
// VULNERABILITY #4: Information Disclosure
// Hidden debug information
// ============================================
// Debug: Server path = <?= __DIR__ ?>
// Debug: Upload dir writable = <?= is_writable('uploads/') ? 'YES' : 'NO' ?>

// FLAG #4: Hidden in PHP comments
// FLAG{worker_dashboard_code_review}

?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <!-- Display upload messages -->
    <?php if ($upload_message): ?>
        <div class="stat-card" style="border-color: var(--lime);">
            <p style="color: var(--lime);">✅ <?= $upload_message ?></p>
            <!-- FLAG #5: Hidden in success message -->
            <!-- FLAG{upload_status_displayed} -->
        </div>
    <?php endif; ?>
    
    <?php if ($upload_error): ?>
        <div class="stat-card" style="border-color: #e74c3c;">
            <p style="color: #e74c3c;">❌ <?= $upload_error ?></p>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <h3 style="color: var(--lime);">Worker Profile</h3>
            <p><strong>ID:</strong> #<?= $user_id ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
            <!-- FLAG #6: Hidden in profile -->
            <!-- FLAG{worker_profile_info} -->
        </div>
        <div class="stat-card">
            <h3>Active Assignments</h3>
            <p style="font-size: 2rem; color: var(--lime);"><?= $assign_count ?></p>
        </div>
    </div>

    <h2 style="border-left: 3px solid var(--lime); padding-left: 10px;">Assigned Research</h2>
    
    <div class="exp-grid">
        <?php if(empty($my_experiments)): ?>
            <p style="color: #666;">No active assignments found in current sector.</p>
        <?php else: ?>
            <?php foreach($my_experiments as $exp): ?>
            <div class="exp-card risk-<?= str_replace(' ', '', $exp['risk_level']) ?>">
                <h3><?= htmlspecialchars($exp['name']) ?></h3>
                <p>Status: <strong><?= $exp['status'] ?></strong></p>
                <div style="background: #333; height: 8px; border-radius: 4px; margin: 10px 0;">
                    <div style="background: var(--lime); width: <?= $exp['progress'] ?? 0 ?>%; height: 100%; border-radius: 4px;"></div>
                </div>
                
                <!-- ============================================ -->
                <!-- VULNERABLE FILE UPLOAD FORM - FOR EACH EXPERIMENT -->
                <!-- ============================================ -->
                <div style="margin: 15px 0; padding: 15px; background: #1a1a1a; border-radius: 4px;">
                    <h4 style="color: var(--lime); margin-top: 0;">📁 Upload Research File</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="exp_id" value="<?= $exp['id'] ?>">
                        
                        <!-- VULNERABILITY: No restrictions on file type -->
                        <input type="file" name="research_file" required 
                               style="width: 100%; margin: 10px 0; color: #fff;">
                        
                        <small style="color: #666;">
                            Allowed files: Any type (no validation) | Max size: Unlimited
                        </small>
                        
                        <button type="submit" class="btn" style="margin-top: 10px; width: 100%;">
                            Upload File
                        </button>
                    </form>
                    
                    <!-- HINT for students (hidden in HTML) -->
                    <!-- 🔍 Try uploading a PHP file with system commands! -->
                    <!-- FLAG #7: Hidden upload hint -->
                    <!-- FLAG{upload_hint_discovered} -->
                </div>
                
                <a href="view_experiment.php?id=<?= $exp['id'] ?>" class="btn">Access Logs</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- ============================================ -->
    <!-- VULNERABILITY: Exposed Uploads Directory Listing -->
    <!-- ============================================ -->
    <?php if (!empty($recent_uploads)): ?>
    <div class="stat-card" style="margin-top: 30px;">
        <h3>📂 Recent Uploads (Directory Listing)</h3>
        <p><small class="text-muted">⚠️ This directory is publicly accessible!</small></p>
        
        <table style="width:100%; border-collapse: collapse;">
            <tr>
                <th>Filename</th>
                <th>Size</th>
                <th>Action</th>
            </tr>
            <?php foreach ($recent_uploads as $file): 
                $filepath = 'uploads/' . $file;
                $filesize = file_exists($filepath) ? round(filesize($filepath)/1024, 2) . ' KB' : 'Unknown';
            ?>
            <tr>
                <td><?= htmlspecialchars($file) ?></td>
                <td><?= $filesize ?></td>
                <td>
                    <a href="uploads/<?= urlencode($file) ?>" target="_blank" class="btn" style="padding: 5px 10px;">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <!-- FLAG #8: Hidden in uploads table -->
        <!-- FLAG{upload_listing_exposed} -->
        
        <!-- HINT: Try accessing uploads/ directly in browser -->
    </div>
    <?php endif; ?>
    
    <!-- ============================================ -->
    <!-- VULNERABILITY: Hidden Debug Information -->
    <!-- ============================================ -->
    <!-- 
        DEBUG INFO - REMOVE IN PRODUCTION
        Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?>
        PHP Version: <?= phpversion() ?>
        Upload dir: <?= __DIR__ ?>/uploads
        FLAG #9: FLAG{debug_info_exposed}
    -->
</div>

<script>
// ============================================
// VULNERABILITY #5: Client-side validation bypass
// ============================================
function validateFileUpload() {
    // This validation does nothing - easily bypassed
    console.log('Client-side validation running...');
    
    // FLAG #10: Hidden in JavaScript
    // FLAG{client_side_validation}
    
    return true; // Always returns true!
}

// Auto-run validation on page load (for show)
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔒 File upload validation enabled (not really)');
    console.log('Flag: FLAG{console_log_flag}');
});

// Hidden function for students to find
function getSecretPath() {
    return '/uploads/backdoor.php'; // This doesn't exist yet - students create it!
}
</script>

<!-- ============================================ -->
<!-- VULNERABILITY #6: HTML Comments with Sensitive Data -->
<!-- ============================================ -->
<!-- 
    ========================================
    TODO: Remove these comments before deployment
    Admin credentials backup: admin: admin123
    Upload directory: /var/www/html/ared-facility/uploads/
    Database: research_lab
    FLAG #11: FLAG{sensitive_comments_exposed}
    ========================================
-->

<!-- HINT: Try uploading a file named shell.php with this content:
<?php 
// Simple PHP shell
if(isset($_GET['cmd'])) {
    echo '<pre>';
    system($_GET['cmd']);
    echo '</pre>';
}
?>
-->

<?php include 'includes/footer.php'; ?>