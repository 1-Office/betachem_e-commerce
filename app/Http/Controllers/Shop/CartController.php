<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Find or create the current customer's cart.
     * Merge any existing guest cart after login.
     */
    private function getCart(Request $request): Cart
    {
        return DB::transaction(function () use ($request) {

            if (auth()->check()) {

                $cart = Cart::firstOrCreate(
                    ['user_id' => auth()->id()],
                    ['session_id' => null]
                );

                $guestCart = Cart::whereNull('user_id')
                    ->where('session_id', $request->session()->getId())
                    ->lockForUpdate()
                    ->first();

                if ($guestCart && $guestCart->id !== $cart->id) {

                    $guestItems = $guestCart->items()
                        ->lockForUpdate()
                        ->get();

                    foreach ($guestItems as $guestItem) {

                        $existingItem = $cart->items()
                            ->where('product_id', $guestItem->product_id)
                            ->lockForUpdate()
                            ->first();

                        if ($existingItem) {

                            $existingItem->quantity += $guestItem->quantity;
                            $existingItem->save();

                            $guestItem->delete();

                        } else {

                            $guestItem->cart_id = $cart->id;
                            $guestItem->save();
                        }
                    }

                    $guestCart->delete();
                }

                return $cart;
            }

            return Cart::firstOrCreate(
                ['session_id' => $request->session()->getId()],
                ['user_id' => null]
            );
        });
    }

    /**
     * Display the shopping cart.
     */
    public function index(Request $request): View
    {
        $cart = $this->getCart($request);

        $cart->load('items.product');

        $subtotal = $cart->items->sum(function ($item) {
            return (float) $item->price * (int) $item->quantity;
        });

        return view('shop.cart.index', compact('cart', 'subtotal'));
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request, int $productId): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);

        $result = DB::transaction(function () use (
            $request,
            $productId,
            $quantity
        ) {

            $product = Product::where('id', $productId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                return 'unavailable';
            }

            if ($quantity > $product->stock_quantity) {
                return 'insufficient_stock';
            }

            $cart = $this->getCart($request);

            $item = $cart->items()
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = $quantity + ($item?->quantity ?? 0);

            if ($newQuantity > $product->stock_quantity) {
                return 'insufficient_stock';
            }

            if ($item) {

                $item->quantity = $newQuantity;
                $item->price = $product->price;
                $item->save();

            } else {

                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }

            return 'success';
        });

        if ($result === 'unavailable') {
            return back()->with(
                'error',
                'This product is currently unavailable.'
            );
        }

        if ($result === 'insufficient_stock') {
            return back()->with(
                'error',
                'There is not enough stock available for that quantity.'
            );
        }

        return redirect()
            ->route('shop.cart.index')
            ->with('success', 'Product added to your cart.');
    }

    /**
     * Update a cart item's quantity.
     */
    public function update(Request $request, int $itemId): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $result = DB::transaction(function () use (
            $request,
            $itemId,
            $validated
        ) {

            $cart = $this->getCart($request);

            $item = $cart->items()
                ->where('id', $itemId)
                ->lockForUpdate()
                ->firstOrFail();

            $product = Product::where('id', $item->product_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                return 'unavailable';
            }

            if ($validated['quantity'] > $product->stock_quantity) {
                return 'insufficient_stock';
            }

            $item->quantity = (int) $validated['quantity'];
            $item->price = $product->price;
            $item->save();

            return 'success';
        });

        if ($result === 'unavailable') {
            return back()->with(
                'error',
                'This product is no longer available.'
            );
        }

        if ($result === 'insufficient_stock') {
            return back()->with(
                'error',
                'The requested quantity exceeds available stock.'
            );
        }

        return back()->with('success', 'Cart quantity updated.');
    }

    /**
     * Remove a cart item.
     */
    public function remove(Request $request, int $itemId): RedirectResponse
    {
        $cart = $this->getCart($request);

        $cart->items()
            ->where('id', $itemId)
            ->firstOrFail()
            ->delete();

        return back()->with(
            'success',
            'Product removed from your cart.'
        );
    }
}