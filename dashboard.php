<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch stats
$total_exp = $pdo->query("SELECT COUNT(*) FROM experiments")->fetchColumn();
$active_exp = $pdo->query("SELECT COUNT(*) FROM experiments WHERE status='Active'")->fetchColumn();
$high_risk = $pdo->query("SELECT COUNT(*) FROM experiments WHERE risk_level='High Risk'")->fetchColumn();

// Fetch experiments
$stmt = $pdo->query("SELECT * FROM experiments");
$experiments = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card"><h3>Total Experiments</h3><p><?= $total_exp ?></p></div>
        <div class="stat-card"><h3>Active Projects</h3><p><?= $active_exp ?></p></div>
        <div class="stat-card"><h3>High Risk</h3><p style="color: #e74c3c;"><?= $high_risk ?></p></div>
        <div class="stat-card"><h3>Role</h3><p><?= strtoupper($_SESSION['role']) ?></p></div>
    </div>

    <div class="exp-grid">
        <?php foreach($experiments as $exp): ?>
        <div class="exp-card risk-<?= str_replace(' ', '', $exp['risk_level']) ?>">
            <h3><?= htmlspecialchars($exp['name']) ?></h3>
            <p><small>ID: <?= $exp['id'] ?></small></p>
            <p><strong>Risk:</strong> <?= $exp['risk_level'] ?></p>
            <p><strong>Status:</strong> <?= $exp['status'] ?></p>
            <div style="background: #333; height: 10px; border-radius: 5px; margin: 10px 0;">
                <div style="background: var(--lime); width: <?= $exp['progress'] ?>%; height: 100%; border-radius: 5px;"></div>
            </div>
            <a href="view_experiment.php?id=<?= $exp['id'] ?>" class="btn">View Details</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>