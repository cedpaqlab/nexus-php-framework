<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nexus PHP Framework</title>
    <link rel="stylesheet" href="/css/home.css">
    <link rel="stylesheet" href="/css/auth.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <nav class="container">
            <a href="/" class="logo">Nexus PHP Framework</a>
            <ul class="nav-links">
                <li><a href="/">Home</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="auth-section">
            <div class="container">
                <div class="auth-card">
                    <h1>Login</h1>
                    <form id="loginForm" class="auth-form">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required autofocus>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                        
                        <div id="error-message" class="error-message" style="display: none;"></div>
                    </form>
                    
                    <div class="quick-access">
                        <p class="quick-access-label">Quick access (test accounts):</p>
                        <div class="quick-access-links">
                            <a href="#" class="quick-link" data-email="superadmin@example.com" data-password="superadmin123">Super Admin</a>
                            <a href="#" class="quick-link" data-email="admin@example.com" data-password="admin123">Admin</a>
                            <a href="#" class="quick-link" data-email="user@example.com" data-password="user123">User</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 Nexus PHP Framework. Built with precision.</p>
        </div>
    </footer>

    <script>
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const $errorMsg = $('#error-message');
            $errorMsg.hide();
            
            $.ajax({
                url: '/login',
                method: 'POST',
                headers: {
                    'X-CSRF-Token': $('input[name="_csrf_token"]').val()
                },
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect || '/dashboard';
                    } else {
                        $errorMsg.text(response.error || 'Login failed').show();
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || {};
                    $errorMsg.text(response.error || 'An error occurred').show();
                }
            });
        });
        
        $('.quick-link').on('click', function(e) {
            e.preventDefault();
            const email = $(this).data('email');
            const password = $(this).data('password');
            
            $('#email').val(email);
            $('#password').val(password);
            $('#loginForm').submit();
        });
    </script>
</body>
</html>
