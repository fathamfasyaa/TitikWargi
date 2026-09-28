<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as GoogleUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Send the visitor to Google's sign-in page.
     *
     * "?back=/reports/5" brings the visitor back to that page after login.
     */
    public function redirect(Request $request): SymfonyRedirectResponse
    {
        $back = $request->string('back')->toString();

        // Only paths on this site ("/..."), never "//other-site.com" or "https://...".
        if (str_starts_with($back, '/') && ! str_starts_with($back, '//') && ! str_contains($back, '\\')) {
            $request->session()->put('url.intended', url($back));
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Google sends the visitor back here after they sign in.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            // For example: the visitor pressed "Cancel", or the login link expired.
            report($exception);

            return redirect('/')->with('error', 'Login dengan Google gagal. Silakan coba lagi.');
        }

        if (! ($googleUser->user['email_verified'] ?? false)) {
            return redirect('/')->with('error', 'Email akun Google Anda belum terverifikasi.');
        }

        $user = $this->findOrCreateUser($googleUser);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Find the user by Google ID, then by email, or create a new one.
     */
    private function findOrCreateUser(GoogleUser $googleUser): User
    {
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first()
            ?? new User(['email' => $googleUser->getEmail()]);

        // Keep the name and photo in sync with the Google account.
        $user->fill([
            'google_id' => $googleUser->getId(),
            'name' => $googleUser->getName() ?? $googleUser->getEmail(),
            'avatar' => $googleUser->getAvatar(),
        ]);
        $user->email_verified_at ??= now();
        $user->save();

        return $user;
    }
}
