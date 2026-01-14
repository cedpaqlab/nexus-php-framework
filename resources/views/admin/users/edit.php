<?php
$title = 'Edit User';
ob_start();
?>

<section class="admin-section">
    <h1>Edit User</h1>
    
    <form id="editUserForm" class="user-form">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password" minlength="8">
        </div>
        
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="user" <?= ($user['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="/admin/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<script>
$('#editUserForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '/admin/users/<?= $user['id'] ?? '' ?>',
        method: 'PUT',
        headers: {
            'X-CSRF-Token': $('input[name="_csrf_token"]').val()
        },
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                window.location.href = '/admin/users';
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Error updating user');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$csrf_token = $csrf_token ?? '';
include __DIR__ . '/../../layouts/admin.php';
?>
