<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nexus PHP Framework - Built for performance, security, and scalability">
    <title>Nexus PHP Framework</title>
    <link rel="stylesheet" href="/css/home.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <nav class="container">
            <a href="/" class="logo">Nexus PHP Framework</a>
            <ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#docs">Docs</a></li>
                <li><a href="#github">GitHub</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Modern PHP Framework</h1>
                    <p>Built for performance, security, and scalability. A minimalist foundation for ambitious applications.</p>
                    <div class="cta-buttons">
                        <a href="#docs" class="btn btn-primary">Get Started</a>
                        <a href="#github" class="btn btn-secondary">View on GitHub</a>
                    </div>
                </div>
            </div>
        </section>

        <div class="gradient-line"></div>

        <section id="features" class="container">
            <div class="grid">
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Performance</h3>
                    <p>Optimized from the ground up. Every component is designed for speed and efficiency.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Security</h3>
                    <p>OWASP Top 10 protection built-in. Secure by default, always.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📦</div>
                    <h3>Modular</h3>
                    <p>Domain-driven architecture. Scale without refactoring.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🧪</div>
                    <h3>Tested</h3>
                    <p>Comprehensive test suite. Confidence in every deployment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🚀</div>
                    <h3>Modern</h3>
                    <p>PHP 8.2+ features. Latest standards and best practices.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Focused</h3>
                    <p>No bloat. Only what you need, when you need it.</p>
                </div>
            </div>
        </section>

        <div class="gradient-line"></div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 Nexus PHP Framework. Built with precision.</p>
        </div>
    </footer>

    <script>
        $(document).ready(function() {
            function checkVisibility() {
                $('.feature-card').each(function() {
                    const elementTop = $(this).offset().top;
                    const elementBottom = elementTop + $(this).outerHeight();
                    const viewportTop = $(window).scrollTop();
                    const viewportBottom = viewportTop + $(window).height();

                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).addClass('visible');
                    }
                });
            }

            $(window).on('scroll resize', checkVisibility);
            checkVisibility();

            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 800);
                }
            });

            $(window).on('scroll', function() {
                if ($(window).scrollTop() > 50) {
                    $('header').css('background', 'rgba(10, 10, 10, 0.95)');
                } else {
                    $('header').css('background', 'rgba(10, 10, 10, 0.8)');
                }
            });
        });
    </script>
</body>
</html>
