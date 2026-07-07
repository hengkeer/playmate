<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Find existing user by email to link account, or create new
            $user = User::where('email', $googleUser->email)->first();

            $isNewUser = false;
            if ($user) {
                // Link Google ID if not linked
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $user->avatar ?? $googleUser->avatar,
                    ]);
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => null,
                ]);
                $isNewUser = true;
            }

            Auth::login($user);
            $user->update(['last_active_at' => now()]);

            if ($isNewUser) {
                return redirect('/profile')->with('success', 'Welcome to PlayMate! Please add at least 1 sport to your profile to get started.');
            }

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            dd($e->getMessage(), $e->getTraceAsString());
            return redirect('/login')->withErrors(['email' => 'Gagal login menggunakan Google. Silakan coba lagi.']);
        }
    }
}
