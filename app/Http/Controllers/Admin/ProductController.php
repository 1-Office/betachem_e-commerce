<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    /**
     * Display products.
     */
    public function index(Request $request): View
    {
        $totalProducts = Product::count();

        $activeProducts = Product::where('is_active', true)->count();

        $lowStockProducts = Product::where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->count();

        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)
            ->count();

        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        if ($request->input('stock') === 'out') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($request->input('stock') === 'low') {
            $query->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'minimum_stock');
        } elseif ($request->input('stock') === 'available') {
            $query->whereColumn('stock_quantity', '>', 'minimum_stock');
        }

        $products = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.products.index', compact(
            'products',
            'categories',
            'totalProducts',
            'activeProducts',
            'lowStockProducts',
            'outOfStockProducts'
        ));
    }

    /**
     * Show the create product form.
     */
    public function create(): View
    {
        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Save a new product.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'specifications' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'additional_images' => ['nullable', 'array', 'max:10'],
            'additional_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $imageFiles = $this->storeUploadedImages(
            $request,
            'main_image',
            'additional_images'
        );

        try {
            DB::transaction(function () use ($validated, $request, $imageFiles) {
                $product = Product::create([
                    'category_id' => $validated['category_id'],
                    'name' => $validated['name'],
                    'slug' => $this->makeUniqueSlug($validated['name']),
                    'sku' => $validated['sku'],
                    'description' => $validated['description'] ?? null,
                    'specifications' => $validated['specifications'] ?? null,
                    'price' => $validated['price'],
                    'stock_quantity' => $validated['stock_quantity'],
                    'minimum_stock' => $validated['minimum_stock'],
                    'weight' => $validated['weight'] ?? null,
                    'main_image' => $imageFiles['main'],
                    'is_active' => $request->boolean('is_active'),
                    'is_featured' => $request->boolean('is_featured'),
                ]);

                if ($imageFiles['main']) {
                    DB::table('product_images')->insert([
                        'product_id' => $product->id,
                        'image_path' => $imageFiles['main'],
                        'alt_text' => $product->name,
                        'sort_order' => 0,
                        'is_primary' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                foreach ($imageFiles['additional'] as $index => $image) {
                    DB::table('product_images')->insert([
                        'product_id' => $product->id,
                        'image_path' => $image,
                        'alt_text' => $product->name,
                        'sort_order' => $index + 1,
                        'is_primary' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            $this->removeUploadedImages($imageFiles);
            throw $exception;
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product added successfully.');
    }

    /**
     * Show the edit product form.
     */
    public function edit(Product $product): View
    {
        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $productImages = DB::table('product_images')
            ->where('product_id', $product->id)
            ->orderBy('sort_order')
            ->get();

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'productImages'
        ));
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'specifications' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'additional_images' => ['nullable', 'array', 'max:10'],
            'additional_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $imageFiles = $this->storeUploadedImages(
            $request,
            'main_image',
            'additional_images'
        );

        try {
            DB::transaction(function () use (
                $validated,
                $request,
                $product,
                $imageFiles
            ) {
                $product->update([
                    'category_id' => $validated['category_id'],
                    'name' => $validated['name'],
                    'slug' => $this->makeUniqueSlug($validated['name'], $product->id),
                    'sku' => $validated['sku'],
                    'description' => $validated['description'] ?? null,
                    'specifications' => $validated['specifications'] ?? null,
                    'price' => $validated['price'],
                    'stock_quantity' => $validated['stock_quantity'],
                    'minimum_stock' => $validated['minimum_stock'],
                    'weight' => $validated['weight'] ?? null,
                    'is_active' => $request->boolean('is_active'),
                    'is_featured' => $request->boolean('is_featured'),
                ]);

                if ($imageFiles['main']) {
                    $product->update([
                        'main_image' => $imageFiles['main'],
                    ]);

                    $primaryImage = DB::table('product_images')
                        ->where('product_id', $product->id)
                        ->where('is_primary', 1)
                        ->first();

                    if ($primaryImage) {
                        DB::table('product_images')
                            ->where('id', $primaryImage->id)
                            ->update([
                                'image_path' => $imageFiles['main'],
                                'alt_text' => $product->name,
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('product_images')->insert([
                            'product_id' => $product->id,
                            'image_path' => $imageFiles['main'],
                            'alt_text' => $product->name,
                            'sort_order' => 0,
                            'is_primary' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $nextSortOrder = (int) DB::table('product_images')
                    ->where('product_id', $product->id)
                    ->max('sort_order') + 1;

                foreach ($imageFiles['additional'] as $image) {
                    DB::table('product_images')->insert([
                        'product_id' => $product->id,
                        'image_path' => $image,
                        'alt_text' => $product->name,
                        'sort_order' => $nextSortOrder++,
                        'is_primary' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            $this->removeUploadedImages($imageFiles);
            throw $exception;
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Activate or deactivate a product without deleting it.
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update([
            'is_active' => !$product->is_active,
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                $product->is_active
                    ? 'Product activated successfully.'
                    : 'Product deactivated successfully.'
            );
    }

    /**
     * Generate a unique product slug.
     */
    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $counter = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Save uploaded product images.
     */
    private function storeUploadedImages(
        Request $request,
        string $mainField,
        string $additionalField
    ): array {
        $directory = public_path('uploads/products');

        File::ensureDirectoryExists($directory);

        $mainImage = null;
        $additionalImages = [];

        if ($request->hasFile($mainField)) {
            $mainImage = $this->saveImageFile(
                $request->file($mainField),
                $directory
            );
        }

        if ($request->hasFile($additionalField)) {
            foreach ($request->file($additionalField) as $file) {
                $additionalImages[] = $this->saveImageFile($file, $directory);
            }
        }

        return [
            'main' => $mainImage,
            'additional' => $additionalImages,
        ];
    }

    /**
     * Save one image using a unique filename.
     */
    private function saveImageFile($file, string $directory): string
    {
        $extension = strtolower($file->extension() ?: 'jpg');

        $filename = 'product_' . now()->format('Ymd_His')
            . '_' . Str::uuid() . '.' . $extension;

        $file->move($directory, $filename);

        return $filename;
    }

    /**
     * Remove newly uploaded files if the database operation fails.
     */
    private function removeUploadedImages(array $images): void
    {
        $files = array_filter(array_merge(
            [$images['main'] ?? null],
            $images['additional'] ?? []
        ));

        foreach ($files as $filename) {
            $path = public_path('uploads/products/' . basename($filename));

            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }
}
