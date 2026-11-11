<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AuthController extends Controller
{
    /**
     * Display customer registration.
     */
    public function showRegister(): View
    {
        return view('shop.auth.register');
    }

    /**
     * Register a new customer.
     */
    public function register(Request $request): RedirectResponse
    {
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

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:254', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'in:' . implode(',', $provinces)],
            'postal_code' => ['required', 'string', 'max:20'],
        ]);

        $guestSessionId = $request->session()->getId();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'province' => $validated['province'],
            'postal_code' => $validated['postal_code'],
            'role' => 'customer',
            'status' => 'active',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        $this->mergeGuestCart($user, $guestSessionId);

        return redirect()
            ->route('shop.home')
            ->with('success', 'Your Betachem account has been created successfully.');
    }

    /**
     * Display customer login.
     */
    public function showLogin(): View
    {
        return view('shop.auth.login');
    }

    /**
     * Authenticate an existing customer.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guestSessionId = $request->session()->getId();

        $credentials['status'] = 'active';

        if (!Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors([
                    'email' => 'Invalid email address or password, or your account is inactive.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $this->mergeGuestCart(Auth::user(), $guestSessionId);

        return redirect()
            ->route('shop.cart.index')
            ->with('success', 'Welcome back to Betachem!');
    }

    /**
     * Display the forgot-password page.
     */
    public function showForgotPassword(): View
    {
        return view('shop.auth.forgot-password');
    }

    /**
     * Generate a customer password-reset token.
     */
    public function sendResetLink(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:254'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        // Prevent an old testing link from appearing for another request.
        $request->session()->forget('password_reset_link');

        try {
            $user = DB::table('users')
                ->where('email', $validated['email'])
                ->where('role', 'customer')
                ->where('status', 'active')
                ->first();

            if ($user) {
                $token = bin2hex(random_bytes(32));

                DB::transaction(function () use ($user, $token) {

                    // Remove previous unused tokens for this customer.
                    DB::table('password_resets')
                        ->where('user_id', $user->id)
                        ->whereNull('used_at')
                        ->delete();

                    // Save the new token with a one-hour expiry.
                    DB::table('password_resets')->insert([
                        'user_id' => $user->id,
                        'token' => $token,
                        'expires_at' => now()->addHour()->toDateTimeString(),
                        'created_at' => now(),
                    ]);
                });

                // Local testing link until email delivery is configured.
                $resetLink = route('shop.password.reset', [
                    'token' => $token,
                ]);

                $request->session()->put(
                    'password_reset_link',
                    $resetLink
                );
            }

            // Use the same response whether the account exists or not.
            return view('shop.auth.forgot-password', [
                'success' => 'If an account exists with that email address, a password reset link has been generated.',
            ]);

        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors([
                    'email' => 'We could not process your request. Please try again.',
                ])
                ->withInput($request->only('email'));
        }
    }

    /**
     * Display the reset-password page.
     */
    public function showResetPassword(string $token): View
    {
        $reset = DB::table('password_resets')
            ->join('users', 'users.id', '=', 'password_resets.user_id')
            ->where('password_resets.token', $token)
            ->whereNull('password_resets.used_at')
            ->where('password_resets.expires_at', '>', now())
            ->where('users.role', 'customer')
            ->where('users.status', 'active')
            ->select('password_resets.id')
            ->first();

        return view('shop.auth.reset-password', [
            'token' => $token,
            'validReset' => (bool) $reset,
            'success' => null,
            'error' => $reset
                ? null
                : 'This password reset link is invalid or has expired.',
        ]);
    }

    /**
     * Process the customer's new password.
     */
    public function resetPassword(
        Request $request,
        string $token
    ): View {
        $password = (string) $request->input('password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        if (strlen($password) < 8) {
            return view('shop.auth.reset-password', [
                'token' => $token,
                'validReset' => true,
                'success' => null,
                'error' => 'Your password must be at least 8 characters long.',
            ]);
        }

        if ($password !== $confirmPassword) {
            return view('shop.auth.reset-password', [
                'token' => $token,
                'validReset' => true,
                'success' => null,
                'error' => 'The passwords do not match.',
            ]);
        }

        try {
            DB::transaction(function () use ($token, $password) {

                // Lock the token so it cannot be used twice concurrently.
                $reset = DB::table('password_resets')
                    ->where('token', $token)
                    ->whereNull('used_at')
                    ->where('expires_at', '>', now())
                    ->lockForUpdate()
                    ->first();

                if (!$reset) {
                    throw new \RuntimeException(
                        'This password reset link is invalid or has expired.'
                    );
                }

                $user = DB::table('users')
                    ->where('id', $reset->user_id)
                    ->where('role', 'customer')
                    ->where('status', 'active')
                    ->first();

                if (!$user) {
                    throw new \RuntimeException(
                        'This password reset link is invalid or has expired.'
                    );
                }

                // Update the customer's existing account password.
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'password' => Hash::make($password),
                        'updated_at' => now(),
                    ]);

                // Mark the current token as used.
                DB::table('password_resets')
                    ->where('id', $reset->id)
                    ->whereNull('used_at')
                    ->update([
                        'used_at' => now(),
                    ]);

                // Remove all other reset tokens belonging to this customer.
                DB::table('password_resets')
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $reset->id)
                    ->delete();
            });

            return view('shop.auth.reset-password', [
                'token' => $token,
                'validReset' => false,
                'success' => 'Your password has been reset successfully.',
                'error' => null,
            ]);

        } catch (Throwable $e) {
            report($e);

            $message = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Something went wrong while resetting your password. Please try again.';

            return view('shop.auth.reset-password', [
                'token' => $token,
                'validReset' => false,
                'success' => null,
                'error' => $message,
            ]);
        }
    }

    /**
     * Log out the customer.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('shop.home')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Merge a guest cart into the authenticated customer's cart.
     */
    private function mergeGuestCart(
        User $user,
        string $guestSessionId
    ): void {
        DB::transaction(function () use ($user, $guestSessionId) {

            $guestCart = Cart::with('items')
                ->whereNull('user_id')
                ->where('session_id', $guestSessionId)
                ->first();

            if (!$guestCart) {
                return;
            }

            $customerCart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => null]
            );

            foreach ($guestCart->items as $guestItem) {

                $existingItem = $customerCart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->quantity += $guestItem->quantity;
                    $existingItem->save();
                } else {
                    $guestItem->cart_id = $customerCart->id;
                    $guestItem->save();
                }
            }

            $guestCart->delete();
        });
    }
}
