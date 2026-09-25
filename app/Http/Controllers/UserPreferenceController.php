<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserPreferenceController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark'],
            'sidebar' => ['required', 'in:expanded,compact'],
            'navbar' => ['required', 'in:sticky,static'],
            'sidebar_color' => ['required', 'in:light,dark'],
            'color_theme' => ['required', 'in:white,cyan,black,purple,orange,green,red'],
        ]);

        $request->user()->updatePreferences($validated);

        return back()->with('success', 'Preferensi tampilan berhasil disimpan.');
    }
}
