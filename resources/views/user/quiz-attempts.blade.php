@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quiz.history') }}">My Quiz History</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $quiz->title }}</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">{{ $quiz->title }}</h1>
                <a href="{{ route('quizzes.take', $quiz->slug) }}" class="btn btn-primary">
                    <i class="fas fa-redo me-2"></i> Retake Quiz
                </a>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-4">
            <!-- Quiz Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    @if($quiz->image)
                        <img src="{{ Storage::url($quiz->image) }}" 
                             class="img-fluid rounded mb-3" 
                             alt="{{ $quiz->title }}"
                             style="width: 100%; height: 180px; object-fit: cover;">
                    @endif
                    
                    <h5 class="card-title">Quiz Information</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-layer-group text-primary me-2"></i>
                            <strong>Category:</strong> {{ $quiz->category->name }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-question-circle text-primary me-2"></i>
                            <strong>Questions:</strong> {{ $quiz->questions_count }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <strong>Time Limit:</strong> {{ $quiz->time_limit }} minutes
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-trophy text-primary me-2"></i>
                            <strong>Passing Score:</strong> {{ $quiz->passing_score ?? 'N/A' }}%
                        </li>
                    </ul>
                    
                    <div class="d-grid">
                        <a href="{{ route('quizzes.show', $quiz->slug) }}" class="btn btn-outline-primary">
                            <i class="fas fa-info-circle me-2"></i> View Quiz Details
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quiz Stats -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Your Performance</h5>
                    
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block mb-3">
                            <div class="progress-circle" 
                                 data-value="{{ $stats['highest_score'] }}" 
                                 data-size="150" 
                                 data-thickness="10" 
                                 data-fill="{{ $stats['highest_score'] >= 70 ? '#198754' : ($stats['highest_score'] >= 50 ? '#ffc107' : '#dc3545') }}">
                                <div class="progress-circle-value">{{ number_format($stats['highest_score'], 1) }}%</div>
                            </div>
                        </div>
                        <h5>Highest Score</h5>
                        <p class="text-muted">Your best attempt on this quiz</p>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-1">{{ $stats['attempts'] }}</h3>
                                <p class="mb-0 text-muted small">Attempts</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-1">{{ number_format($stats['average_score'], 1) }}%</h3>
                                <p class="mb-0 text-muted small">Avg. Score</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-1">{{ $stats['passed'] }}</h3>
                                <p class="mb-0 text-muted small">Passed</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-1">{{ $stats['failed'] }}</h3>
                                <p class="mb-0 text-muted small">Failed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quiz Leaderboard -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Top Performers</h5>
                    
                    @if($quizLeaderboard->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($quizLeaderboard as $index => $result)
                                <div class="list-group-item px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                                {{ $index + 1 }}
                                            </span>
                                            <img src="{{ $result->user->avatar ? Storage::url($result->user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($result->user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                                 alt="{{ $result->user->name }}" 
                                                 class="rounded-circle" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">
                                                {{ $result->user->name }}
                                                @if(auth()->id() === $result->user_id)
                                                    <span class="badge bg-primary ms-2">You</span>
                                                @endif
                                            </h6>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $result->max_score >= 70 ? 'success' : ($result->max_score >= 50 ? 'warning' : 'danger') }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $result->max_score }}%" 
                                                         aria-valuenow="{{ $result->max_score }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="fw-bold" style="min-width: 45px;">{{ $result->max_score }}%</span>
                                            </div>
                                        </div>
                                        <div class="ms-2 text-muted small text-end">
                                            <div>{{ $result->time_taken_formatted }}</div>
                                            <div class="small">Best Time</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if(!in_array(auth()->id(), $quizLeaderboard->pluck('user_id')->toArray()))
                            <div class="text-center mt-3">
                                <a href="{{ route('quizzes.take', $quiz->slug) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-trophy me-1"></i> Get on the Leaderboard
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No leaderboard data yet</p>
                            <p class="text-muted small">Be the first to take this quiz!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Your Quiz Attempts</h5>
                        <div>
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchAttempts" placeholder="Search attempts...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($attempts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Score</th>
                                        <th>Time Taken</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="attemptsTableBody">
                                    @foreach($attempts as $attempt)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="bg-light rounded p-2 text-center" style="width: 50px;">
                                                            <div class="text-primary fw-bold">{{ $attempt->created_at->format('d') }}</div>
                                                            <div class="text-muted small">{{ $attempt->created_at->format('M') }}</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div>{{ $attempt->created_at->format('h:i A') }}</div>
                                                        <small class="text-muted">{{ $attempt->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
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
                                                    <span class="fw-bold" style="min-width: 45px;">{{ $attempt->percentage }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                {{ gmdate('i\m s\s', $attempt->time_taken) }}
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
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                            type="button" 
                                                            id="dropdownMenuButton{{ $attempt->id }}" 
                                                            data-bs-toggle="dropdown" 
                                                            aria-expanded="false">
                                                        <i class="fas fa-ellipsis-h"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $attempt->id }}">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('quizzes.results', [$quiz->slug, $attempt->id]) }}">
                                                                <i class="fas fa-eye me-2"></i> View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deleteAttemptModal{{ $attempt->id }}">
                                                                <i class="fas fa-trash-alt me-2"></i> Delete Attempt
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                
                                                <!-- Delete Attempt Modal -->
                                                <div class="modal fade" id="deleteAttemptModal{{ $attempt->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Delete Attempt</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete this attempt? This action cannot be undone.</p>
                                                                <p class="mb-0"><strong>Date:</strong> {{ $attempt->created_at->format('F j, Y \a\t h:i A') }}</p>
                                                                <p class="mb-0"><strong>Score:</strong> {{ $attempt->percentage }}%</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('quiz.attempt.delete', $attempt->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">
                                                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="p-3">
                            {{ $attempts->links() }}
                        </div>
                    @else
                        <div class="text-center p-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5>No quiz attempts found</h5>
                            <p class="text-muted">You haven't taken this quiz yet. Start your first attempt now!</p>
                            <a href="{{ route('quizzes.take', $quiz->slug) }}" class="btn btn-primary mt-3">
                                <i class="fas fa-play me-2"></i> Start Quiz
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Performance Chart -->
            @if($attempts->count() > 1)
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Your Progress Over Time</h5>
                        <canvas id="performanceChart" height="250"></canvas>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .progress-circle {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
    }
    .progress-circle-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5rem;
        font-weight: bold;
    }
    .breadcrumb {
        background-color: transparent;
        padding: 0.5rem 0;
        margin-bottom: 1rem;
    }
    .breadcrumb-item a {
        color: #6c757d;
        text-decoration: none;
    }
    .breadcrumb-item.active {
        color: #0d6efd;
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
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/progressbar.js@1.1.0/dist/progressbar.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize progress circles
        var progressBars = document.querySelectorAll('.progress-circle');
        progressBars.forEach(function(progressBar) {
            var value = parseFloat(progressBar.getAttribute('data-value')) / 100;
            var size = parseInt(progressBar.getAttribute('data-size')) || 150;
            var thickness = parseInt(progressBar.getAttribute('data-thickness')) || 10;
            var fill = progressBar.getAttribute('data-fill') || '#0d6efd';
            
            var bar = new ProgressBar.Circle(progressBar, {
                color: fill,
                strokeWidth: thickness,
                trailWidth: thickness / 2,
                trailColor: '#f5f5f5',
                easing: 'easeInOut',
                duration: 1400,
                text: {
                    value: '0%',
                    className: 'progress-circle-value',
                    style: {
                        color: '#495057',
                        position: 'absolute',
                        left: '50%',
                        top: '50%',
                        padding: 0,
                        margin: 0,
                        transform: 'translate(-50%, -50%)',
                        fontSize: '1.5rem',
                        fontWeight: 'bold'
                    }
                },
                step: function(state, circle) {
                    var value = Math.round(circle.value() * 100);
                    circle.setText(value + '%');
                }
            });
            
            bar.animate(value);
        });
        
        // Search functionality for attempts
        var searchInput = document.getElementById('searchAttempts');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                var searchTerm = this.value.toLowerCase();
                var rows = document.querySelectorAll('#attemptsTableBody tr');
                
                rows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
        
        // Performance chart
        @if($attempts->count() > 1)
            var ctx = document.getElementById('performanceChart').getContext('2d');
            var labels = [];
            var scores = [];
            var passed = [];
            
            @foreach($attempts->sortBy('created_at') as $attempt)
                labels.push('{{ $attempt->created_at->format("M j") }}');
                scores.push({{ $attempt->percentage }});
                passed.push({{ $attempt->passed ? 1 : 0 }});
            @endforeach
            
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.reverse(),
                    datasets: [{
                        label: 'Score (%)',
                        data: scores.reverse(),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: function(context) {
                            var index = context.dataIndex;
                            return passed[index] ? '#198754' : '#dc3545';
                        },
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Score: ' + context.raw + '%';
                                },
                                afterLabel: function(context) {
                                    var index = context.dataIndex;
                                    return 'Status: ' + (passed[index] ? 'Passed' : 'Failed');
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        @endif
    });
</script>
@endpush
@endsection
