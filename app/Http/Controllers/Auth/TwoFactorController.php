<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        if (! $user->two_factor_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->two_factor_secret = $secret;
            $user->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        return Inertia::render('Auth/TwoFactorSetup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $user->two_factor_secret,
            'enabled' => $user->twoFactorEnabled(),
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'El código ingresado no es válido.']);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        return redirect()->route('profile.edit')->with('success', '2FA activado correctamente.');
    }

    public function disable(Request $request)
    {
        $user = $request->user();
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return redirect()->route('profile.edit')->with('success', '2FA desactivado.');
    }
}
