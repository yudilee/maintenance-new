<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Show general settings page
     */
    public function index()
    {
        $general = [
            'app_logo_path' => Setting::get('app_logo_path', asset('images/logo.png')),
            'app_logo_link' => Setting::get('app_logo_link', route('maintenance.dashboard')),
        ];

        return view('settings', compact('general'));
    }

    /**
     * Update general settings (Logo, etc)
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'app_logo_link' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            // Store the file in storage/app/public/logos
            $path = $request->file('logo')->store('logos', 'public');
            Setting::set('app_logo_path', Storage::url($path));
        }

        if ($request->has('app_logo_link')) {
            Setting::set('app_logo_link', $request->app_logo_link);
        }

        return redirect()->route('settings')->with('success', 'General settings updated successfully!');
    }
}
