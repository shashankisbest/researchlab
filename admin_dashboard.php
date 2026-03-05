<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requireAdmin();

// --- ACTION HANDLERS ---

// Delete Experiment
if (isset($_GET['delete_exp'])) {
    $stmt = $pdo->prepare("DELETE FROM experiments WHERE id = ?");
    $stmt->execute([$_GET['delete_exp']]);
    header("Location: admin_dashboard.php?msg=deleted");
}

// Update Status/Progress
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_exp'])) {
    $stmt = $pdo->prepare("UPDATE experiments SET status = ?, progress = ?, risk_level = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['progress'], $_POST['risk_level'], $_POST['exp_id']]);
}

// Assign Worker
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_worker'])) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO assignments (user_id, experiment_id) VALUES (?, ?)");
    $stmt->execute([$_POST['user_id'], $_POST['exp_id']]);
}

// Fetch Data
$experiments = $pdo->query("SELECT * FROM experiments")->fetchAll();
$workers = $pdo->query("SELECT id, username FROM users WHERE role='worker'")->fetchAll();
$all_logs = $pdo->query("SELECT l.*, u.username, e.name as exp_name FROM logs l JOIN users u ON l.user_id = u.id JOIN experiments e ON l.experiment_id = e.id ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1 style="color: var(--lime);">ARED Command & Control</h1>

    <div class="stat-card" style="margin-bottom: 30px;">
        <h3>Active Experiments</h3>
        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #333; color: var(--lime);">
                <th>Name</th><th>Risk</th><th>Progress</th><th>Actions</th>
            </tr>
            <?php foreach($experiments as $e): ?>
            <tr style="border-bottom: 1px solid #222;">
                <td><?= htmlspecialchars($e['name']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="update_exp" value="1">
                        <input type="hidden" name="exp_id" value="<?= $e['id'] ?>">
                        <input type="hidden" name="status" value="<?= $e['status'] ?>">
                        <input type="hidden" name="progress" value="<?= $e['progress'] ?>">
                        <select name="risk_level" onchange="this.form.submit()" style="background:#000; color:#fff; border:none;">
                            <option <?= $e['risk_level']=='Normal'?'selected':'' ?>>Normal</option>
                            <option <?= $e['risk_level']=='Sensitive'?'selected':'' ?>>Sensitive</option>
                            <option <?= $e['risk_level']=='High Risk'?'selected':'' ?>>High Risk</option>
                        </select>
                    </form>
                </td>
                <td><?= $e['progress'] ?>%</td>
                <td>
                    <a href="admin_dashboard.php?delete_exp=<?= $e['id'] ?>" style="color: #e74c3c; text-decoration: none;" onclick="return confirm('Confirm Purge?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="stat-card">
            <h3>Assign Personnel</h3>
            <form method="POST">
                <input type="hidden" name="assign_worker" value="1">
                <select name="user_id" style="width:100%; margin-bottom:10px; padding:10px; background:#000; color:#fff;">
                    <?php foreach($workers as $w): ?>
                        <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['username']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="exp_id" style="width:100%; margin-bottom:10px; padding:10px; background:#000; color:#fff;">
                    <?php foreach($experiments as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn" style="width:100%;">DEPLOY WORKER</button>
            </form>
        </div>

        <div class="stat-card">
            <h3>Facility Logs (Recent)</h3>
            <div style="font-size: 0.8rem; max-height: 200px; overflow-y: auto;">
                <?php foreach($all_logs as $log): ?>
                    <p style="border-bottom: 1px solid #222;">
                        [<?= $log['exp_name'] ?>] <strong><?= $log['username'] ?>:</strong> <?= htmlspecialchars($log['content']) ?>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>