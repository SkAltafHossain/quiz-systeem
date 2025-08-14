<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getQuizzesDataTable($request);
        }
        
        $categories = Category::active()->pluck('name', 'id');
        return view('admin.quizzes.index', compact('categories'));
    }

    /**
     * Return JSON data for DataTables
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function getQuizzesDataTable(Request $request)
    {
        $query = Quiz::with(['category', 'questions']);
        
        // Apply search filter
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply category filter
        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }
        
        // Apply status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_published', $request->status === 'published');
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('category_name', function($quiz) {
                return $quiz->category->name;
            })
            ->addColumn('questions_count', function($quiz) {
                return $quiz->questions_count ?? 0;
            })
            ->addColumn('status', function($quiz) {
                $status = $quiz->is_published ? 'success' : 'warning';
                $label = $quiz->is_published ? 'Published' : 'Draft';
                return "<span class='badge bg-{$status}'>{$label}</span>";
            })
            ->addColumn('created_at_formatted', function($quiz) {
                return $quiz->created_at->format('M d, Y');
            })
            ->addColumn('actions', function($quiz) {
                return view('admin.components.action-buttons', [
                    'editRoute' => route('admin.quizzes.edit', $quiz->id),
                    'deleteRoute' => route('admin.quizzes.destroy', $quiz->id),
                    'deleteClass' => 'delete-quiz-btn',
                    'model' => $quiz,
                    'showView' => true,
                    'viewRoute' => route('quizzes.show', $quiz->slug)
                ])->render();
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    /**
     * Get categories for the create/edit forms
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        $categories = Category::active()->pluck('name', 'id');
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:quizzes',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|integer|min:1|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after:started_at',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        // Generate slug from title
        $validated['slug'] = $this->createSlug($request->title);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('quizzes', 'public');
        }
        
        // Format dates
        if ($request->has('started_at')) {
            $validated['started_at'] = Carbon::parse($request->started_at);
        }
        
        if ($request->has('ended_at')) {
            $validated['ended_at'] = Carbon::parse($request->ended_at);
        }
        
        $quiz = Quiz::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Quiz created successfully!',
            'data' => $quiz
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function show(Quiz $quiz)
    {
        $quiz->load(['category', 'questions.options', 'results' => function($query) {
            $query->latest()->take(10);
        }]);
        
        return view('admin.quizzes.show', compact('quiz'));
    }

    /**
     * Get the specified quiz data for editing.
     *
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuiz(Quiz $quiz)
    {
        $quiz->load('category');
        $quiz->image_url = $quiz->image ? asset('storage/' . $quiz->image) : null;
        
        return response()->json([
            'success' => true,
            'data' => $quiz
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quizzes')->ignore($quiz->id)
            ],
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|integer|min:1|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after:started_at',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        // Update slug if title has changed
        if ($quiz->title !== $request->title) {
            $validated['slug'] = $this->createSlug($request->title, $quiz->id);
        }
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($quiz->image) {
                \Storage::disk('public')->delete($quiz->image);
            }
            $validated['image'] = $request->file('image')->store('quizzes', 'public');
        } elseif ($request->has('remove_image') && $request->remove_image) {
            // Remove image if remove_image flag is set
            if ($quiz->image) {
                \Storage::disk('public')->delete($quiz->image);
                $validated['image'] = null;
            }
        }
        
        // Format dates
        if ($request->has('started_at')) {
            $validated['started_at'] = Carbon::parse($request->started_at);
        } else {
            $validated['started_at'] = null;
        }
        
        if ($request->has('ended_at')) {
            $validated['ended_at'] = Carbon::parse($request->ended_at);
        } else {
            $validated['ended_at'] = null;
        }
        
        $quiz->update($validated);
        
        // Handle image removal if requested
        if ($request->has('remove_image') && $request->remove_image && $quiz->image) {
            Storage::disk('public')->delete($quiz->image);
            $quiz->update(['image' => null]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Quiz updated successfully!',
            'data' => $quiz
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function destroy(Quiz $quiz)
    {
        // Check if quiz has associated questions
        if ($quiz->questions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete quiz with associated questions. Please delete the questions first.',
            ], 422);
        }
        
        // Check if quiz has associated results
        if ($quiz->results()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete quiz with associated results. Please delete the results first.',
            ], 422);
        }
        
        // Delete image if exists
        if ($quiz->image) {
            \Storage::disk('public')->delete($quiz->image);
        }
        
        $quiz->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Quiz deleted successfully!',
        ]);
    }
    
    /**
     * Toggle quiz published status
     * 
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Quiz $quiz)
    {
        $quiz->update([
            'is_published' => !$quiz->is_published
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Quiz status updated!',
            'data' => [
                'is_published' => $quiz->is_published
            ]
        ]);
    }
    
    /**
     * Create a slug from the given string
     * 
     * @param  string  $title
     * @param  int  $id
     * @return string
     */
    private function createSlug($title, $id = 0)
    {
        $slug = Str::slug($title);
        $count = Quiz::where('slug', 'LIKE', $slug . '%')
            ->where('id', '!=', $id)
            ->count();
            
        return $count ? $slug . '-' . ($count + 1) : $slug;
    }
}
