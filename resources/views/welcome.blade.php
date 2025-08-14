<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Quiz System') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .feature-icon { 
            width: 48px; 
            height: 48px; 
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-indigo-900">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                {{ config('app.name', 'Quiz System') }}
            </a>
            <div class="d-flex">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-light me-2">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-light">Register</a>
                @else
                    <a href="{{ route('home') }}" class="btn btn-light">Dashboard</a>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero text-white py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Test Your Knowledge</h1>
                    <p class="lead mb-4">Join thousands of users improving their skills with our interactive quizzes.</p>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-light btn-lg me-2">Get Started</a>
                    @else
                        <a href="{{ route('home') }}" class="btn btn-light btn-lg me-2">Go to Dashboard</a>
                    @endguest
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="https://illustrations.popsy.co/white/online-test.svg" alt="Online Quiz" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-5 bg-white">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon mx-auto">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <h4>Quick Tests</h4>
                        <p class="text-muted">Take quizzes on various topics and get instant results.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon mx-auto">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h4>Compete</h4>
                        <p class="text-muted">Climb the leaderboard and earn achievements.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon mx-auto">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Track Progress</h4>
                        <p class="text-muted">Monitor your improvement with detailed analytics.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Quiz System') }}. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
