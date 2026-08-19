<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected SettingService $settingService;

    public function __construct(
        SettingService $settingService
    ) {
        $this->settingService = $settingService;
    }


    /*
    |--------------------------------------------------------------------------
    | List Settings
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,

            'data' => $this->settingService->all(
                $request->group
            )
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Show Setting
    |--------------------------------------------------------------------------
    */

    public function show(string $key)
    {
        $setting = Setting::where(
            'key',
            $key
        )->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $setting->key,
                'value' => $this->settingService->get(
                    $setting->key
                ),
                'type' => $setting->type,
                'group' => $setting->group,
                'description' => $setting->description,
            ]
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Create / Update Setting
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'nullable',
            'type' => 'required|in:string,boolean,integer,float,json,password,image',
            'group' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $setting = $this->settingService->set(
            $validated['key'],
            $validated['value'] ?? null,
            $validated['type'],
            $validated['group'],
            $validated['description'] ?? null,
            $validated['is_public'] ?? false
        );

        return response()->json([
            'success' => true,
            'message' => 'Setting saved successfully.',
            'data' => $setting
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Setting
    |--------------------------------------------------------------------------
    */

    public function destroy(string $key)
    {
        $setting = Setting::where(
            'key',
            $key
        )->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found.'
            ], 404);
        }

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Setting deleted successfully.'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Public Settings
    |--------------------------------------------------------------------------
    */

    public function publicSettings()
    {
        return response()->json([
            'success' => true,

            'data' =>
                $this->settingService
                    ->publicSettings()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Upload Image Setting (Logo / Favicon)
    |--------------------------------------------------------------------------
    */

    public function uploadImage(Request $request)
    {
        $definitions = [
            'site_logo' => [
                'group' => 'branding',
                'description' => 'Organization logo',
            ],
            'site_favicon' => [
                'group' => 'branding',
                'description' => 'Browser favicon',
            ],
        ];

        $validated = $request->validate([
            'key' => 'required|string|in:' . implode(',', array_keys($definitions)),
            'file' => 'required|image|mimes:png,jpg,jpeg,webp,svg,ico|max:2048',
        ]);

        $meta = $definitions[$validated['key']];

        /*
        |--------------------------------------------------------------------------
        | Remove Previous File
        |--------------------------------------------------------------------------
        */

        $existing = Setting::where('key', $validated['key'])->first();

        if ($existing && $existing->value && Storage::disk('public')->exists($existing->value)) {
            Storage::disk('public')->delete($existing->value);
        }

        $path = $request->file('file')->store('settings', 'public');

        $setting = $this->settingService->set(
            $validated['key'],
            $path,
            'image',
            $meta['group'],
            $meta['description'],
            true
        );

        return response()->json([
            'success' => true,
            'message' => 'Uploaded successfully.',
            'data' => [
                'key' => $setting->key,
                'value' => $setting->value,
                'url' => Storage::disk('public')->url($setting->value),
            ],
        ]);
    }
}