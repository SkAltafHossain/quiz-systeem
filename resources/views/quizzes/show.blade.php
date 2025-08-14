@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}">Quizzes</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quizzes.index', ['category' => $quiz->category->slug]) }}">{{ $quiz->category->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $quiz->title }}</li>
                </ol>
            </nav>

            <div class="card mb-4">
                @if($quiz->image)
                    <img src="{{ Storage::url($quiz->image) }}" class="card-img-top" alt="{{ $quiz->title }}" style="max-height: 300px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary">{{ $quiz->category->name }}</span>
                            <h1 class="h3 mb-2 mt-2">{{ $quiz->title }}</h1>
                        </div>
                        <div class="text-end">
                            <div class="d-flex align-items-center text-muted mb-1">
                                <i class="fas fa-question-circle me-1"></i>
                                <span>{{ $quiz->questions_count }} questions</span>
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <span>{{ $quiz->time_limit ? $quiz->time_limit . ' min' : 'No time limit' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="mb-2">About this quiz</h5>
                        <p class="text-muted">{{ $quiz->description }}</p>
                    </div>

                    @if($previousAttempt)
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <h6 class="mb-1">Your Previous Attempt</h6>
                                    <p class="mb-0">
                                        Score: {{ $previousAttempt->score }}/{{ $previousAttempt->total_questions }} 
                                        ({{ $previousAttempt->percentage }}%) 
                                        • {{ $previousAttempt->passed ? 'Passed' : 'Failed' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        <a href="{{ route('quizzes.take', $quiz->slug) }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-play-circle me-2"></i>
                            {{ $previousAttempt ? 'Retake Quiz' : 'Start Quiz' }}
                        </a>
                        @if($previousAttempt)
                            <a href="#" class="btn btn-outline-secondary">
                                <i class="fas fa-chart-bar me-2"></i>View Results
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quiz Details Tabs -->
            <div class="card mb-4">
                <ul class="nav nav-tabs" id="quizTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">
                            <i class="fas fa-info-circle me-1"></i> Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="topics-tab" data-bs-toggle="tab" data-bs-target="#topics" type="button" role="tab" aria-controls="topics" aria-selected="false">
                            <i class="fas fa-book me-1"></i> Topics Covered
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab" aria-controls="stats" aria-selected="false">
                            <i class="fas fa-chart-pie me-1"></i> Statistics
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-4" id="quizTabsContent">
                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                        <h5 class="mb-3">Quiz Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-calendar-alt text-primary me-2"></i> <strong>Created:</strong> {{ $quiz->created_at->format('F j, Y') }}</li>
                                    <li class="mb-2"><i class="fas fa-sync-alt text-primary me-2"></i> <strong>Attempts:</strong> {{ $quiz->results_count }} times</li>
                                    <li class="mb-2"><i class="fas fa-trophy text-primary me-2"></i> <strong>Passing Score:</strong> {{ $quiz->passing_score ?? 70 }}%</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-clock text-primary me-2"></i> <strong>Time Limit:</strong> {{ $quiz->time_limit ? $quiz->time_limit . ' minutes' : 'No time limit' }}</li>
                                    <li class="mb-2"><i class="fas fa-redo text-primary me-2"></i> <strong>Retake Policy:</strong> {{ $quiz->retake_after ? 'Can retake after ' . $quiz->retake_after . ' hours' : 'Unlimited attempts' }}</li>
                                    <li class="mb-2"><i class="fas fa-question-circle text-primary me-2"></i> <strong>Questions:</strong> {{ $quiz->questions_count }} total</li>
                                </ul>
                            </div>
                        </div>
                        
                        @if($quiz->instructions)
                            <div class="mt-4">
                                <h6>Instructions</h6>
                                <div class="bg-light p-3 rounded">
                                    {!! nl2br(e($quiz->instructions)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="tab-pane fade" id="topics" role="tabpanel" aria-labelledby="topics-tab">
                        <h5 class="mb-3">Topics Covered</h5>
                        @if($quiz->topics)
                            <ul class="list-group list-group-flush">
                                @foreach(explode('\n', $quiz->topics) as $topic)
                                    @if(trim($topic))
                                        <li class="list-group-item border-0 px-0">
                                            <i class="fas fa-check-circle text-success me-2"></i> {{ trim($topic) }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            <div class="alert alert-info">
                                No specific topics listed for this quiz.
                            </div>
                        @endif
                    </div>
                    
                    <div class="tab-pane fade" id="stats" role="tabpanel" aria-labelledby="stats-tab">
                        <h5 class="mb-3">Quiz Statistics</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light mb-3">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Average Score</h6>
                                        <h2 class="display-4 text-primary">{{ $quiz->average_score ?? 0 }}%</h2>
                                        <p class="text-muted mb-0">based on {{ $quiz->results_count }} attempts</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light mb-3">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Pass Rate</h6>
                                        <h2 class="display-4 text-success">{{ $quiz->pass_rate ?? 0 }}%</h2>
                                        <p class="text-muted mb-0">of users passed this quiz</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6>Score Distribution</h6>
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $quiz->pass_rate ?? 0 }}%" aria-valuenow="{{ $quiz->pass_rate ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $quiz->pass_rate ?? 0 }}% Passed
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ 100 - ($quiz->pass_rate ?? 0) }}%" aria-valuenow="{{ 100 - ($quiz->pass_rate ?? 0) }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ 100 - ($quiz->pass_rate ?? 0) }}% Failed
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quiz Author -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">About the Author</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="{{ $quiz->user->avatar_url }}" 
                             alt="{{ $quiz->user->name }}" 
                             class="rounded-circle" 
                             width="100" 
                             height="100"
                             style="object-fit: cover;">
                    </div>
                    <h5>{{ $quiz->user->name }}</h5>
                    <p class="text-muted">
                        {{ $quiz->user->quizzes()->count() }} quizzes created
                    </p>
                    <p class="small text-muted">
                        {{ $quiz->user->bio ?? 'No bio available' }}
                    </p>
                    <a href="#" class="btn btn-outline-primary btn-sm">View Profile</a>
                </div>
            </div>

            <!-- Related Quizzes -->
            @if($relatedQuizzes->count() > 0)
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">More Quizzes</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($relatedQuizzes as $relatedQuiz)
                            <a href="{{ route('quizzes.show', $relatedQuiz->slug) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $relatedQuiz->title }}</h6>
                                    <small>{{ $relatedQuiz->results_count }} <i class="fas fa-users"></i></small>
                                </div>
                                <small class="text-muted">{{ $relatedQuiz->category->name }}</small>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Share Quiz -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Share This Quiz</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center gap-2">
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('quizzes.show', $quiz->slug)) }}&text={{ urlencode('Check out this quiz: ' . $quiz->title) }}" 
                           target="_blank" 
                           class="btn btn-outline-primary btn-sm rounded-circle"
                           data-bs-toggle="tooltip" 
                           data-bs-placement="top" 
                           title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('quizzes.show', $quiz->slug)) }}" 
                           target="_blank" 
                           class="btn btn-outline-primary btn-sm rounded-circle"
                           data-bs-toggle="tooltip" 
                           data-bs-placement="top" 
                           title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('quizzes.show', $quiz->slug)) }}&title={{ urlencode($quiz->title) }}&summary={{ urlencode($quiz->description) }}" 
                           target="_blank" 
                           class="btn btn-outline-primary btn-sm rounded-circle"
                           data-bs-toggle="tooltip" 
                           data-bs-placement="top" 
                           title="Share on LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <button class="btn btn-outline-secondary btn-sm rounded-circle copy-link"
                                data-url="{{ route('quizzes.show', $quiz->slug) }}"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="Copy link">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Copy link functionality
        document.querySelectorAll('.copy-link').forEach(button => {
            button.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                navigator.clipboard.writeText(url).then(() => {
                    const tooltip = bootstrap.Tooltip.getInstance(this);
                    const originalTitle = this.getAttribute('data-bs-original-title');
                    this.setAttribute('data-bs-original-title', 'Link copied!');
                    tooltip.show();
                    
                    setTimeout(() => {
                        this.setAttribute('data-bs-original-title', originalTitle);
                        tooltip.hide();
                    }, 2000);
                });
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    .nav-tabs .nav-link {
        color: #6c757d;
        font-weight: 500;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        font-weight: 600;
        border-bottom: 3px solid #0d6efd;
    }
    .progress-bar {
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    .btn-outline-primary {
        --bs-btn-hover-color: #fff;
    }
    .btn-outline-secondary {
        --bs-btn-hover-color: #fff;
    }
</style>
@endpush
@endsection
