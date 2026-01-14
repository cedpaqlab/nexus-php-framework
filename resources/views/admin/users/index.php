<?php
$title = 'Manage Users';
ob_start();
?>

<section class="admin-section">
    <div class="section-header">
        <h1>Manage Users</h1>
        <a href="/admin/users/create" class="btn btn-primary">Create User</a>
    </div>

    <div class="filters">
        <form method="GET" action="/admin/users">
            <select name="role">
                <option value="">All Roles</option>
                <option value="user" <?= ($filters['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users ?? [] as $user): ?>
            <tr>
                <td><?= htmlspecialchars((string)($user['id'] ?? '')) ?></td>
                <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                <td>
                    <span class="role-badge role-<?= htmlspecialchars($user['role'] ?? 'user') ?>">
                        <?= htmlspecialchars($user['role'] ?? 'user') ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($user['created_at'] ?? '') ?></td>
                <td class="actions">
                    <a href="/admin/users/<?= $user['id'] ?? '' ?>/edit" class="btn-small">Edit</a>
                    <button onclick="deleteUser(<?= $user['id'] ?? '' ?>)" class="btn-small btn-danger">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<script>
const csrfToken = '<?= htmlspecialchars($csrf_token ?? '') ?>';

function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    $.ajax({
        url: '/admin/users/' + id,
        method: 'DELETE',
        headers: {
            'X-CSRF-Token': csrfToken
        },
        success: function() {
            location.reload();
        },
        error: function() {
            alert('Error deleting user');
        }
    });
}
</script>

<?php
$content = ob_get_clean();
$csrf_token = $csrf_token ?? '';
include __DIR__ . '/../../layouts/admin.php';
?>
