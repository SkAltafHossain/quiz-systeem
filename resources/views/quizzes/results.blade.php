@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Result Header -->
            <div class="text-center mb-5">
                <div class="mb-3">
                    @if($result->passed)
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-5x mb-3"></i>
                            <h2 class="mb-2">Quiz Completed!</h2>
                        </div>
                        <p class="lead text-muted">Congratulations! You passed the quiz.</p>
                    @else
                        <div class="text-danger">
                            <i class="fas fa-times-circle fa-5x mb-3"></i>
                            <h2 class="mb-2">Quiz Completed</h2>
                        </div>
                        <p class="lead text-muted">You didn't pass this time, but you can try again!</p>
                    @endif
                </div>
                
                <div class="row justify-content-center mb-4">
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <div class="row text-center">
                                    <div class="col-6 border-end">
                                        <h3 class="display-4 fw-bold {{ $result->passed ? 'text-success' : 'text-danger' }}">
                                            {{ $result->percentage }}%
                                        </h3>
                                        <p class="text-muted mb-0">Your Score</p>
                                    </div>
                                    <div class="col-6">
                                        <h3 class="display-4 fw-bold">
                                            {{ $result->score }}/{{ $result->total_questions }}
                                        </h3>
                                        <p class="text-muted mb-0">Correct Answers</p>
                                    </div>
                                </div>
                                
                                @if($quiz->passing_score)
                                    <div class="mt-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Passing Score: {{ $quiz->passing_score }}%</span>
                                            <span>{{ $result->percentage }}%</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-{{ $result->passed ? 'success' : 'danger' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $result->percentage }}%" 
                                                 aria-valuenow="{{ $result->percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('quizzes.show', $quiz->slug) }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Quiz
                    </a>
                    <a href="{{ route('quizzes.take', $quiz->slug) }}" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i> Retake Quiz
                    </a>
                </div>
            </div>
            
            <!-- Detailed Results -->
            <div class="card shadow-sm mb-5">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" id="resultsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="answers-tab" data-bs-toggle="tab" data-bs-target="#answers" type="button" role="tab" aria-controls="answers" aria-selected="true">
                                <i class="fas fa-list-check me-2"></i> Your Answers
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="statistics-tab" data-bs-toggle="tab" data-bs-target="#statistics" type="button" role="tab" aria-controls="statistics" aria-selected="false">
                                <i class="fas fa-chart-pie me-2"></i> Statistics
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content p-4" id="resultsTabsContent">
                        <!-- Answers Tab -->
                        <div class="tab-pane fade show active" id="answers" role="tabpanel" aria-labelledby="answers-tab">
                            @foreach($quiz->questions as $index => $question)
                                @php
                                    $userAnswerIds = $userAnswers[$question->id] ?? [];
                                    $correctAnswerIds = $question->options->where('is_correct', true)->pluck('id')->toArray();
                                    $isCorrect = !array_diff($userAnswerIds, $correctAnswerIds) && !array_diff($correctAnswerIds, $userAnswerIds);
                                @endphp
                                
                                <div class="mb-5">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">
                                            <span class="badge bg-{{ $isCorrect ? 'success' : 'danger' }} me-2">
                                                {{ $index + 1 }}
                                            </span>
                                            {{ $question->text }}
                                        </h5>
                                        <span class="badge bg-{{ $isCorrect ? 'success' : 'danger' }}">
                                            {{ $isCorrect ? 'Correct' : 'Incorrect' }}
                                        </span>
                                    </div>
                                    
                                    @if($question->image)
                                        <div class="mb-3 text-center">
                                            <img src="{{ Storage::url($question->image) }}" 
                                                 alt="Question Image" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 200px;">
                                        </div>
                                    @endif
                                    
                                    <div class="options ps-4">
                                        @foreach($question->options as $option)
                                            @php
                                                $isUserAnswer = in_array($option->id, $userAnswerIds);
                                                $isCorrectOption = $option->is_correct;
                                                
                                                $optionClass = '';
                                                if ($isUserAnswer && $isCorrectOption) {
                                                    $optionClass = 'bg-success bg-opacity-10 border-success';
                                                } elseif ($isUserAnswer && !$isCorrectOption) {
                                                    $optionClass = 'bg-danger bg-opacity-10 border-danger';
                                                } elseif (!$isUserAnswer && $isCorrectOption) {
                                                    $optionClass = 'border-success';
                                                }
                                            @endphp
                                            
                                            <div class="mb-2 p-3 rounded border {{ $optionClass }}">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="{{ $question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}" 
                                                           {{ $isUserAnswer ? 'checked' : '' }} 
                                                           disabled>
                                                    <label class="form-check-label w-100">
                                                        {{ $option->text }}
                                                        @if($isUserAnswer && !$isCorrectOption)
                                                            <span class="text-danger ms-2">
                                                                <i class="fas fa-times"></i> Your answer
                                                            </span>
                                                        @endif
                                                        @if($isCorrectOption)
                                                            <span class="text-success ms-2">
                                                                <i class="fas fa-check"></i> Correct answer
                                                            </span>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    @if($question->explanation)
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Explanation:</strong> {{ $question->explanation }}
                                        </div>
                                    @endif
                                </div>
                                
                                @if(!$loop->last)
                                    <hr class="my-4">
                                @endif
                            @endforeach
                        </div>
                        
                        <!-- Statistics Tab -->
                        <div class="tab-pane fade" id="statistics" role="tabpanel" aria-labelledby="statistics-tab">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Score Distribution</h5>
                                            <canvas id="scoreChart" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Performance Summary</h5>
                                            <div class="list-group list-group-flush">
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>Time Taken</span>
                                                    <span class="fw-bold">{{ gmdate('H:i:s', $result->time_taken) }}</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>Completed On</span>
                                                    <span class="fw-bold">{{ $result->created_at->format('M j, Y g:i A') }}</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>Correct Answers</span>
                                                    <span class="fw-bold">{{ $result->score }} of {{ $result->total_questions }}</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>Passing Score</span>
                                                    <span class="fw-bold">{{ $quiz->passing_score ?? 'N/A' }}%</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>Your Score</span>
                                                    <span class="fw-bold">{{ $result->percentage }}%</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>Result</span>
                                                    <span class="badge bg-{{ $result->passed ? 'success' : 'danger' }}">
                                                        {{ $result->passed ? 'Passed' : 'Failed' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Question Analysis</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Question</th>
                                                    <th>Your Answer</th>
                                                    <th>Correct Answer</th>
                                                    <th>Status</th>
                                                    <th>Time Spent</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($quiz->questions as $index => $question)
                                                    @php
                                                        $userAnswerIds = $userAnswers[$question->id] ?? [];
                                                        $correctAnswerIds = $question->options->where('is_correct', true)->pluck('id')->toArray();
                                                        $isCorrect = !array_diff($userAnswerIds, $correctAnswerIds) && !array_diff($correctAnswerIds, $userAnswerIds);
                                                        
                                                        $userAnswersText = [];
                                                        foreach($userAnswerIds as $id) {
                                                            $option = $question->options->firstWhere('id', $id);
                                                            if ($option) $userAnswersText[] = $option->text;
                                                        }
                                                        
                                                        $correctAnswersText = [];
                                                        foreach($correctAnswerIds as $id) {
                                                            $option = $question->options->firstWhere('id', $id);
                                                            if ($option) $correctAnswersText[] = $option->text;
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td>Question {{ $index + 1 }}</td>
                                                        <td>
                                                            @if(count($userAnswersText) > 0)
                                                                {{ implode(', ', $userAnswersText) }}
                                                            @else
                                                                <span class="text-muted">Not answered</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ implode(', ', $correctAnswersText) }}</td>
                                                        <td>
                                                            @if($isCorrect)
                                                                <span class="badge bg-success">Correct</span>
                                                            @else
                                                                <span class="badge bg-danger">Incorrect</span>
                                                            @endif
                                                        </td>
                                                        <td>--:--</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="text-center mb-5">
                <h4 class="mb-3">What would you like to do next?</h4>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('quizzes.take', $quiz->slug) }}" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i> Retake Quiz
                    </a>
                    <a href="{{ route('quizzes.show', $quiz->slug) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-info-circle me-2"></i> View Quiz Details
                    </a>
                    <a href="{{ route('quizzes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-search me-2"></i> Browse More Quizzes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize score chart
        const ctx = document.getElementById('scoreChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Correct', 'Incorrect'],
                    datasets: [{
                        data: [
                            {{ $result->score }}, 
                            {{ $result->total_questions - $result->score }}
                        ],
                        backgroundColor: [
                            '#198754',
                            '#dc3545'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>

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
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.3s ease;
    }
    .options .form-check-input:checked + .form-check-label {
        font-weight: 600;
    }
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
</style>
@endpush
@endsection
