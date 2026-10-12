<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class SettingController extends Controller
{
    /**
     * Show the account settings form.
     */
    public function show()
    {
        return view('users.settings', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's account settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
        ]);

        $user->update($validated);

        return back()->with('success', 'Account settings updated successfully!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                Rules\Password::defaults(),
                'confirmed',
            ],
        ]);

        $user = Auth::user();

        // Verify current password
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Invalidate other sessions
        Auth::logoutOtherDevices($request->password);

        return back()->with('success', 'Password changed successfully!');
    }

    /**
     * Update the user's privacy settings.
     */
    public function updatePrivacy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'is_private' => ['required', 'boolean'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Privacy settings updated successfully!');
    }
}
