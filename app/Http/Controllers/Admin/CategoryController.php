<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getCategoriesDataTable($request);
        }
        
        return view('admin.categories.index');
    }

    /**
     * Return JSON data for DataTables
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function getCategoriesDataTable(Request $request)
    {
        $query = Category::withCount('quizzes');
        
        // Apply search filter
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status);
        }
        
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('status', function($category) {
                return $category->is_active 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('quizzes_count', function($category) {
                return $category->quizzes_count ?? 0;
            })
            ->addColumn('created_at_formatted', function($category) {
                return $category->created_at->format('M d, Y');
            })
            ->addColumn('actions', function($category) {
                return view('admin.components.action-buttons', [
                    'editRoute' => route('admin.categories.edit', $category->id),
                    'deleteRoute' => route('admin.categories.destroy', $category->id),
                    'deleteClass' => 'delete-category-btn',
                    'model' => $category
                ])->render();
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.categories.create');
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
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        // Generate slug from name
        $validated['slug'] = $this->createSlug($request->name);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }
        
        $category = Category::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully!',
            'redirect' => route('admin.categories.index')
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($category->id)
            ],
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        // Update slug if name has changed
        if ($category->name !== $request->name) {
            $validated['slug'] = $this->createSlug($request->name, $category->id);
        }
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                \Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        } elseif ($request->has('remove_image') && $request->remove_image) {
            // Remove image if remove_image flag is set
            if ($category->image) {
                \Storage::disk('public')->delete($category->image);
                $validated['image'] = null;
            }
        }
        
        $category->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully!',
            'redirect' => route('admin.categories.index')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        // Check if category has associated quizzes
        if ($category->quizzes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated quizzes.',
            ], 422);
        }
        
        // Delete image if exists
        if ($category->image) {
            \Storage::disk('public')->delete($category->image);
        }
        
        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully!',
        ]);
    }
    
    /**
     * Toggle category status
     * 
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(Category $category)
    {
        $category->update([
            'is_active' => !$category->is_active
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Category status updated!',
            'is_active' => $category->is_active
        ]);
    }
    
    /**
     * Create a slug from the given string
     * 
     * @param  string  $name
     * @param  int  $id
     * @return string
     */
    private function createSlug($name, $id = 0)
    {
        $slug = Str::slug($name);
        $count = Category::where('slug', 'LIKE', $slug . '%')
            ->where('id', '!=', $id)
            ->count();
            
        return $count ? $slug . '-' . ($count + 1) : $slug;
    }
}
