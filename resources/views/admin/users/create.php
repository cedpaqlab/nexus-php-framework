<?php
$title = 'Create User';
ob_start();
?>

<section class="admin-section">
    <h1>Create User</h1>
    
    <form id="createUserForm" class="user-form">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>
        
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="/admin/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<script>
$('#createUserForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '/admin/users',
        method: 'POST',
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
            alert('Error creating user');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$csrf_token = $csrf_token ?? '';
include __DIR__ . '/../../layouts/admin.php';
?>
