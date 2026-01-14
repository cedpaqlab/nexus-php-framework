<?php
$title = 'Dashboard';
ob_start();
?>

<section class="dashboard-section">
    <h1>Welcome to Your Dashboard</h1>
    
    <div class="user-info-card">
        <h2>Your Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">ID:</span>
                <span class="info-value"><?= htmlspecialchars((string)($user['id'] ?? '')) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($user['email'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Role:</span>
                <span class="info-value">
                    <span class="role-badge role-<?= htmlspecialchars($user['role'] ?? 'user') ?>">
                        <?= htmlspecialchars($user['role'] ?? 'user') ?>
                    </span>
                </span>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
$csrf_token = $csrf_token ?? '';
include __DIR__ . '/../layouts/dashboard.php';
?>
