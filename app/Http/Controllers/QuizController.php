<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Display a listing of quizzes with optional category filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['category', 'results' => function($q) {
            $q->where('user_id', auth()->id());
        }])
        ->published()
        ->withCount(['questions as question_count']);

        // Filter by category if specified
        if ($request->has('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'title':
                $query->orderBy('title');
                break;
            case 'popular':
                $query->withCount('results as attempts')
                      ->orderBy('attempts', 'desc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $quizzes = $query->paginate(12)
                        ->withQueryString();

        // Get all categories for the filter sidebar
        $categories = Category::withCount(['quizzes' => function($q) {
            $q->published();
        }])->orderBy('name')->get();

        // Get popular quizzes for sidebar
        $popularQuizzes = Quiz::published()
            ->withCount('results')
            ->orderBy('results_count', 'desc')
            ->take(5)
            ->get();

        return view('quizzes.index', compact('quizzes', 'categories', 'popularQuizzes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified quiz with details and start button.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $slug)
    {
        $quiz = Quiz::with(['category', 'questions' => function($q) {
            $q->orderBy('order');
        }])
        ->withCount('questions')
        ->published()
        ->where('slug', $slug)
        ->firstOrFail();

        // Check if user has already taken this quiz
        $previousAttempt = auth()->check() 
            ? auth()->user()->results()
                ->where('quiz_id', $quiz->id)
                ->latest()
                ->first()
            : null;

        // Get related quizzes
        $relatedQuizzes = Quiz::where('category_id', $quiz->category_id)
            ->where('id', '!=', $quiz->id)
            ->published()
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('quizzes.show', compact('quiz', 'previousAttempt', 'relatedQuizzes'));
    }
    
    /**
     * Display the quiz taking interface.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function take(string $slug)
    {
        $quiz = Quiz::with(['questions' => function($query) {
            $query->with(['options' => function($q) {
                $q->inRandomOrder();
            }])->orderBy('order');
        }])
        ->published()
        ->where('slug', $slug)
        ->firstOrFail();

        // Check if user can take the quiz
        $lastAttempt = auth()->user()->results()
            ->where('quiz_id', $quiz->id)
            ->latest()
            ->first();
            
        if ($lastAttempt && $quiz->retake_after) {
            $retakeTime = $lastAttempt->created_at->addHours($quiz->retake_after);
            if ($retakeTime->isFuture()) {
                return redirect()->route('quizzes.show', $quiz->slug)
                    ->with('error', 'You can retake this quiz after ' . $retakeTime->diffForHumans());
            }
        }

        // Get the first question
        $firstQuestion = $quiz->questions->first();
        if (!$firstQuestion) {
            return redirect()->route('quizzes.show', $quiz->slug)
                ->with('error', 'This quiz has no questions yet.');
        }

        // Start or resume quiz session
        if (!session()->has('quiz_session')) {
            session([
                'quiz_session' => [
                    'quiz_id' => $quiz->id,
                    'start_time' => now(),
                    'time_limit' => $quiz->time_limit ? $quiz->time_limit * 60 : null, // in seconds
                    'answers' => [],
                    'current_question' => $firstQuestion->id
                ]
            ]);
        }

        // Get current question with options
        $currentQuestion = $quiz->questions
            ->where('id', session('quiz_session.current_question'))
            ->first() ?? $firstQuestion;

        return view('quizzes.take', compact('quiz', 'currentQuestion'));
    }
    
    /**
     * Handle quiz submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request, string $slug)
    {
        $quiz = Quiz::with('questions.options')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
            
        if (!session()->has('quiz_session')) {
            return redirect()->route('quizzes.show', $quiz->slug)
                ->with('error', 'No active quiz session found.');
        }

        $session = session('quiz_session');
        
        // Validate the request
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required_without:skip|array',
            'answer.*' => 'exists:options,id',
            'skip' => 'boolean',
            'finish' => 'boolean'
        ]);
        
        // Store the answer
        if (!isset($validated['skip'])) {
            $session['answers'][$validated['question_id']] = $validated['answer'] ?? [];
        }
        
        // Move to next question or finish quiz
        if (isset($validated['finish'])) {
            return $this->finishQuiz($quiz);
        }
        
        // Get next question
        $currentQuestion = $quiz->questions->firstWhere('id', $validated['question_id']);
        $nextQuestion = $quiz->questions->firstWhere('order', '>', $currentQuestion->order);
        
        if ($nextQuestion) {
            $session['current_question'] = $nextQuestion->id;
            session(['quiz_session' => $session]);
            return redirect()->route('quizzes.take', $quiz->slug);
        }
        
        // If no more questions, finish the quiz
        return $this->finishQuiz($quiz);
    }
    
    /**
     * Finish the quiz and show results.
     *
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function finishQuiz(Quiz $quiz)
    {
        $session = session('quiz_session');
        $questions = $quiz->questions;
        $score = 0;
        $totalQuestions = $questions->count();
        
        // Calculate score
        foreach ($questions as $question) {
            $userAnswers = $session['answers'][$question->id] ?? [];
            $correctAnswers = $question->options()->where('is_correct', true)->pluck('id')->toArray();
            
            if (!array_diff($userAnswers, $correctAnswers) && !array_diff($correctAnswers, $userAnswers)) {
                $score++;
            }
        }
        
        // Calculate percentage
        $percentage = $totalQuestions > 0 ? round(($score / $totalQuestions) * 100) : 0;
        $passed = $percentage >= $quiz->passing_score;
        
        // Store result
        $result = auth()->user()->results()->create([
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'percentage' => $percentage,
            'passed' => $passed,
            'time_taken' => now()->diffInSeconds(session('quiz_session.start_time')),
            'answers' => $session['answers']
        ]);
        
        // Clear session
        session()->forget('quiz_session');
        
        // Redirect to results page
        return redirect()->route('quizzes.results', [$quiz->slug, $result->id])
            ->with('success', 'Quiz submitted successfully!');
    }
    
    /**
     * Display quiz results.
     *
     * @param  string  $slug
     * @param  \App\Models\Result  $result
     * @return \Illuminate\View\View
     */
    public function results(string $slug, Result $result)
    {
        $quiz = Quiz::with(['questions.options'])
            ->where('slug', $slug)
            ->firstOrFail();
            
        // Verify result belongs to the authenticated user and quiz
        if ($result->user_id !== auth()->id() || $result->quiz_id !== $quiz->id) {
            abort(403);
        }
        
        // Get user's answers
        $userAnswers = $result->answers;
        
        return view('quizzes.results', compact('quiz', 'result', 'userAnswers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
