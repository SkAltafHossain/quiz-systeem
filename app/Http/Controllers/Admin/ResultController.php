<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ResultController extends Controller
{
    /**
     * Display a listing of quiz results.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Result::with(['user', 'quiz'])
                ->select('results.*')
                ->latest('completed_at');

            // Filter by quiz if specified
            if ($request->has('quiz_id') && $request->quiz_id) {
                $query->where('quiz_id', $request->quiz_id);
            }

            // Filter by user if specified
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by date range if specified
            if ($request->has('date_range') && $request->date_range) {
                $dates = explode(' to ', $request->date_range);
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = isset($dates[1]) 
                    ? Carbon::parse($dates[1])->endOfDay() 
                    : Carbon::parse($dates[0])->endOfDay();
                
                $query->whereBetween('completed_at', [$startDate, $endDate]);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function($result) {
                    return $result->user ? $result->user->name : 'N/A';
                })
                ->addColumn('quiz_title', function($result) {
                    return $result->quiz ? $result->quiz->title : 'N/A';
                })
                ->addColumn('score_display', function($result) {
                    $percentage = $result->score > 0 ? round(($result->score / $result->max_score) * 100) : 0;
                    $badgeClass = $percentage >= $result->quiz->passing_score ? 'bg-success' : 'bg-danger';
                    return "<span class='badge {$badgeClass}'>{$result->score}/{$result->max_score} ({$percentage}%)</span>";
                })
                ->addColumn('status_badge', function($result) {
                    $status = $result->is_passed ? 'Passed' : 'Failed';
                    $badgeClass = $result->is_passed ? 'bg-success' : 'bg-danger';
                    return "<span class='badge {$badgeClass}'>{$status}</span>";
                })
                ->addColumn('completion_time', function($result) {
                    return $result->completed_at ? $result->completed_at->format('M d, Y h:i A') : 'N/A';
                })
                ->addColumn('actions', function($result) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.results.show', $result->id) . '" class="btn btn-sm btn-outline-primary">';
                    $btn .= '<i class="fas fa-eye"></i> View';
                    $btn .= '</a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-danger ms-2 delete-result-btn" data-id="' . $result->id . '">';
                    $btn .= '<i class="fas fa-trash"></i>';
                    $btn .= '</button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['score_display', 'status_badge', 'actions'])
                ->make(true);
        }

        // Get quizzes and users for filters
        $quizzes = Quiz::orderBy('title')->pluck('title', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('admin.results.index', compact('quizzes', 'users'));
    }

    /**
     * Display the specified result.
     */
    public function show($id)
    {
        $result = Result::with(['user', 'quiz.questions.options', 'answers'])->findOrFail($id);
        
        // Calculate statistics
        $totalQuestions = $result->quiz->questions->count();
        $correctAnswers = $result->answers->where('is_correct', true)->count();
        $incorrectAnswers = $result->answers->where('is_correct', false)->count();
        $unanswered = $totalQuestions - $result->answers->count();
        
        // Prepare data for the chart
        $chartData = [
            'labels' => ['Correct', 'Incorrect', 'Unanswered'],
            'data' => [
                $correctAnswers,
                $incorrectAnswers,
                $unanswered
            ],
            'backgroundColor' => [
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(108, 117, 125, 0.8)'
            ]
        ];

        return view('admin.results.show', compact('result', 'chartData'));
    }

    /**
     * Remove the specified result from storage.
     */
    public function destroy($id)
    {
        try {
            $result = Result::findOrFail($id);
            $result->answers()->delete();
            $result->delete();

            return response()->json([
                'success' => true,
                'message' => 'Result deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete result: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export results to CSV
     */
    public function export(Request $request)
    {
        $query = Result::with(['user', 'quiz']);

        // Apply filters
        if ($request->has('quiz_id') && $request->quiz_id) {
            $query->where('quiz_id', $request->quiz_id);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_range') && $request->date_range) {
            $dates = explode(' to ', $request->date_range);
            $startDate = Carbon::parse($dates[0])->startOfDay();
            $endDate = isset($dates[1]) 
                ? Carbon::parse($dates[1])->endOfDay() 
                : Carbon::parse($dates[0])->endOfDay();
            
            $query->whereBetween('completed_at', [$startDate, $endDate]);
        }

        $results = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quiz_results_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($results) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID', 'User', 'Quiz', 'Score', 'Max Score', 'Percentage', 'Status', 
                'Time Spent', 'Completed At', 'IP Address'
            ]);
            
            // Add data rows
            foreach ($results as $result) {
                $percentage = $result->score > 0 ? round(($result->score / $result->max_score) * 100) : 0;
                
                fputcsv($file, [
                    $result->id,
                    $result->user ? $result->user->name : 'N/A',
                    $result->quiz ? $result->quiz->title : 'N/A',
                    $result->score,
                    $result->max_score,
                    $percentage . '%',
                    $result->is_passed ? 'Passed' : 'Failed',
                    gmdate('H:i:s', $result->time_spent),
                    $result->completed_at ? $result->completed_at->format('Y-m-d H:i:s') : 'N/A',
                    $result->ip_address
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
