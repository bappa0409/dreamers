<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function current()
    {
        return response()->json([
            'language' => App::getLocale(),
        ]);
    }

    public function switch(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|in:en,bn',
        ]);

        $user = $request->user();

        $user->update([
            'language' => $validated['language'],
        ]);

        App::setLocale($validated['language']);

        return response()->json([
            'message' => 'Language changed successfully.',
            'language' => $user->language,
        ]);
    }
}
