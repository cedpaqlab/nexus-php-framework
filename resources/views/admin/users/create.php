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
        
        <div id="error-messages" class="error-messages" style="display: none;"></div>
    </form>
</section>

<script>
$('#createUserForm').on('submit', function(e) {
    e.preventDefault();
    
    const $errorMessages = $('#error-messages');
    $errorMessages.hide().empty();
    
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
                let errorHtml = '';
                if (response.errors) {
                    errorHtml = '<ul>';
                    for (const field in response.errors) {
                        const fieldErrors = Array.isArray(response.errors[field]) 
                            ? response.errors[field] 
                            : [response.errors[field]];
                        fieldErrors.forEach(error => {
                            errorHtml += '<li>' + error + '</li>';
                        });
                    }
                    errorHtml += '</ul>';
                } else {
                    errorHtml = '<p>' + (response.error || 'Unknown error') + '</p>';
                }
                $errorMessages.html(errorHtml).show();
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON || {};
            let errorHtml = '';
            if (response.errors) {
                errorHtml = '<ul>';
                for (const field in response.errors) {
                    const fieldErrors = Array.isArray(response.errors[field]) 
                        ? response.errors[field] 
                        : [response.errors[field]];
                    fieldErrors.forEach(error => {
                        errorHtml += '<li>' + error + '</li>';
                    });
                }
                errorHtml += '</ul>';
            } else {
                errorHtml = '<p>' + (response.error || 'An error occurred while creating the user') + '</p>';
            }
            $errorMessages.html(errorHtml).show();
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$csrf_token = $csrf_token ?? '';
include __DIR__ . '/../../layouts/admin.php';
?>
