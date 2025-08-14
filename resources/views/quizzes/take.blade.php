@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Quiz Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">{{ $quiz->title }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}">Quizzes</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('quizzes.index', ['category' => $quiz->category->slug]) }}">{{ $quiz->category->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Take Quiz</li>
                        </ol>
                    </nav>
                </div>
                <div class="text-end">
                    <div id="quiz-timer" class="h4 mb-0 fw-bold text-danger" 
                         data-time-limit="{{ $quiz->time_limit * 60 }}"
                         data-start-time="{{ now()->timestamp }}">
                        @if($quiz->time_limit)
                            {{ sprintf('%02d:00', $quiz->time_limit) }}
                        @else
                            No Time Limit
                        @endif
                    </div>
                    <div class="text-muted small">Time Remaining</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress mb-4" style="height: 10px;">
                @php
                    $questionCount = $quiz->questions->count();
                    $currentQuestionIndex = $quiz->questions->search(fn($q) => $q->id === $currentQuestion->id) + 1;
                    $progress = ($currentQuestionIndex / $questionCount) * 100;
                @endphp
                <div class="progress-bar bg-primary" role="progressbar" 
                     style="width: {{ $progress }}%" 
                     aria-valuenow="{{ $progress }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <span class="visually-hidden">{{ $progress }}% Complete</span>
                </div>
            </div>
            <div class="d-flex justify-content-between mb-4">
                <div>
                    Question <span id="current-question">{{ $currentQuestionIndex }}</span> of {{ $questionCount }}
                </div>
                <div>
                    <span class="badge bg-light text-dark">
                        {{ $quiz->time_limit ? $quiz->time_limit . ' min' : 'No time limit' }}
                    </span>
                    <span class="badge bg-light text-dark">
                        {{ $questionCount }} Questions
                    </span>
                    @if($quiz->passing_score)
                        <span class="badge bg-light text-dark">
                            Passing Score: {{ $quiz->passing_score }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Question Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="quiz-form" action="{{ route('quizzes.submit', $quiz->slug) }}" method="POST">
                        @csrf
                        <input type="hidden" name="question_id" value="{{ $currentQuestion->id }}">
                        
                        <div class="question mb-4">
                            <h4 class="mb-4">{{ $currentQuestion->text }}</h4>
                            
                            @if($currentQuestion->image)
                                <div class="mb-4 text-center">
                                    <img src="{{ Storage::url($currentQuestion->image) }}" 
                                         alt="Question Image" 
                                         class="img-fluid rounded" 
                                         style="max-height: 300px;">
                                </div>
                            @endif
                            
                            @if($currentQuestion->explanation)
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ $currentQuestion->explanation }}
                                </div>
                            @endif
                            
                            <!-- Options -->
                            <div class="options mb-4">
                                @if($currentQuestion->type === 'multiple_choice')
                                    @foreach($currentQuestion->options as $option)
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="answer[]" 
                                                   value="{{ $option->id }}"
                                                   id="option-{{ $option->id }}"
                                                   {{ in_array($option->id, session('quiz_session.answers.' . $currentQuestion->id, [])) ? 'checked' : '' }}>
                                            <label class="form-check-label w-100" for="option-{{ $option->id }}">
                                                <span class="option-text">{{ $option->text }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                @elseif($currentQuestion->type === 'true_false')
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="answer[]" 
                                               value="1"
                                               id="option-true"
                                               {{ in_array('1', session('quiz_session.answers.' . $currentQuestion->id, [])) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="option-true">
                                            <i class="fas fa-check-circle text-success me-2"></i> True
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="answer[]" 
                                               value="0"
                                               id="option-false"
                                               {{ in_array('0', session('quiz_session.answers.' . $currentQuestion->id, [])) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="option-false">
                                            <i class="fas fa-times-circle text-danger me-2"></i> False
                                        </label>
                                    </div>
                                @elseif($currentQuestion->type === 'single_choice')
                                    @foreach($currentQuestion->options as $option)
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" 
                                                   type="radio" 
                                                   name="answer[]" 
                                                   value="{{ $option->id }}"
                                                   id="option-{{ $option->id }}"
                                                   {{ in_array($option->id, session('quiz_session.answers.' . $currentQuestion->id, [])) ? 'checked' : '' }}>
                                            <label class="form-check-label w-100" for="option-{{ $option->id }}">
                                                <span class="option-text">{{ $option->text }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            @if($currentQuestionIndex > 1)
                                <button type="button" class="btn btn-outline-secondary" id="prev-question">
                                    <i class="fas fa-arrow-left me-2"></i> Previous
                                </button>
                            @else
                                <div></div>
                            @endif
                            
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" id="skip-question">
                                    Skip <i class="fas fa-forward ms-2"></i>
                                </button>
                                
                                @if($currentQuestionIndex < $questionCount)
                                    <button type="button" class="btn btn-primary" id="next-question">
                                        Next <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success" id="finish-quiz">
                                        Finish <i class="fas fa-check ms-2"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quiz Navigation Dots -->
            <div class="d-flex flex-wrap gap-2 mb-4" id="question-dots">
                @foreach($quiz->questions as $index => $question)
                    <a href="#" 
                       class="question-dot d-flex align-items-center justify-content-center rounded-circle {{ $question->id === $currentQuestion->id ? 'bg-primary text-white' : (in_array($question->id, array_keys(session('quiz_session.answers', []))) ? 'bg-success text-white' : 'bg-light') }}"
                       data-question-id="{{ $question->id }}"
                       style="width: 40px; height: 40px; text-decoration: none;">
                        {{ $index + 1 }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Time's Up Modal -->
<div class="modal fade" id="timeUpModal" tabindex="-1" aria-labelledby="timeUpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="timeUpModalLabel">Time's Up!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-clock fa-4x text-danger mb-3"></i>
                    <h4>Your time is up!</h4>
                    <p class="mb-0">The quiz will be automatically submitted with your current answers.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit-quiz">Submit Quiz</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quizForm = document.getElementById('quiz-form');
        const nextBtn = document.getElementById('next-question');
        const prevBtn = document.getElementById('prev-question');
        const skipBtn = document.getElementById('skip-question');
        const finishBtn = document.getElementById('finish-quiz');
        const submitBtn = document.getElementById('submit-quiz');
        const questionDots = document.querySelectorAll('.question-dot');
        const timerElement = document.getElementById('quiz-timer');
        const timeLimit = parseInt(timerElement.dataset.timeLimit);
        const startTime = parseInt(timerElement.dataset.startTime);
        
        // Timer functionality
        let timeLeft = timeLimit;
        let timerInterval;
        
        function updateTimer() {
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                document.getElementById('timeUpModal').classList.add('show');
                document.getElementById('timeUpModal').style.display = 'block';
                document.body.classList.add('modal-open');
                return;
            }
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            timeLeft--;
        }
        
        // Only start timer if there's a time limit
        if (timeLimit > 0) {
            // Calculate time elapsed since start
            const now = Math.floor(Date.now() / 1000);
            const elapsed = now - startTime;
            timeLeft = Math.max(0, timeLimit - elapsed);
            
            // Update timer immediately and then every second
            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
        }
        
        // Navigation handlers
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                quizForm.submit();
            });
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                // Add a hidden field to indicate previous button was clicked
                const prevInput = document.createElement('input');
                prevInput.type = 'hidden';
                prevInput.name = 'previous';
                prevInput.value = '1';
                quizForm.appendChild(prevInput);
                quizForm.submit();
            });
        }
        
        if (skipBtn) {
            skipBtn.addEventListener('click', function() {
                const skipInput = document.createElement('input');
                skipInput.type = 'hidden';
                skipInput.name = 'skip';
                skipInput.value = '1';
                quizForm.appendChild(skipInput);
                quizForm.submit();
            });
        }
        
        if (finishBtn) {
            finishBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to finish the quiz? You cannot change your answers after submission.')) {
                    const finishInput = document.createElement('input');
                    finishInput.type = 'hidden';
                    finishInput.name = 'finish';
                    finishInput.value = '1';
                    quizForm.appendChild(finishInput);
                    quizForm.submit();
                }
            });
        }
        
        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                const finishInput = document.createElement('input');
                finishInput.type = 'hidden';
                finishInput.name = 'finish';
                finishInput.value = '1';
                quizForm.appendChild(finishInput);
                quizForm.submit();
            });
        }
        
        // Question dot navigation
        questionDots.forEach(dot => {
            dot.addEventListener('click', function(e) {
                e.preventDefault();
                const questionId = this.dataset.questionId;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'question_id';
                input.value = questionId;
                quizForm.appendChild(input);
                quizForm.submit();
            });
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            // Don't navigate if user is typing in an input field
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }
            
            // Left arrow for previous question
            if (e.key === 'ArrowLeft' && prevBtn) {
                prevBtn.click();
            }
            // Right arrow for next question
            else if ((e.key === 'ArrowRight' || e.key === ' ') && nextBtn) {
                nextBtn.click();
            }
            // Enter to submit form on last question
            else if (e.key === 'Enter' && finishBtn) {
                finishBtn.click();
            }
            // Number keys to jump to specific question
            else if (e.key >= 1 && e.key <= 9) {
                const index = parseInt(e.key) - 1;
                if (index < questionDots.length) {
                    questionDots[index].click();
                }
            }
        });
        
        // Warn before leaving the page
        window.addEventListener('beforeunload', function(e) {
            // Only show warning if the quiz is in progress
            if (timeLeft > 0 && timeLeft < timeLimit) {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your progress will be lost.';
                return e.returnValue;
            }
        });
    });
</script>

<style>
    .question-dot {
        transition: all 0.2s ease;
    }
    .question-dot:hover {
        transform: scale(1.1);
    }
    .form-check-input:checked + .form-check-label {
        font-weight: 600;
    }
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.3s ease;
    }
    .breadcrumb {
        background: none;
        padding: 0.5rem 0;
    }
    .breadcrumb-item + .breadcrumb-item::before {
        content: '›';
    }
</style>
@endpush
@endsection
