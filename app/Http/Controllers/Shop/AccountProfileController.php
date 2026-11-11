<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AccountProfileController extends Controller
{
    /**
     * Display the profile editing page.
     */
    public function edit(): View
    {
        return view('shop.account.edit', [
            'user' => Auth::user(),
            'provinces' => [
                'Eastern Cape',
                'Free State',
                'Gauteng',
                'KwaZulu-Natal',
                'Limpopo',
                'Mpumalanga',
                'Northern Cape',
                'North West',
                'Western Cape',
            ],
        ]);
    }

    /**
     * Update the customer's profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

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
                'max:190',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'phone' => [
                'required',
                'string',
                'max:50',
            ],

            'address' => [
                'required',
                'string',
                'max:500',
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],

            'province' => [
                'required',
                Rule::in($provinces),
            ],

            'postal_code' => [
                'required',
                'string',
                'max:20',
            ],

            'current_password' => [
                'nullable',
                'required_with:new_password,confirm_password',
                'current_password',
            ],

            'new_password' => [
                'nullable',
                'required_with:current_password,confirm_password',
                Password::min(8),
            ],

            'confirm_password' => [
                'nullable',
                'required_with:current_password,new_password',
                'same:new_password',
            ],
        ]);

        // Update personal and delivery information.
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->address = $validated['address'];
        $user->city = $validated['city'];
        $user->province = $validated['province'];
        $user->postal_code = $validated['postal_code'];

        // Change the password only when a new password is supplied.
        if (!empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        // Save without changing the customer's role or status.
        $user->save();

        // Refresh the authenticated user's information.
        $user->refresh();

        return redirect()
            ->route('shop.account.edit')
            ->with('success', 'Your account details have been updated successfully.');
    }
}
