<?php
$title = 'Admin Dashboard';
ob_start();
?>

<section class="admin-section">
    <h1>Admin Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?= $stats['total_users'] ?? 0 ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🛡️</div>
            <div class="stat-content">
                <h3><?= $stats['admin_count'] ?? 0 ?></h3>
                <p>Admins</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👤</div>
            <div class="stat-content">
                <h3><?= $stats['user_count'] ?? 0 ?></h3>
                <p>Regular Users</p>
            </div>
        </div>
    </div>

    <div class="admin-actions">
        <a href="/admin/users" class="btn btn-primary">Manage Users</a>
        <a href="/admin/users/create" class="btn btn-secondary">Create User</a>
    </div>

    <div class="recent-users">
        <h2>Recent Users</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($users ?? [], 0, 10) as $user): ?>
                <tr>
                    <td><?= htmlspecialchars((string)($user['id'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                    <td><span class="role-badge role-<?= htmlspecialchars($user['role'] ?? 'user') ?>"><?= htmlspecialchars($user['role'] ?? 'user') ?></span></td>
                    <td>
                        <a href="/admin/users/<?= $user['id'] ?? '' ?>/edit" class="btn-small">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
$content = ob_get_clean();
$csrf_token = $csrf_token ?? '';
include __DIR__ . '/../layouts/admin.php';
?>
