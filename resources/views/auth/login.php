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
    </script>
</body>
</html>
