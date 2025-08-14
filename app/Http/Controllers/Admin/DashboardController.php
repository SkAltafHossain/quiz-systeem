<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with analytics.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $attemptsData = $this->getAttemptsData();
        $quizzesByCategory = $this->getQuizzesByCategory();
        $recentUsers = $this->getRecentUsers();
        $recentResults = $this->getRecentResults();
        $topQuizzes = $this->getTopQuizzes();

        return view('admin.dashboard', [
            'stats' => $stats,
            'attemptsData' => $attemptsData,
            'quizzesByCategory' => $quizzesByCategory,
            'recentUsers' => $recentUsers,
            'recentResults' => $recentResults,
            'topQuizzes' => $topQuizzes,
        ]);
    }

    /**
     * Get dashboard statistics.
     *
     * @return array
     */
    protected function getDashboardStats()
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        
        return [
            'total_users' => User::count(),
            'total_quizzes' => Quiz::count(),
            'total_questions' => Question::count(),
            'total_attempts' => Result::count(),
            'active_users' => User::where('last_login_at', '>=', $now->subDays(30))->count(),
            'avg_score' => Result::avg('score') ? round(Result::avg('score'), 1) : 0,
            'completion_rate' => Result::count() > 0 
                ? round((Result::where('completed', true)->count() / Result::count()) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get quiz attempts data for the last 30 days.
     *
     * @return array
     */
    protected function getAttemptsData()
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(29);
        
        $attempts = Result::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with 0
        $attemptsData = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $date = $currentDate->format('Y-m-d');
            $attempt = $attempts->firstWhere('date', $date);
            
            $attemptsData[] = [
                'date' => $currentDate->format('M j'),
                'count' => $attempt ? $attempt->count : 0,
            ];
            
            $currentDate->addDay();
        }

        return $attemptsData;
    }

    /**
     * Get quizzes grouped by category.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getQuizzesByCategory()
    {
        return Quiz::with('category')
            ->select('category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->category ? $item->category->name : 'Uncategorized',
                    'count' => $item->count,
                ];
            });
    }

    /**
     * Get recently registered users.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRecentUsers()
    {
        return User::latest()
            ->take(5)
            ->get();
    }

    /**
     * Get recent quiz results.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRecentResults()
    {
        return Result::with(['user', 'quiz'])
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * Get top quizzes by number of attempts.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getTopQuizzes()
    {
        return Quiz::withCount('results')
            ->orderBy('results_count', 'desc')
            ->take(5)
            ->get();
    }
}
