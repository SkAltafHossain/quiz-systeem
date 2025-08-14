<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Category;
use App\Models\Question;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_quizzes' => Quiz::count(),
            'total_questions' => Question::count(),
            'total_attempts' => Result::count(),
            'active_users' => User::where('last_seen', '>=', now()->subMinutes(5))->count(),
        ];

        // Get recent users
        $recentUsers = User::latest()->take(5)->get();

        // Get recent quizzes
        $recentQuizzes = Quiz::with('category')
            ->latest()
            ->take(5)
            ->get();

        // Get recent results
        $recentResults = Result::with(['user', 'quiz'])
            ->latest()
            ->take(5)
            ->get();

        // Get quiz attempts per day for the last 30 days
        $quizAttemptsData = Result::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $attemptsDates = $quizAttemptsData->pluck('date');
        $attemptsCount = $quizAttemptsData->pluck('total');

        // Get quizzes by category for pie chart
        $quizzesByCategory = Quiz::select(
                'categories.name as category_name',
                DB::raw('count(quizzes.id) as total')
            )
            ->join('categories', 'quizzes.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->get();

        $categoryNames = $quizzesByCategory->pluck('category_name');
        $categoryCounts = $quizzesByCategory->pluck('total');

        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentQuizzes',
            'recentResults',
            'attemptsDates',
            'attemptsCount',
            'categoryNames',
            'categoryCounts'
        ));
    }
}
