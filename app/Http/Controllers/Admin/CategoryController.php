<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    /**
     * Display and filter categories.
     */
    public function index(Request $request): View
    {
        $totalCategories = Category::count();
        $activeCategories = Category::where('is_active', true)->count();
        $inactiveCategories = Category::where('is_active', false)->count();
        $totalProducts = Product::count();

        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $categories = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact(
            'categories',
            'totalCategories',
            'activeCategories',
            'inactiveCategories',
            'totalProducts'
        ));
    }

    /**
     * Show the create category form.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Save a new category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $imageName = $this->storeCategoryImage($request);
        }

        try {
            DB::transaction(function () use ($validated, $request, $imageName) {
                Category::create([
                    'name' => $validated['name'],
                    'slug' => $this->makeUniqueSlug($validated['name']),
                    'description' => $validated['description'] ?? null,
                    'image' => $imageName,
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'is_active' => $request->boolean('is_active'),
                ]);
            });
        } catch (\Throwable $exception) {
            $this->removeCategoryImage($imageName);
            throw $exception;
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category added successfully.');
    }

    /**
     * Show the edit category form.
     */
    public function edit(Category $category): View
    {
        $category->loadCount('products');

        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $newImageName = null;

        if ($request->hasFile('image')) {
            $newImageName = $this->storeCategoryImage($request);
        }

        try {
            DB::transaction(function () use (
                $validated,
                $request,
                $category,
                $newImageName
            ) {
                $category->update([
                    'name' => $validated['name'],
                    'slug' => $this->makeUniqueSlug(
                        $validated['name'],
                        $category->id
                    ),
                    'description' => $validated['description'] ?? null,
                    'image' => $newImageName ?: $category->image,
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'is_active' => $request->boolean('is_active'),
                ]);
            });
        } catch (\Throwable $exception) {
            $this->removeCategoryImage($newImageName);
            throw $exception;
        }

        // The previous image file is deliberately retained.
        // This avoids accidentally removing an image still in use.

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Activate or deactivate a category without deleting it.
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => !$category->is_active,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                $category->is_active
                    ? 'Category activated successfully.'
                    : 'Category deactivated successfully.'
            );
    }

    /**
     * Generate a unique category image filename.
     */
    private function storeCategoryImage(Request $request): string
    {
        $file = $request->file('image');

        $directory = public_path('uploads/categories');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());

        $filename = 'category_' . now()->format('Ymd_His')
            . '_' . Str::uuid()
            . '.' . $extension;

        $file->move($directory, $filename);

        return $filename;
    }

    /**
     * Remove a newly uploaded image if saving fails.
     */
    private function removeCategoryImage(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = public_path('uploads/categories/' . basename($filename));

        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Generate a unique slug.
     */
    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Category::where('slug', $slug)
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}