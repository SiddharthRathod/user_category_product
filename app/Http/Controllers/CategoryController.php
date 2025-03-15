<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('childrenRecursive')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('categories.create', compact('categories'));
    }

    public function store(CategoryRequest $request)
    {
        
        Category::create($request->all());

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('categories.edit', compact('category', 'categories'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:active,inactive',
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Check if the category has any subcategories
        if ($category->children()->count() > 0) {
            return redirect()->route('categories.index')->with('error', 'Category has subcategories and cannot be deleted.');
        }

        // Check if the category has any associated products
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')->with('error', 'Category has associated products and cannot be deleted.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function getSubcategories($parent_id)
    {
        $subcategories = Category::where('parent_id', $parent_id)->get();
        return response()->json($subcategories);
    }
}
