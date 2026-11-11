<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        // Read shop filters
        $categorySlug = trim($request->query('category', ''));
        $search = trim($request->query('search', ''));
        $priceRange = trim($request->query('price_range', ''));
        $inStock = $request->boolean('in_stock');
        $featured = $request->boolean('featured');

        $allowedSorts = [
            'newest',
            'popular',
            'price_low',
            'price_high',
            'name_asc',
            'name_desc',
        ];

        $sort = $request->query('sort', 'newest');

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        // Available price ranges
        $priceRanges = [
            '' => [
                'label' => 'All Prices',
                'min' => null,
                'max' => null,
            ],
            'under_200' => [
                'label' => 'Under R200',
                'min' => null,
                'max' => 200,
            ],
            '200_299' => [
                'label' => 'R200 – R299',
                'min' => 200,
                'max' => 300,
            ],
            '300_499' => [
                'label' => 'R300 – R499',
                'min' => 300,
                'max' => 500,
            ],
            '500_999' => [
                'label' => 'R500 – R999',
                'min' => 500,
                'max' => 1000,
            ],
            '1000_2499' => [
                'label' => 'R1,000 – R2,499',
                'min' => 1000,
                'max' => 2500,
            ],
            '2500_4999' => [
                'label' => 'R2,500 – R4,999',
                'min' => 2500,
                'max' => 5000,
            ],
            '5000_plus' => [
                'label' => 'R5,000+',
                'min' => 5000,
                'max' => null,
            ],
        ];

        if (!array_key_exists($priceRange, $priceRanges)) {
            $priceRange = '';
        }

        // Active categories and product counts
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount([
                'products as product_count' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Selected category
        $selectedCategory = null;

        if ($categorySlug !== '') {
            $selectedCategory = Category::query()
                ->where('slug', $categorySlug)
                ->where('is_active', true)
                ->first();
        }

        // Build product query
        $productQuery = Product::query()
            ->with('category')
            ->where('is_active', true);

        // Search
        if ($search !== '') {
            $productQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($selectedCategory) {
            $productQuery->where(
                'category_id',
                $selectedCategory->id
            );
        }

        // Price filter
        $currentPrice = $priceRanges[$priceRange];

        if ($currentPrice['min'] !== null) {
            $productQuery->where(
                'price',
                '>=',
                $currentPrice['min']
            );
        }

        if ($currentPrice['max'] !== null) {
            $productQuery->where(
                'price',
                '<',
                $currentPrice['max']
            );
        }

        // Availability filter
        if ($inStock) {
            $productQuery->where('stock_quantity', '>', 0);
        }

        // Featured filter
        if ($featured) {
            $productQuery->where('is_featured', true);
        }

        // Sorting
        switch ($sort) {
            case 'price_low':
                $productQuery->orderBy('price')
                    ->orderBy('name');
                break;

            case 'price_high':
                $productQuery->orderByDesc('price')
                    ->orderBy('name');
                break;

            case 'name_asc':
                $productQuery->orderBy('name');
                break;

            case 'name_desc':
                $productQuery->orderByDesc('name');
                break;

            case 'popular':
                $productQuery->orderByDesc('is_featured')
                    ->orderByDesc('created_at');
                break;

            default:
                $productQuery->orderByDesc('created_at');
                break;
        }

        // Paginate products and preserve filters in pagination URLs
        $products = $productQuery
            ->paginate(4)
            ->withQueryString();

        // Featured products
        $featuredProducts = collect();

        if (
            !$selectedCategory &&
            $search === '' &&
            $priceRange === '' &&
            !$inStock &&
            !$featured
        ) {
            $featuredProducts = Product::query()
                ->with('category')
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderByDesc('created_at')
                ->take(4)
                ->get();
        }

        // Number of active filters
        $activeFilters =
            (int) ($categorySlug !== '') +
            (int) ($search !== '') +
            (int) ($priceRange !== '') +
            (int) $inStock +
            (int) $featured;

        // AJAX request: return only the product results
        if ($request->ajax()) {
            return response()->json([
                'html' => view(
                    'shop.partials.product-results',
                    compact('products')
                )->render(),

                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ]);
        }

        // Normal first page load
        return view('shop.home', compact(
            'categories',
            'selectedCategory',
            'products',
            'featuredProducts',
            'priceRanges',
            'categorySlug',
            'search',
            'priceRange',
            'inStock',
            'featured',
            'sort',
            'activeFilters'
        ));
    }
}
