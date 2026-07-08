<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        Gate::authorize('manage categories');

        $categories = Category::withCount('campaigns')->latest()->get();

        return Inertia::render('admin/categories', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('manage categories');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('flash', ['success' => 'Category created.']);
    }

    public function update(Request $request, Category $category)
    {
        Gate::authorize('manage categories');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$category->id],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('flash', ['success' => 'Category updated.']);
    }

    public function destroy(Category $category)
    {
        Gate::authorize('manage categories');

        if ($category->campaigns()->exists()) {
            return back()->with('flash', ['error' => 'Cannot delete category with active campaigns.']);
        }

        $category->delete();

        return back()->with('flash', ['success' => 'Category deleted.']);
    }
}
