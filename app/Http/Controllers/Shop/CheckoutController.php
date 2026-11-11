<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        $cart = Cart::with('items.product')
            ->where('user_id', $user->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('shop.cart.index')
                ->with('error', 'Your shopping cart is empty.');
        }

        $cartItems = $cart->items;

        $subtotal = 0;

        foreach ($cartItems as $item) {
            if (!$item->product || !$item->product->is_active) {
                continue;
            }

            $subtotal +=
                (float) $item->product->price *
                (int) $item->quantity;
        }

        $deliveryFee = 0;
        $discount = 0;
        $total = $subtotal + $deliveryFee - $discount;

        $provinces = [
            'Eastern Cape',
            'Free State',
            'Gauteng',
            'KwaZulu-Natal',
            'Limpopo',
            'Mpumalanga',
            'Northern Cape',
            'North West',
            'Western Cape',
        ];

        return view('shop.checkout.index', compact(
            'user',
            'cart',
            'cartItems',
            'subtotal',
            'deliveryFee',
            'discount',
            'total',
            'provinces'
        ));
    }


    /**
     * Validate checkout and place an order.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'address' => [
                'required',
                'string',
                'max:255',
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],

            'province' => [
                'required',
                Rule::in([
                    'Eastern Cape',
                    'Free State',
                    'Gauteng',
                    'KwaZulu-Natal',
                    'Limpopo',
                    'Mpumalanga',
                    'Northern Cape',
                    'North West',
                    'Western Cape',
                ]),
            ],

            'postal_code' => [
                'required',
                'string',
                'max:20',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'payment_method' => [
                'required',
                Rule::in(['eft']),
            ],
        ]);

        $userId = Auth::id();

        try {
            $orderId = DB::transaction(function () use (
                $validated,
                $userId
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Customer Cart
                |--------------------------------------------------------------------------
                */

                $cart = Cart::where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if (!$cart) {
                    throw ValidationException::withMessages([
                        'cart' => 'Your shopping cart is empty.',
                    ]);
                }

                $cartItems = $cart->items()
                    ->orderBy('id')
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'cart' => 'Your shopping cart is empty.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Lock Products and Validate Stock
                |--------------------------------------------------------------------------
                */

                $products = Product::whereIn(
                    'id',
                    $cartItems->pluck('product_id')->unique()
                )
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $subtotal = 0;

                foreach ($cartItems as $item) {

                    $product = $products->get(
                        $item->product_id
                    );

                    if (!$product || !$product->is_active) {
                        throw ValidationException::withMessages([
                            'cart' => 'One or more products in your cart are no longer available.',
                        ]);
                    }

                    $quantity = (int) $item->quantity;

                    if ($quantity <= 0) {
                        throw ValidationException::withMessages([
                            'cart' => 'Your cart contains an invalid product quantity.',
                        ]);
                    }

                    if ($product->stock_quantity < $quantity) {
                        throw ValidationException::withMessages([
                            'cart' => 'Insufficient stock for ' .
                                $product->name .
                                '. Available stock: ' .
                                $product->stock_quantity . '.',
                        ]);
                    }

                    $subtotal +=
                        (float) $product->price * $quantity;
                }


                /*
                |--------------------------------------------------------------------------
                | Calculate Order Total
                |--------------------------------------------------------------------------
                */

                $subtotal = round($subtotal, 2);
                $deliveryFee = 0.00;
                $discount = 0.00;

                $total = round(
                    $subtotal + $deliveryFee - $discount,
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | Generate Order Number
                |--------------------------------------------------------------------------
                */

                do {
                    $orderNumber = 'BET-' .
                        now()->format('Ymd') .
                        '-' .
                        strtoupper(Str::random(6));

                } while (
                    DB::table('orders')
                        ->where('order_number', $orderNumber)
                        ->exists()
                );


                /*
                |--------------------------------------------------------------------------
                | Create Order
                |--------------------------------------------------------------------------
                */

                $now = now();

                $orderId = DB::table('orders')->insertGetId([
                    'user_id' => $userId,

                    'order_number' => $orderNumber,

                    'status' => 'pending',

                    'subtotal' => $subtotal,

                    'delivery_fee' => $deliveryFee,

                    'discount' => $discount,

                    'total' => $total,

                    'payment_status' => 'pending',

                    'payment_method' => 'eft',

                    'shipping_first_name' => $validated['first_name'],

                    'shipping_last_name' => $validated['last_name'],

                    'shipping_phone' => $validated['phone'],

                    'shipping_email' => $validated['email'],

                    'shipping_address' => $validated['address'],

                    'shipping_city' => $validated['city'],

                    'shipping_province' => $validated['province'],

                    'shipping_postal_code' => $validated['postal_code'],

                    'notes' => $validated['notes'] ?? null,

                    'created_at' => $now,

                    'updated_at' => $now,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Create Order Items and Deduct Stock
                |--------------------------------------------------------------------------
                */

                foreach ($cartItems as $item) {

                    $product = $products->get(
                        $item->product_id
                    );

                    $quantity = (int) $item->quantity;

                    $price = round(
                        (float) $product->price,
                        2
                    );

                    $itemSubtotal = round(
                        $price * $quantity,
                        2
                    );

                    DB::table('order_items')->insert([
                        'order_id' => $orderId,

                        'product_id' => $product->id,

                        'product_name' => $product->name,

                        'sku' => $product->sku,

                        'price' => $price,

                        'quantity' => $quantity,

                        'subtotal' => $itemSubtotal,

                        'created_at' => $now,

                        'updated_at' => $now,
                    ]);

                    $product->decrement(
                        'stock_quantity',
                        $quantity
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Create Customer Notification
                |--------------------------------------------------------------------------
                */

                DB::table('notifications')->insert([
                    'user_id' => $userId,

                    'order_id' => $orderId,

                    'type' => 'order_placed',

                    'title' => 'Order placed successfully',

                    'message' => 'Your order ' .
                        $orderNumber .
                        ' has been received and is now pending processing.',

                    'is_read' => 0,

                    'created_at' => $now,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Clear Customer Cart
                |--------------------------------------------------------------------------
                */

                $cart->items()->delete();

                return $orderId;
            });

            return redirect()
                ->route('shop.checkout.success', [
                    'id' => $orderId,
                ]);

        } catch (ValidationException $exception) {
            throw $exception;

        } catch (\Throwable $exception) {

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'We could not place your order. Please try again.'
                );
        }
    }
}
