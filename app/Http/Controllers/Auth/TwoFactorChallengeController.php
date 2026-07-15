<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request)
    {
        if (! $request->session()->has('login.two_factor_user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = $request->session()->get('login.two_factor_user_id');
        $user = User::find($userId);

        if (! $user || ! $user->two_factor_secret) {
            return redirect()->route('login');
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'El código no es válido. Intente de nuevo.']);
        }

        $request->session()->forget('login.two_factor_user_id');
        auth()->login($user, $request->session()->get('login.two_factor_remember', false));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
