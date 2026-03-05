<!DOCTYPE html>
<html>
<head>
    <title>ARED FACILITY</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo" style="text-decoration: none; cursor: pointer;">ARED FACILITY</a>
        <div style="display: flex; gap: 20px; align-items: center;">
            <div class="status-indicator">SYSTEM STATUS: SECURE</div>
            <button class="btn" onclick="document.getElementById('ctfModal').style.display='block'">CTF OBJECTIVES</button>
            <?php if(isAdmin()): ?> <a href="admin_dashboard.php" class="btn" style="border-color: #f1c40f;">ADMIN PANEL</a> <?php endif; ?>
            <a href="logout.php" style="color: #fff; text-decoration: none;">Logout</a>
        </div>
    </nav>

    <div id="ctfModal" class="modal">
        <div class="modal-content">
            <h2 style="color: var(--lime);">ACTIVE OBJECTIVES</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 10px; border-bottom: 1px solid #333;">Access classified data of unassigned experiment – 80 pts</li>
                <li style="padding: 10px; border-bottom: 1px solid #333;">Escalate from Worker to Admin – 100 pts</li>
                <li style="padding: 10px; border-bottom: 1px solid #333;">Modify experiment risk level – 60 pts</li>
                <li style="padding: 10px; border-bottom: 1px solid #333;">Delete research logs – 50 pts</li>
                <li style="padding: 10px; border-bottom: 1px solid #333;">Retrieve hidden classified flag – 20 pts</li>
            </ul>
            <button class="btn" onclick="document.getElementById('ctfModal').style.display='none'">CLOSE</button>
        </div>
    </div>