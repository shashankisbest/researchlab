<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requireLogin();

// VULNERABILITY #1: SQL Injection in ID parameter
// Direct concatenation without preparation
$id = $_GET['id'] ?? 0;

// VULNERABLE QUERY - No prepared statement!
$query = "SELECT * FROM experiments WHERE id = $id";
$result = mysqli_query($conn, $query); // Note: using mysqli, not PDO

// Convert to associative array to maintain compatibility
if ($result && mysqli_num_rows($result) > 0) {
    $exp = mysqli_fetch_assoc($result);
} else {
    die("Experiment not found.");
}

// Check if assigned (for workers) - Keep this secure to contrast with vulnerable one
$is_assigned = false;
// This one still uses PDO (secure) - good for teaching contrast
$check_stmt = $pdo->prepare("SELECT * FROM assignments WHERE user_id = ? AND experiment_id = ?");
$check_stmt->execute([$_SESSION['user_id'], $id]);
if ($check_stmt->fetch()) $is_assigned = true;

// Handle Log Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_content']) && $is_assigned) {
    $ins = $pdo->prepare("INSERT INTO logs (experiment_id, user_id, content) VALUES (?, ?, ?)");
    $ins->execute([$id, $_SESSION['user_id'], $_POST['log_content']]);
    header("Location: view_experiment.php?id=$id");
    exit;
}

// Admin Secondary Password Check (Simple implementation)
$show_classified = false;
if (isAdmin()) {
    if (isset($_POST['admin_key']) && $_POST['admin_key'] === 'ARED_INTERNAL_2026') {
        $show_classified = true;
    }
} elseif ($is_assigned) {
    $show_classified = true;
}

// VULNERABILITY #2: SQL Injection in logs query
// Another vulnerable query for students to find
$log_query = "SELECT logs.*, users.username FROM logs JOIN users ON logs.user_id = users.id WHERE experiment_id = $id ORDER BY created_at DESC";
$log_result = mysqli_query($conn, $log_query);

// Debug comment with hidden flag
//<!-- FLAG{sql_injection_in_view_experiment} -->
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="stat-card">
        <h2><?= htmlspecialchars($exp['name']) ?></h2>
        <p><?= htmlspecialchars($exp['description']) ?></p>
        <hr style="border: 0.5px solid #333;">
        
        <h3>Classified Research Data</h3>
        <div style="background: #000; padding: 20px; border: 1px dashed var(--lime);">
            <?php if ($show_classified): ?>
                <code><?= htmlspecialchars($exp['classified_content']) ?></code>
            <?php elseif (isAdmin()): ?>
                <form method="POST">
                    <input type="password" name="admin_key" placeholder="Enter Admin Override Key">
                    <button type="submit" class="btn">Unlock Section</button>
                </form>
            <?php else: ?>
                <p style="color: #e74c3c;">Access Restricted – Not Assigned to This Experiment</p>
            <?php endif; ?>
        </div>

        <h3>Research Logs</h3>
        <?php
        // Display logs using the vulnerable query result
        if ($log_result && mysqli_num_rows($log_result) > 0):
            while($log = mysqli_fetch_assoc($log_result)): ?>
                <div style="border-bottom: 1px solid #333; padding: 10px;">
                    <strong><?= htmlspecialchars($log['username']) ?>:</strong> <?= htmlspecialchars($log['content']) ?>
                    <br><small><?= htmlspecialchars($log['created_at']) ?></small>
                </div>
            <?php endwhile;
        else: ?>
            <p>No logs found for this experiment.</p>
        <?php endif; ?>

        <?php if ($is_assigned): ?>
        <form method="POST" style="margin-top: 20px;">
            <textarea name="log_content" style="width:100%; background:#000; color:#fff; border: 1px solid var(--lime);" placeholder="Enter your research log..."></textarea>
            <button type="submit" class="btn">Upload Log Entry</button>
        </form>
        <?php endif; ?>
        
        <!-- Hidden hint for students -->
        <!-- Hint: Try adding ' UNION SELECT... to the id parameter -->
        <!-- Flag: FLAG{sql_injection_basic} -->
    </div>
</div>

<?php include 'includes/footer.php'; ?>