<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM experiments WHERE id = ?");
$stmt->execute([$id]);
$exp = $stmt->fetch();

if (!$exp) die("Experiment not found.");

// Check if assigned (for workers)
$is_assigned = false;
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
        $log_stmt = $pdo->prepare("SELECT logs.*, users.username FROM logs JOIN users ON logs.user_id = users.id WHERE experiment_id = ? ORDER BY created_at DESC");
        $log_stmt->execute([$id]);
        while($log = $log_stmt->fetch()): ?>
            <div style="border-bottom: 1px solid #333; padding: 10px;">
                <strong><?= $log['username'] ?>:</strong> <?= htmlspecialchars($log['content']) ?>
                <br><small><?= $log['created_at'] ?></small>
            </div>
        <?php endwhile; ?>

        <?php if ($is_assigned): ?>
        <form method="POST" style="margin-top: 20px;">
            <textarea name="log_content" style="width:100%; background:#000; color:#fff; border: 1px solid var(--lime);"></textarea>
            <button type="submit" class="btn">Upload Log Entry</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>