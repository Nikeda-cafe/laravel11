<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Laravel Rolling Deployment 3</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .hero-header {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            padding: 3rem 2rem;
            text-align: center;
        }

        .laravel-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 0.5rem;
        }

        .hero-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .feature-icon.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .feature-icon.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .feature-icon.orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .feature-card {
            padding: 1.5rem;
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px -15px rgba(0, 0, 0, 0.2);
        }

        .feature-title {
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 0.5rem;
        }

        .feature-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .cta-section {
            padding: 2rem;
            background: #f8f9fa;
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(102, 126, 234, 0.5);
            color: white;
        }

        .btn-outline-custom {
            border: 2px solid var(--gradient-start);
            color: var(--gradient-start);
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-custom:hover {
            background: var(--gradient-start);
            color: white;
        }

        .version-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .footer-links a {
            color: #6c757d;
            text-decoration: none;
            margin: 0 0.75rem;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--gradient-start);
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.8rem;
            }

            .hero-card {
                margin: 1rem;
            }
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">Laravel App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ url('/') }}">ホーム</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.index') }}">ユーザー一覧</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <section class="hero-section">
        <div class="container mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="hero-card">
                        <div class="hero-header">
                            <svg class="laravel-logo" viewBox="0 0 50 52" xmlns="http://www.w3.org/2000/svg">
                                <path d="M49.626 11.564a.809.809 0 0 1 .028.209v10.972a.8.8 0 0 1-.402.694l-9.209 5.302V39.25c0 .286-.152.55-.4.694L20.42 51.01c-.044.025-.092.041-.14.058-.018.006-.035.017-.054.022a.805.805 0 0 1-.41 0c-.022-.006-.042-.018-.063-.026-.044-.016-.09-.03-.132-.054L.402 39.944A.801.801 0 0 1 0 39.25V6.334c0-.072.01-.142.028-.21.006-.023.02-.044.028-.067.015-.042.029-.085.051-.124.015-.026.037-.047.055-.071.023-.032.044-.065.071-.093.023-.023.053-.04.079-.06.029-.024.055-.05.088-.069h.001l9.61-5.533a.802.802 0 0 1 .8 0l9.61 5.533h.002c.032.02.059.045.088.068.026.02.055.038.078.06.028.029.048.062.072.094.017.024.04.045.054.071.023.04.036.082.052.124.008.023.022.044.028.068a.809.809 0 0 1 .028.209v20.559l8.008-4.611v-10.51c0-.07.01-.141.028-.208.007-.024.02-.045.028-.068.016-.042.03-.085.052-.124.015-.026.037-.047.054-.071.024-.032.044-.065.072-.093.023-.023.052-.04.078-.06.03-.024.056-.05.088-.069h.001l9.611-5.533a.801.801 0 0 1 .8 0l9.61 5.533c.034.02.06.045.09.068.025.02.054.038.077.06.028.029.048.062.072.094.018.024.04.045.054.071.023.039.036.082.052.124.009.023.022.044.028.068zm-1.574 10.718v-9.124l-3.363 1.936-4.646 2.675v9.124l8.01-4.611zm-9.61 16.505v-9.13l-4.57 2.61-13.05 7.448v9.216l17.62-10.144zM1.602 7.719v31.068L19.22 48.93v-9.214l-9.204-5.209-.003-.002-.004-.002c-.031-.018-.057-.044-.086-.066-.025-.02-.054-.036-.076-.058l-.002-.003c-.026-.025-.044-.056-.066-.084-.02-.027-.044-.05-.06-.078l-.001-.003c-.018-.03-.029-.066-.042-.1-.013-.03-.03-.058-.038-.09v-.001c-.01-.038-.012-.078-.016-.117-.004-.03-.012-.06-.012-.09v-.002-21.481L4.965 9.654 1.602 7.72zm8.81-5.994L2.405 6.334l8.005 4.609 8.006-4.61-8.006-4.608zm4.164 28.764l4.645-2.674V7.719l-3.363 1.936-4.646 2.675v20.096l3.364-1.937zM39.243 7.164l-8.006 4.609 8.006 4.609 8.005-4.61-8.005-4.608zm-.801 10.605l-4.646-2.675-3.363-1.936v9.124l4.645 2.674 3.364 1.937v-9.124zM20.02 38.33l11.743-6.704 5.87-3.35-8-4.606-9.211 5.303-8.395 4.833 7.993 4.524z" fill="#FF2D20"/>
                            </svg>
                            <h1 class="hero-title">Welcome to Laravel Rolling Deployment 4</h1>
                            <p class="hero-subtitle">The PHP Framework for Web Artisans</p>
                            <span class="version-badge mt-2">Laravel 11</span>
                        </div>

                        <div class="p-4">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="feature-card text-center">
                                        <div class="feature-icon purple mx-auto">
                                            ⚡
                                        </div>
                                        <h5 class="feature-title">高速</h5>
                                        <p class="feature-description">最適化されたパフォーマンスで素早いレスポンスを実現</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-card text-center">
                                        <div class="feature-icon blue mx-auto">
                                            🛡️
                                        </div>
                                        <h5 class="feature-title">セキュア</h5>
                                        <p class="feature-description">堅牢なセキュリティ機能を標準搭載</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-card text-center">
                                        <div class="feature-icon orange mx-auto">
                                            🧩
                                        </div>
                                        <h5 class="feature-title">拡張性</h5>
                                        <p class="feature-description">豊富なパッケージで機能を自在に拡張</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cta-section text-center">
                            <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
                                <a href="https://laravel.com/docs" target="_blank" class="btn btn-gradient">
                                    ドキュメント
                                </a>
                                <a href="https://github.com/laravel/laravel" target="_blank" class="btn btn-outline-custom">
                                    GitHub
                                </a>
                            </div>
                            <div class="footer-links">
                                <a href="https://laravel-news.com" target="_blank">
                                    News
                                </a>
                                <a href="https://laracasts.com" target="_blank">
                                    Laracasts
                                </a>
                                <a href="https://forge.laravel.com" target="_blank">
                                    Forge
                                </a>
                            </div>
                        </div>
                    </div>

                    <p class="text-center text-white mt-4 opacity-75">
                        <small>Built with ❤️ using Laravel & Bootstrap</small>
                    </p>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
