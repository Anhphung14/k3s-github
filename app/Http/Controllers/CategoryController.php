<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;


class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $paginate = $request->input('paginate', 10);

        $query = \App\Models\Category::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = Category::paginate($paginate)->appends($request->query());

        $categories->getCollection()->transform(function ($category) {
            return [
                'id' => $category->id, 
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ];
        });

        return Inertia::render('categories/Index', [
            'categoryData' => $categories,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('categories/Form', [
            'category_detail' => null,
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
        ]);
        Category::create($data);
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function show($id)
    {
        return response()->json(Category::findOrFail($id));
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return Inertia::render('categories/Form', [
            'category_detail' => $category,
            'categories' => Category::where('id', '!=', $id)->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
        ]);
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:categories,id',
        ]);
        \App\Models\Category::whereIn('id', $data['ids'])->delete();
        return redirect()->route('categories.index')->with('success', 'Selected categories deleted.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:categories,id',
            'status' => 'required|in:draft,published',
        ]);
        \App\Models\Category::whereIn('id', $data['ids'])->update(['status' => $data['status']]);
        return redirect()->route('categories.index')->with('success', 'Status updated for selected categories.');
    }
}
