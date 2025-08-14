<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserQuizController extends Controller
{
    /**
     * Display the authenticated user's quiz history.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get user's quiz attempts with pagination
        $attempts = $user->results()
            ->with('quiz.category')
            ->latest()
            ->paginate(10);
            
        // Get user's stats
        $stats = [
            'total_quizzes' => $user->results()->count(),
            'average_score' => $user->results()->avg('percentage') ?? 0,
            'quizzes_taken' => $user->results()->distinct('quiz_id')->count(),
            'passed_quizzes' => $user->results()->where('passed', true)->count(),
        ];
        
        // Get recent activity
        $recentActivity = $user->results()
            ->with('quiz')
            ->latest()
            ->take(5)
            ->get();
            
        return view('user.quiz-history', compact('attempts', 'stats', 'recentActivity'));
    }
    
    /**
     * Display the leaderboard.
     *
     * @return \Illuminate\View\View
     */
    public function leaderboard()
    {
        // Overall leaderboard (top 50)
        $leaderboard = User::select('users.id', 'users.name', 'users.avatar')
            ->join('results', 'users.id', '=', 'results.user_id')
            ->selectRaw('users.id, users.name, users.avatar, COUNT(results.id) as quiz_count, AVG(results.percentage) as avg_score, MAX(results.percentage) as highest_score')
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->having('quiz_count', '>=', 3) // Only include users who have taken at least 3 quizzes
            ->orderBy('avg_score', 'desc')
            ->orderBy('quiz_count', 'desc')
            ->limit(50)
            ->get();
            
        // Current user's position
        $currentUser = null;
        if (auth()->check()) {
            $currentUser = User::select('users.id', 'users.name', 'users.avatar')
                ->leftJoin('results', 'users.id', '=', 'results.user_id')
                ->selectRaw('users.id, users.name, users.avatar, COUNT(results.id) as quiz_count, AVG(results.percentage) as avg_score, MAX(results.percentage) as highest_score')
                ->where('users.id', auth()->id())
                ->groupBy('users.id', 'users.name', 'users.avatar')
                ->first();
                
            if ($currentUser && $currentUser->quiz_count > 0) {
                $currentUser->position = User::select(DB::raw('COUNT(*) as position'))
                    ->join('results as r1', 'users.id', '=', 'r1.user_id')
                    ->groupBy('users.id')
                    ->havingRaw('AVG(r1.percentage) > ?', [$currentUser->avg_score])
                    ->count() + 1;
            }
        }
        
        // Recent high scores
        $recentHighScores = Result::with(['user', 'quiz'])
            ->where('percentage', '>=', 90) // Only show high scores (90% or above)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        // Top quizzes (by average score with minimum attempts)
        $topQuizzes = Quiz::select('quizzes.*', DB::raw('AVG(results.percentage) as avg_score'))
            ->join('results', 'quizzes.id', '=', 'results.quiz_id')
            ->groupBy('quizzes.id')
            ->havingRaw('COUNT(results.id) >= 5') // Only include quizzes with at least 5 attempts
            ->orderBy('avg_score', 'desc')
            ->take(5)
            ->get();
            
        return view('user.leaderboard', compact('leaderboard', 'currentUser', 'recentHighScores', 'topQuizzes'));
    }
    
    /**
     * Display quiz history for a specific quiz.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function quizHistory($slug)
    {
        $quiz = Quiz::where('slug', $slug)->firstOrFail();
        $user = auth()->user();
        
        // Get user's attempts for this quiz
        $attempts = $user->results()
            ->where('quiz_id', $quiz->id)
            ->latest()
            ->paginate(10);
            
        if ($attempts->isEmpty()) {
            return redirect()->route('quizzes.show', $quiz->slug)
                ->with('info', 'You have not taken this quiz yet.');
        }
        
        // Get user's stats for this quiz
        $stats = [
            'attempts' => $attempts->total(),
            'highest_score' => $attempts->max('percentage'),
            'average_score' => $attempts->avg('percentage'),
            'last_attempt' => $attempts->first()->created_at,
            'passed' => $attempts->where('passed', true)->count(),
            'failed' => $attempts->where('passed', false)->count(),
        ];
        
        // Get quiz leaderboard
        $quizLeaderboard = Result::with('user')
            ->where('quiz_id', $quiz->id)
            ->select('user_id', DB::raw('MAX(percentage) as max_score'), DB::raw('MIN(time_taken) as best_time'))
            ->groupBy('user_id')
            ->orderBy('max_score', 'desc')
            ->orderBy('best_time')
            ->limit(10)
            ->get();
            
        // Format time taken for display
        $quizLeaderboard->each(function($item) {
            $item->time_taken_formatted = gmdate('i\m s\s', $item->best_time);
            return $item;
        });
        
        return view('user.quiz-attempts', compact('quiz', 'attempts', 'stats', 'quizLeaderboard'));
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
     * Remove the specified quiz attempt.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = Result::findOrFail($id);
        
        // Authorize the action
        $this->authorize('delete', $result);
        
        // Delete the result
        $result->delete();
        
        return redirect()->back()
            ->with('success', 'Quiz attempt deleted successfully.');
    }
    
    // The following methods are part of the resource controller but not used in our application
    public function show(string $id) { abort(404); }
    public function edit(string $id) { abort(404); }
    public function update(Request $request, string $id) { abort(404); }
}
