<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = \App\Models\Setting::all();
        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting->key] = $setting->value;
        }
        return response()->json(['data' => $formatted]);
    }

    // Hanya key yang aman ditampilkan ke publik
    public function publik()
    {
        $keys = ['app_name', 'app_description', 'rekening_info'];
        $settings = \App\Models\Setting::whereIn('key', $keys)->pluck('value', 'key');
        return response()->json(['data' => collect($keys)->mapWithKeys(fn ($k) => [$k => $settings[$k] ?? ''])]);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        foreach ($data as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        return response()->json(['message' => 'Settings updated']);
    }
}
