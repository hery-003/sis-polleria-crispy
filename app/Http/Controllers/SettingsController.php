<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Middleware;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    #[Middleware(['auth', 'role:admin'])]
    public function index()
    {
        $settings = Setting::pluck('value', 'key');

        return inertia('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'session_lifetime' => 'required|integer|min:1|max:1440',
            'app_name' => 'required|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value);
        }

        Cache::forget('settings_all');

        return redirect()->route('settings.index')->with('success', 'Configuración actualizada correctamente');
    }
}
