<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Panel - {{ config('app.name', 'Quiz System') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
            --top-navbar-height: 56px;
        }
        
        body {
            font-size: 0.9rem;
            background-color: #f8f9fa;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #2c3e50;
            color: #fff;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            background: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu li a:hover, 
        .sidebar-menu li a.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-left-color: #3498db;
        }
        
        .sidebar-menu li a i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        .sidebar-menu .submenu {
            padding-left: 20px;
            list-style: none;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .sidebar-menu .submenu.show {
            max-height: 1000px;
            transition: max-height 0.5s ease-in;
        }
        
        .sidebar-menu .has-submenu > a::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            float: right;
            transition: transform 0.3s;
        }
        
        .sidebar-menu .has-submenu.active > a::after {
            transform: rotate(-180deg);
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: calc(100vh - var(--top-navbar-height));
            transition: all 0.3s;
        }
        
        /* Top Navbar */
        .top-navbar {
            height: var(--top-navbar-height);
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            right: 0;
            left: var(--sidebar-width);
            z-index: 999;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .top-navbar .navbar-toggler {
            border: none;
            font-size: 1.25rem;
            color: #495057;
        }
        
        .user-dropdown .dropdown-toggle {
            color: #495057;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .user-dropdown img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.25rem;
        }
        
        .card-title {
            margin-bottom: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        /* Tables */
        .table th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: none;
            padding: 1rem;
            color: #6c757d;
        }
        
        .table td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }
        
        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .top-navbar {
                left: 0;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .main-content.sidebar-collapsed {
                margin-left: 0;
            }
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Custom Toggle Switch */
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        /* Loading Spinner */
        .spinner-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">{{ config('app.name', 'Quiz System') }}</h4>
            <small class="text-muted">Admin Panel</small>
        </div>
        
        <ul class="sidebar-menu mt-3">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <li class="has-submenu {{ request()->is('admin/categories*') ? 'active' : '' }}">
                <a href="#" class="menu-toggle">
                    <i class="fas fa-folder"></i> Categories
                </a>
                <ul class="submenu {{ request()->is('admin/categories*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.index') ? 'active' : '' }}">
                            <i class="fas fa-list"></i> All Categories
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="has-submenu {{ request()->is('admin/quizzes*') ? 'active' : '' }}">
                <a href="#" class="menu-toggle">
                    <i class="fas fa-question-circle"></i> Quizzes
                </a>
                <ul class="submenu {{ request()->is('admin/quizzes*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('admin.quizzes.index') }}" class="{{ request()->routeIs('admin.quizzes.index') ? 'active' : '' }}">
                            <i class="fas fa-list"></i> All Quizzes
                        </a>
                    </li>
                </ul>
            </li>
            
            @php
                // Get the first quiz to use as default in the navigation
                $quizzes = \App\Models\Quiz::latest()->take(5)->get();
                $hasQuizzes = $quizzes->isNotEmpty();
            @endphp
            
            <li class="has-submenu {{ request()->is('admin/quizzes/*/questions*') ? 'active' : '' }}">
                <a href="#" class="menu-toggle">
                    <i class="fas fa-question"></i> Questions
                </a>
                <ul class="submenu {{ request()->is('admin/quizzes/*/questions*') ? 'show' : '' }}">
                    @if($hasQuizzes)
                        @foreach($quizzes as $quiz)
                            <li>
                                <a href="{{ route('admin.quizzes.questions.index', $quiz) }}" class="{{ request()->routeIs('admin.quizzes.questions.index') && request()->route('quiz') == $quiz->id ? 'active' : '' }}">
                                    <i class="fas fa-list"></i> {{ Str::limit($quiz->title, 20) }}
                                </a>
                            </li>
                        @endforeach
                        
                        @if(\App\Models\Quiz::count() > 5)
                            <li class="divider"></li>
                            <li>
                                <a href="{{ route('admin.quizzes.index') }}">
                                    <i class="fas fa-ellipsis-h"></i> View All Quizzes
                                </a>
                            </li>
                        @endif
                    @else
                        <li>
                            <a href="{{ route('admin.quizzes.create') }}">
                                <i class="fas fa-plus"></i> Create Your First Quiz
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
            
            <li class="has-submenu {{ request()->is('admin/results*') ? 'active' : '' }}">
                <a href="#" class="menu-toggle">
                    <i class="fas fa-chart-bar"></i> Results
                </a>
                <ul class="submenu {{ request()->is('admin/results*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('admin.results.index') }}" class="{{ request()->routeIs('admin.results.index') ? 'active' : '' }}">
                            <i class="fas fa-list"></i> All Results
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.results.export') }}" class="{{ request()->routeIs('admin.results.export') ? 'active' : '' }}">
                            <i class="fas fa-file-export"></i> Export Results
                        </a>
                    </li>
                </ul>
            </li>
            
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            
            <li class="mt-4">
                <a href="{{ route('home') }}" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <button class="btn btn-link text-dark d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="d-flex align-items-center">
            <div class="dropdown user-dropdown">
                <a href="#" class="dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ Auth::user()->name }}">
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content" id="main-content">
        @yield('content')
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mainContent = document.getElementById('main-content');
            
            if (sidebar && sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    mainContent.classList.toggle('sidebar-collapsed');
                });
            }
            
            // Toggle submenus
            const menuToggles = document.querySelectorAll('.menu-toggle');
            menuToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const submenu = this.nextElementSibling;
                    const parent = this.parentElement;
                    
                    // Close other open submenus at the same level
                    if (parent.classList.contains('has-submenu')) {
                        const openMenus = document.querySelectorAll('.has-submenu.active');
                        openMenus.forEach(function(menu) {
                            if (menu !== parent && !menu.contains(parent)) {
                                menu.classList.remove('active');
                                const otherSubmenu = menu.querySelector('.submenu');
                                if (otherSubmenu) {
                                    otherSubmenu.style.maxHeight = '0';
                                }
                            }
                        });
                        
                        // Toggle current submenu
                        parent.classList.toggle('active');
                        if (submenu) {
                            if (submenu.style.maxHeight) {
                                submenu.style.maxHeight = null;
                            } else {
                                submenu.style.maxHeight = submenu.scrollHeight + 'px';
                            }
                        }
                    }
                });
            });
            
            // Auto-expand active menu on page load
            const activeMenu = document.querySelector('.sidebar-menu .active');
            if (activeMenu) {
                let parent = activeMenu.closest('.has-submenu');
                while (parent) {
                    parent.classList.add('active');
                    const submenu = parent.querySelector('.submenu');
                    if (submenu) {
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    }
                    parent = parent.parentElement.closest('.has-submenu');
                }
            }
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Global AJAX setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Global error handler for AJAX requests
        $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
            let message = 'An error occurred while processing your request.';
            
            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                message = jqXHR.responseJSON.message;
            } else if (jqXHR.status === 0) {
                message = 'No internet connection or server is down.';
            } else if (jqXHR.status === 403) {
                message = 'You are not authorized to perform this action.';
            } else if (jqXHR.status === 404) {
                message = 'The requested resource was not found.';
            } else if (jqXHR.status === 419) {
                message = 'The page has expired. Please refresh and try again.';
            } else if (jqXHR.status === 422) {
                message = 'The given data was invalid.';
            } else if (jqXHR.status === 500) {
                message = 'An internal server error occurred.';
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#0d6efd',
            });
        });
    </script>
    
    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
