@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold mb-3">Leaderboard</h1>
            <p class="lead text-muted">See how you compare with other quiz takers</p>
            
            <!-- Tabs -->
            <ul class="nav nav-pills justify-content-center mb-4" id="leaderboardTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overall-tab" data-bs-toggle="pill" data-bs-target="#overall" type="button" role="tab" aria-controls="overall" aria-selected="true">
                        <i class="fas fa-trophy me-2"></i> Overall
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="recent-tab" data-bs-toggle="pill" data-bs-target="#recent" type="button" role="tab" aria-controls="recent" aria-selected="false">
                        <i class="fas fa-bolt me-2"></i> Recent High Scores
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="quizzes-tab" data-bs-toggle="pill" data-bs-target="#quizzes" type="button" role="tab" aria-controls="quizzes" aria-selected="false">
                        <i class="fas fa-star me-2"></i> Top Quizzes
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Current User Stats (if logged in) -->
            @auth
                @if($currentUser && $currentUser->quiz_count > 0)
                    <div class="card mb-4 border-primary">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center mb-3 mb-md-0">
                                    <div class="position-relative d-inline-block">
                                        <img src="{{ $currentUser->avatar ? Storage::url($currentUser->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($currentUser->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                             alt="{{ $currentUser->name }}" 
                                             class="rounded-circle" 
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                            #{{ $currentUser->position }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-4 mb-2 mb-md-0">
                                            <h5 class="mb-1">{{ $currentUser->name }}</h5>
                                            <p class="text-muted mb-0">Your Position</p>
                                        </div>
                                        <div class="col-md-4 mb-2 mb-md-0">
                                            <h5 class="mb-1">{{ number_format($currentUser->avg_score, 1) }}%</h5>
                                            <p class="text-muted mb-0">Average Score</p>
                                        </div>
                                        <div class="col-md-4">
                                            <h5 class="mb-1">{{ $currentUser->quiz_count }}</h5>
                                            <p class="text-muted mb-0">Quizzes Taken</p>
                                        </div>
                                    </div>
                                    <div class="progress mt-3" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: {{ $currentUser->avg_score }}%" 
                                             aria-valuenow="{{ $currentUser->avg_score }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fs-4"></i>
                            <div>
                                <h5 class="mb-1">Start taking quizzes to appear on the leaderboard!</h5>
                                <p class="mb-0">You need to complete at least 3 quizzes to be ranked.</p>
                            </div>
                            <a href="{{ route('quizzes.index') }}" class="btn btn-primary ms-auto">
                                Browse Quizzes <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                @endif
            @else
                <div class="alert alert-warning mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-sign-in-alt me-3 fs-4"></i>
                        <div>
                            <h5 class="mb-1">Sign in to track your progress!</h5>
                            <p class="mb-0">Log in or create an account to see your position on the leaderboard.</p>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ route('login') }}" class="btn btn-outline-warning me-2">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-warning">Register</a>
                        </div>
                    </div>
                </div>
            @endauth
            
            <!-- Tab Content -->
            <div class="tab-content" id="leaderboardTabsContent">
                <!-- Overall Leaderboard -->
                <div class="tab-pane fade show active" id="overall" role="tabpanel" aria-labelledby="overall-tab">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-trophy text-warning me-2"></i>
                                    Top Performers
                                </h5>
                                <small class="text-muted">Ranked by average score (min. 3 quizzes)</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($leaderboard->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">#</th>
                                                <th>User</th>
                                                <th class="text-center">Quizzes</th>
                                                <th class="text-center">Highest Score</th>
                                                <th class="text-center">Average Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leaderboard as $index => $user)
                                                <tr class="{{ auth()->id() === $user->id ? 'table-primary' : '' }}">
                                                    <td class="fw-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                                                 alt="{{ $user->name }}" 
                                                                 class="rounded-circle me-3" 
                                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                                            <div>
                                                                <h6 class="mb-0">
                                                                    {{ $user->name }}
                                                                    @if(auth()->id() === $user->id)
                                                                        <span class="badge bg-primary ms-2">You</span>
                                                                    @endif
                                                                </h6>
                                                                <small class="text-muted">{{ $user->quiz_count }} quizzes</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark">{{ $user->quiz_count }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success bg-opacity-10 text-success">
                                                            {{ number_format($user->highest_score, 1) }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                                <div class="progress-bar bg-{{ $user->avg_score >= 70 ? 'success' : ($user->avg_score >= 50 ? 'warning' : 'danger') }}" 
                                                                     role="progressbar" 
                                                                     style="width: {{ $user->avg_score }}%" 
                                                                     aria-valuenow="{{ $user->avg_score }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <span class="fw-bold" style="min-width: 40px;">{{ number_format($user->avg_score, 1) }}%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center p-5">
                                    <i class="fas fa-trophy fa-4x text-muted mb-3"></i>
                                    <h5>No leaderboard data yet</h5>
                                    <p class="text-muted">Be the first to take a quiz and top the leaderboard!</p>
                                    <a href="{{ route('quizzes.index') }}" class="btn btn-primary">
                                        <i class="fas fa-play me-2"></i> Start a Quiz
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Recent High Scores -->
                <div class="tab-pane fade" id="recent" role="tabpanel" aria-labelledby="recent-tab">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt text-warning me-2"></i>
                                Recent High Scores
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if($recentHighScores->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentHighScores as $score)
                                        <div class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-md-6 mb-2 mb-md-0">
                                                    <div class="d-flex align-items-center">
                                                        <div class="position-relative me-3">
                                                            <img src="{{ $score->user->avatar ? Storage::url($score->user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($score->user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                                                 alt="{{ $score->user->name }}" 
                                                                 class="rounded-circle" 
                                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                                            @if($loop->index < 3)
                                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                                                    {{ $loop->index + 1 }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">{{ $score->user->name }}</h6>
                                                            <p class="mb-0 text-muted small">
                                                                {{ $score->quiz->title }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 text-center text-md-start mb-2 mb-md-0">
                                                    <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                                        <div class="me-2">
                                                            <span class="badge bg-success bg-opacity-10 text-success p-2">
                                                                <i class="fas fa-star me-1"></i> {{ $score->percentage }}%
                                                            </span>
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $score->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 text-md-end">
                                                    <a href="{{ route('quizzes.show', $score->quiz->slug) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-play me-1"></i> Take Quiz
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-chart-line me-1"></i> Stats
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center p-5">
                                    <i class="fas fa-bolt fa-4x text-muted mb-3"></i>
                                    <h5>No recent high scores</h5>
                                    <p class="text-muted">Be the first to achieve a high score!</p>
                                    <a href="{{ route('quizzes.index') }}" class="btn btn-primary">
                                        <i class="fas fa-play me-2"></i> Start a Quiz
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Top Quizzes -->
                <div class="tab-pane fade" id="quizzes" role="tabpanel" aria-labelledby="quizzes-tab">
                    <div class="row">
                        @forelse($topQuizzes as $quiz)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 shadow-sm">
                                    @if($quiz->image)
                                        <img src="{{ Storage::url($quiz->image) }}" 
                                             class="card-img-top" 
                                             alt="{{ $quiz->title }}"
                                             style="height: 160px; object-fit: cover;">
                                    @endif
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">{{ $quiz->title }}</h5>
                                            <span class="badge bg-light text-dark">
                                                {{ $quiz->questions_count }} {{ Str::plural('question', $quiz->questions_count) }}
                                            </span>
                                        </div>
                                        
                                        <p class="card-text text-muted small">
                                            {{ Str::limit($quiz->description, 100) }}
                                        </p>
                                        
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small">Average Score</span>
                                                <span class="small fw-bold">{{ number_format($quiz->avg_score, 1) }}%</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $quiz->avg_score >= 70 ? 'success' : ($quiz->avg_score >= 50 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $quiz->avg_score }}%" 
                                                     aria-valuenow="{{ $quiz->avg_score }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-light text-dark">
                                                    <i class="fas fa-users me-1"></i> {{ $quiz->results_count }} {{ Str::plural('attempt', $quiz->results_count) }}
                                                </span>
                                                <span class="badge bg-light text-dark ms-2">
                                                    <i class="fas fa-clock me-1"></i> {{ $quiz->time_limit }} min
                                                </span>
                                            </div>
                                            <a href="{{ route('quizzes.show', $quiz->slug) }}" class="btn btn-primary btn-sm">
                                                Take Quiz <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center p-5">
                                    <i class="fas fa-star fa-4x text-muted mb-3"></i>
                                    <h5>No quiz data available</h5>
                                    <p class="text-muted">There are no quizzes with enough attempts to display rankings.</p>
                                    <a href="{{ route('quizzes.index') }}" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i> Browse Quizzes
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- How It Works Section -->
    <div class="row mt-5">
        <div class="col-12 text-center mb-4">
            <h3 class="fw-bold">How the Leaderboard Works</h3>
            <p class="text-muted">Understand how rankings are calculated and how you can climb the leaderboard</p>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-trophy fa-2x text-primary"></i>
                    </div>
                    <h5>Ranking System</h5>
                    <p class="text-muted">Users are ranked by their average score across all quizzes. You need to complete at least 3 quizzes to be ranked.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-star fa-2x text-success"></i>
                    </div>
                    <h5>Scoring</h5>
                    <p class="text-muted">Your score is calculated as a percentage of correct answers. Higher scores place you higher on the leaderboard.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-sync-alt fa-2x text-warning"></i>
                    </div>
                    <h5>Updates</h5>
                    <p class="text-muted">The leaderboard updates in real-time as users complete quizzes. Check back often to see your progress!</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .nav-pills .nav-link {
        border-radius: 50px;
        padding: 0.5rem 1.25rem;
        font-weight: 500;
        color: #6c757d;
        margin: 0 0.25rem 0.5rem;
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: white;
        box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
    }
    .nav-pills .nav-link i {
        margin-right: 5px;
    }
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.3s ease;
    }
    .card {
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
        padding: 1.25rem 1.5rem;
    }
    .list-group-item:first-child {
        border-top: none;
    }
    .list-group-item:last-child {
        border-bottom: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // Enable tooltips
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Store the active tab in localStorage when changed
        var leaderboardTabs = document.getElementById('leaderboardTabs');
        if (leaderboardTabs) {
            leaderboardTabs.addEventListener('shown.bs.tab', function (event) {
                var activeTab = event.target.getAttribute('id');
                localStorage.setItem('activeLeaderboardTab', activeTab);
            });
            
            // Activate the stored tab on page load
            var activeTabId = localStorage.getItem('activeLeaderboardTab');
            if (activeTabId) {
                var activeTab = document.querySelector('#' + activeTabId);
                if (activeTab) {
                    var tab = new bootstrap.Tab(activeTab);
                    tab.show();
                }
            }
        }
    });
</script>
@endpush
@endsection
