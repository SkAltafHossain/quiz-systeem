@extends('admin.layouts.app')

@section('title', 'Quiz Result Details')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
<style>
    .result-summary {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .result-summary .row {
        margin-bottom: 1rem;
    }
    .result-summary .label {
        font-weight: 600;
        color: #6c757d;
    }
    .result-summary .value {
        font-size: 1.1rem;
        color: #212529;
    }
    .question-card {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .question-card .card-header {
        background-color: #f8f9fa;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e9ecef;
    }
    .question-card .card-body {
        padding: 1.25rem;
    }
    .option {
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
        background-color: #f8f9fa;
    }
    .option.correct {
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    .option.incorrect {
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    .option.selected {
        font-weight: 600;
    }
    .chart-container {
        position: relative;
        height: 250px;
        margin-bottom: 2rem;
    }
    .time-spent {
        font-size: 1.25rem;
        font-weight: 600;
        color: #0d6efd;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.results.index') }}">Quiz Results</a></li>
                        <li class="breadcrumb-item active">Result Details</li>
                    </ol>
                </div>
                <h4 class="page-title">Quiz Result Details</h4>
            </div>
        </div>
    </div>

    <!-- Result Summary -->
    <div class="row">
        <div class="col-12">
            <div class="result-summary">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <div class="label">User</div>
                            <div class="value">{{ $result->user->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <div class="label">Quiz</div>
                            <div class="value">{{ $result->quiz->title }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <div class="label">Score</div>
                            <div class="value">
                                @php
                                    $percentage = $result->score > 0 ? round(($result->score / $result->max_score) * 100) : 0;
                                    $badgeClass = $result->is_passed ? 'bg-success' : 'bg-danger';
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $result->score }}/{{ $result->max_score }} ({{ $percentage }}%)
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <div class="label">Status</div>
                            <div class="value">
                                @if($result->is_passed)
                                    <span class="badge bg-success">Passed</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <div class="label">Completed At</div>
                            <div class="value">{{ $result->completed_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <div class="label">Time Spent</div>
                            <div class="time-spent">
                                {{ gmdate('H:i:s', $result->time_spent) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <div class="label">IP Address</div>
                            <div class="value">{{ $result->ip_address ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Performance Overview</h4>
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions & Answers -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Questions & Answers</h4>
                    
                    @php $questionNumber = 1; @endphp
                    @foreach($result->quiz->questions as $question)
                        @php
                            $userAnswer = $result->answers->where('question_id', $question->id)->first();
                            $isCorrect = $userAnswer ? $userAnswer->is_correct : false;
                        @endphp
                        
                        <div class="question-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Question #{{ $questionNumber++ }}</h5>
                                @if($userAnswer)
                                    @if($isCorrect)
                                        <span class="badge bg-success">Correct</span>
                                    @else
                                        <span class="badge bg-danger">Incorrect</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Not Answered</span>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="question-text mb-3">
                                    {!! $question->question_text !!}
                                </div>
                                
                                @if($question->question_type === 'multiple_choice' || $question->question_type === 'single_choice')
                                    <div class="options">
                                        @foreach($question->options as $option)
                                            @php
                                                $isSelected = $userAnswer && in_array($option->id, json_decode($userAnswer->selected_options, true) ?? []);
                                                $optionClass = '';
                                                
                                                if ($isSelected && $isCorrect && $option->is_correct) {
                                                    $optionClass = 'correct selected';
                                                } elseif ($isSelected && !$isCorrect) {
                                                    $optionClass = $option->is_correct ? 'correct' : 'incorrect selected';
                                                } elseif (!$isSelected && $option->is_correct) {
                                                    $optionClass = 'correct';
                                                }
                                            @endphp
                                            <div class="option {{ $optionClass }}">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="{{ $question->question_type === 'single_choice' ? 'radio' : 'checkbox' }}" 
                                                           {{ $isSelected ? 'checked' : '' }} disabled>
                                                    <label class="form-check-label">
                                                        {!! $option->option_text !!}
                                                        @if($option->is_correct)
                                                            <i class="fas fa-check-circle text-success ms-1"></i>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($question->question_type === 'true_false')
                                    <div class="options">
                                        @php
                                            $correctAnswer = $question->options->firstWhere('is_correct', true);
                                            $userSelected = $userAnswer ? $question->options->firstWhere('id', json_decode($userAnswer->selected_options, true)[0] ?? null) : null;
                                        @endphp
                                        
                                        <div class="option {{ $correctAnswer && $correctAnswer->option_text === 'True' ? 'correct' : '' }} {{ $userSelected && $userSelected->option_text === 'True' ? ($isCorrect ? 'selected' : 'incorrect') : '' }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" {{ $userSelected && $userSelected->option_text === 'True' ? 'checked' : '' }} disabled>
                                                <label class="form-check-label">
                                                    True
                                                    @if($correctAnswer && $correctAnswer->option_text === 'True')
                                                        <i class="fas fa-check-circle text-success ms-1"></i>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="option {{ $correctAnswer && $correctAnswer->option_text === 'False' ? 'correct' : '' }} {{ $userSelected && $userSelected->option_text === 'False' ? ($isCorrect ? 'selected' : 'incorrect') : '' }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" {{ $userSelected && $userSelected->option_text === 'False' ? 'checked' : '' }} disabled>
                                                <label class="form-check-label">
                                                    False
                                                    @if($correctAnswer && $correctAnswer->option_text === 'False')
                                                        <i class="fas fa-check-circle text-success ms-1"></i>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($question->question_type === 'short_answer' && $userAnswer)
                                    <div class="mt-3">
                                        <label class="form-label">User's Answer:</label>
                                        <div class="p-2 bg-light rounded">
                                            {{ $userAnswer->answer_text ?? 'No answer provided' }}
                                        </div>
                                        
                                        @if($question->correct_answer)
                                            <div class="mt-2">
                                                <label class="form-label">Correct Answer:</label>
                                                <div class="p-2 bg-light rounded">
                                                    {{ $question->correct_answer }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        No answer provided for this question.
                                    </div>
                                @endif
                                
                                @if($question->explanation)
                                    <div class="alert alert-light mt-3">
                                        <strong>Explanation:</strong> {!! $question->explanation !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const chartData = @json($chartData);
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: chartData.backgroundColor,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
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
    });
</script>
@endpush
