@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-3">
            <!-- User Profile Card -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&color=7F9CF5&background=EBF4FF' }}" 
                         alt="{{ Auth::user()->name }}" 
                         class="rounded-circle img-fluid" 
                         style="width: 120px; height: 120px; object-fit: cover;">
                    <h5 class="my-3">{{ Auth::user()->name }}</h5>
                    <p class="text-muted mb-1">{{ Auth::user()->email }}</p>
                    <p class="text-muted mb-4">Member since {{ Auth::user()->created_at->format('M Y') }}</p>
                    
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user-edit me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="small text-muted mb-1">Quizzes Taken</p>
                            <h6 class="mb-0">{{ $stats['total_quizzes'] }}</h6>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-trophy text-primary"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="small text-muted mb-1">Average Score</p>
                            <h6 class="mb-0">{{ number_format($stats['average_score'], 1) }}%</h6>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-chart-line text-success"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small text-muted mb-1">Quizzes Passed</p>
                            <h6 class="mb-0">{{ $stats['passed_quizzes'] }} of {{ $stats['quizzes_taken'] }}</h6>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Recent Activity</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentActivity as $activity)
                            <a href="{{ route('quizzes.results', [$activity->quiz->slug, $activity->id]) }}" class="list-group-item list-group-item-action border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-light rounded text-{{ $activity->passed ? 'success' : 'danger' }} fs-4">
                                                <i class="fas fa-{{ $activity->passed ? 'check' : 'times' }}"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $activity->quiz->title }}</h6>
                                        <p class="small text-muted mb-0">
                                            {{ $activity->percentage }}% • {{ $activity->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center p-4">
                                <p class="text-muted mb-0">No recent activity</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">My Quiz History</h2>
                <a href="{{ route('leaderboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-trophy me-1"></i> View Leaderboard
                </a>
            </div>
            
            <!-- Filter and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('quiz.history') }}" method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label for="search" class="form-label">Search Quizzes</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Search by quiz name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Results</option>
                                <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>Passed</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="highest" {{ request('sort') == 'highest' ? 'selected' : '' }}>Highest Score</option>
                                <option value="lowest" {{ request('sort') == 'lowest' ? 'selected' : '' }}>Lowest Score</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Apply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quiz Attempts -->
            <div class="card">
                <div class="card-body p-0">
                    @if($attempts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Quiz</th>
                                        <th>Category</th>
                                        <th>Score</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attempts as $attempt)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($attempt->quiz->image)
                                                        <img src="{{ Storage::url($attempt->quiz->image) }}" 
                                                             alt="{{ $attempt->quiz->title }}" 
                                                             class="rounded me-3" 
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-question text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $attempt->quiz->title }}</h6>
                                                        <small class="text-muted">{{ $attempt->quiz->questions_count }} questions</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $attempt->quiz->category->name }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                        <div class="progress-bar bg-{{ $attempt->percentage >= 70 ? 'success' : ($attempt->percentage >= 50 ? 'warning' : 'danger') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ $attempt->percentage }}%" 
                                                             aria-valuenow="{{ $attempt->percentage }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="fw-bold">{{ $attempt->percentage }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($attempt->passed)
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-check-circle me-1"></i> Passed
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                                        <i class="fas fa-times-circle me-1"></i> Failed
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $attempt->created_at->format('M j, Y g:i A') }}">
                                                    {{ $attempt->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton{{ $attempt->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-h"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $attempt->id }}">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('quizzes.results', [$attempt->quiz->slug, $attempt->id]) }}">
                                                                <i class="fas fa-eye me-2"></i> View Results
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('quizzes.take', $attempt->quiz->slug) }}">
                                                                <i class="fas fa-redo me-2"></i> Retake Quiz
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               onclick="event.preventDefault(); document.getElementById('delete-attempt-{{ $attempt->id }}').submit();">
                                                                <i class="fas fa-trash-alt me-2"></i> Delete Attempt
                                                            </a>
                                                            <form id="delete-attempt-{{ $attempt->id }}" 
                                                                  action="{{ route('quiz.attempt.delete', $attempt->id) }}" 
                                                                  method="POST" style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="p-3">
                            {{ $attempts->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center p-5">
                            <div class="mb-4">
                                <i class="fas fa-inbox fa-4x text-muted"></i>
                            </div>
                            <h5>No quiz attempts found</h5>
                            <p class="text-muted">You haven't taken any quizzes yet. Start by browsing our quizzes!</p>
                            <a href="{{ route('quizzes.index') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-search me-2"></i> Browse Quizzes
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .progress {
        min-width: 80px;
    }
    .avatar-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 50%;
    }
    .avatar-title {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        font-size: 1.2rem;
    }
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .dropdown-menu {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 0.5rem;
    }
    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        border-radius: 0.25rem;
        margin: 0.1rem 0.5rem;
        width: auto;
    }
</style>
@endpush

@push('scripts')
<script>
    // Enable tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@endsection
