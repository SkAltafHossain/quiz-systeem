<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class QuestionController extends Controller
{
    /**
     * Display a listing of questions for a quiz.
     */
    public function index(Request $request, $quizId)
    {
        if ($request->ajax()) {
            $questions = Question::with('options')
                ->where('quiz_id', $quizId)
                ->latest()
                ->get();

            return DataTables::of($questions)
                ->addIndexColumn()
                ->addColumn('question_text', function($question) {
                    return Str::limit($question->question_text, 100);
                })
                ->addColumn('type', function($question) {
                    return ucfirst($question->type);
                })
                ->addColumn('points', function($question) {
                    return $question->points;
                })
                ->addColumn('options_count', function($question) {
                    return $question->options->count();
                })
                ->addColumn('actions', function($question) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-primary edit-question-btn" data-id="' . $question->id . '" data-quiz="' . $question->quiz_id . '">';
                    $btn .= '<i class="fas fa-edit"></i> Edit';
                    $btn .= '</button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-danger delete-question-btn ms-2" data-id="' . $question->id . '">';
                    $btn .= '<i class="fas fa-trash"></i> Delete';
                    $btn .= '</button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $quiz = Quiz::findOrFail($quizId);
        return view('admin.questions.index', compact('quiz'));
    }

    /**
     * Get question data for editing.
     */
    public function getQuestion($quizId, $questionId)
    {
        $question = Question::with('options')
            ->where('quiz_id', $quizId)
            ->findOrFail($questionId);
            
        return response()->json([
            'success' => true,
            'data' => $question
        ]);
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request, $quizId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|max:1000',
            'type' => 'required|in:multiple_choice,single_choice,true_false,short_answer',
            'points' => 'required|integer|min:1',
            'explanation' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'options' => 'required_if:type,multiple_choice,single_choice,true_false|array|min:2',
            'options.*.option_text' => 'required_with:options|string|max:500',
            'options.*.is_correct' => 'required_with:options|boolean',
            'options.*.explanation' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Create the question
            $question = Question::create([
                'quiz_id' => $quizId,
                'question_text' => $validated['question_text'],
                'type' => $validated['type'],
                'points' => $validated['points'],
                'explanation' => $validated['explanation'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Create options if this is a multiple/single choice or true/false question
            if (in_array($validated['type'], ['multiple_choice', 'single_choice', 'true_false']) && !empty($validated['options'])) {
                foreach ($validated['options'] as $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'explanation' => $optionData['explanation'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully',
                'data' => $question->load('options')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create question: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, $quizId, $questionId)
    {
        $question = Question::where('quiz_id', $quizId)->findOrFail($questionId);
        
        $validated = $request->validate([
            'question_text' => 'required|string|max:1000',
            'type' => 'required|in:multiple_choice,single_choice,true_false,short_answer',
            'points' => 'required|integer|min:1',
            'explanation' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'options' => 'required_if:type,multiple_choice,single_choice,true_false|array|min:2',
            'options.*.id' => 'sometimes|exists:options,id',
            'options.*.option_text' => 'required_with:options|string|max:500',
            'options.*.is_correct' => 'required_with:options|boolean',
            'options.*.explanation' => 'nullable|string|max:500',
            'deleted_options' => 'sometimes|array',
            'deleted_options.*' => 'exists:options,id'
        ]);

        try {
            DB::beginTransaction();

            // Update the question
            $question->update([
                'question_text' => $validated['question_text'],
                'type' => $validated['type'],
                'points' => $validated['points'],
                'explanation' => $validated['explanation'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Handle options update
            if (in_array($validated['type'], ['multiple_choice', 'single_choice', 'true_false'])) {
                // Delete removed options
                if (!empty($validated['deleted_options'])) {
                    Option::whereIn('id', $validated['deleted_options'])->delete();
                }

                // Update or create options
                if (!empty($validated['options'])) {
                    foreach ($validated['options'] as $optionData) {
                        if (isset($optionData['id'])) {
                            // Update existing option
                            $option = $question->options()->find($optionData['id']);
                            if ($option) {
                                $option->update([
                                    'option_text' => $optionData['option_text'],
                                    'is_correct' => $optionData['is_correct'] ?? false,
                                    'explanation' => $optionData['explanation'] ?? null,
                                ]);
                            }
                        } else {
                            // Create new option
                            $question->options()->create([
                                'option_text' => $optionData['option_text'],
                                'is_correct' => $optionData['is_correct'] ?? false,
                                'explanation' => $optionData['explanation'] ?? null,
                            ]);
                        }
                    }
                }
            } else {
                // For short answer questions, remove all options
                $question->options()->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully',
                'data' => $question->load('options')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update question: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy($quizId, $questionId)
    {
        try {
            $question = Question::where('quiz_id', $quizId)->findOrFail($questionId);
            
            // Delete related options first
            $question->options()->delete();
            
            // Then delete the question
            $question->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle question active status.
     */
    public function toggleStatus($quizId, $questionId)
    {
        try {
            $question = Question::where('quiz_id', $quizId)->findOrFail($questionId);
            $question->update([
                'is_active' => !$question->is_active
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Question status updated successfully',
                'is_active' => $question->fresh()->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update question status: ' . $e->getMessage()
            ], 500);
        }
    }
}
