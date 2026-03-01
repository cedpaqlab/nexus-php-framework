<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ $csrf_token ?? '' }}">
    <title>@yield('title', 'Admin Panel') - Nexus PHP Framework</title>
    <link rel="stylesheet" href="/css/home.css">
    <link rel="stylesheet" href="/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <nav class="container">
            <a href="/admin" class="logo">Admin Panel</a>
            <ul class="nav-links">
                <li><a href="/admin">Dashboard</a></li>
                <li><a href="/admin/users">Users</a></li>
                <li><a href="/">Home</a></li>
                <li>
                    <form method="POST" action="/logout" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-logout" style="background: none; border: none; color: inherit; cursor: pointer; font: inherit; padding: 0;">Logout</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 Nexus PHP Framework. Admin Panel.</p>
        </div>
    </footer>
</body>
</html>
