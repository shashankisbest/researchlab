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
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <h3 style="color: var(--lime);">Worker Profile</h3>
            <p><strong>ID:</strong> #<?= $user_id ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
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
                    <div style="background: var(--lime); width: <?= $exp['progress'] ?>%; height: 100%; border-radius: 4px;"></div>
                </div>
                <a href="view_experiment.php?id=<?= $exp['id'] ?>" class="btn">Access Logs</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>