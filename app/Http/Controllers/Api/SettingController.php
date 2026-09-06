<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'group'=>'nullable|string|max:100',
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$this->settingService->all(
                $validated['group']??null
            ),
        ]);
    }

    public function show(string $key)
    {
        $setting=Setting::where('key',$key)->first();

        if(!$setting){
            return response()->json([
                'success'=>false,
                'message'=>'Setting not found.',
            ],404);
        }

        return response()->json([
            'success'=>true,
            'data'=>[
                'key'=>$setting->key,
                'value'=>$setting->type==='password'
                    ?($setting->value?'':null)
                    :$this->settingService->get($setting->key),
                'type'=>$setting->type,
                'options'=>$setting->options,
                'group'=>$setting->group,
                'description'=>$setting->description,
                'is_public'=>$setting->is_public,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated=$this->validateSettingPayload(
            $request->all()
        );

        $setting=$this->settingService->set(
            $validated['key'],
            $validated['value']??null,
            $validated['type'],
            $validated['group'],
            $validated['description']??null,
            $validated['is_public']??false,
            $validated['options']??null
        );

        return response()->json([
            'success'=>true,
            'message'=>'Setting saved successfully.',
            'data'=>$this->settingService->redact($setting),
        ]);
    }

    /**
     * Save all fields from one settings panel using one HTTP request.
     */
    public function bulkStore(Request $request)
    {
        $outer=$request->validate([
            'settings'=>'required|array|min:1|max:100',
            'settings.*'=>'required|array',
        ]);

        $items=collect($outer['settings'])
            ->map(fn(array $item)=>$this->validateSettingPayload($item))
            ->values()
            ->all();

        $saved=$this->settingService
            ->setMany($items)
            ->map(fn(Setting $setting)=>$this->settingService->redact($setting))
            ->values();

        return response()->json([
            'success'=>true,
            'message'=>'Settings saved successfully.',
            'data'=>$saved,
        ]);
    }

    public function destroy(string $key)
    {
        if(!$this->settingService->delete($key)){
            return response()->json([
                'success'=>false,
                'message'=>'Setting not found.',
            ],404);
        }

        return response()->json([
            'success'=>true,
            'message'=>'Setting deleted successfully.',
        ]);
    }

    public function publicSettings()
    {
        $settings=$this->settingService
            ->publicSettings()
            ->mapWithKeys(function($setting){
                $value=$setting->type==='password'
                    ?null
                    :$this->settingService->get($setting->key);

                if($setting->type==='image'&&$value){
                    $value=Storage::disk('public')->url($value);
                }

                return[$setting->key=>$value];
            });

        return response()->json([
            'success'=>true,
            'data'=>$settings,
        ]);
    }

    public function uploadImage(Request $request)
    {
        $definitions=[
            'site_logo'=>[
                'group'=>'branding',
                'description'=>'Organization logo',
            ],
            'site_favicon'=>[
                'group'=>'branding',
                'description'=>'Browser favicon',
            ],
            'site_logo_other'=>[
                'group'=>'branding',
                'description'=>'For Receipt, Invoice, Voucher.',
            ],
        ];

        $validated=$request->validate([
            'key'=>'required|string|in:'.implode(',',array_keys($definitions)),
            'file'=>'required|file|mimes:png,jpg,jpeg,webp,ico|max:2048',
        ]);

        $meta=$definitions[$validated['key']];
        $existing=Setting::where('key',$validated['key'])->first();
        $oldPath=$existing?->value;

        // Store the replacement first. The previous image is only removed after
        // the setting row has been updated successfully.
        $path=$request->file('file')->store('settings','public');

        try{
            $setting=$this->settingService->set(
                $validated['key'],
                $path,
                'image',
                $meta['group'],
                $meta['description'],
                true
            );
        }catch(\Throwable $e){
            Storage::disk('public')->delete($path);
            throw $e;
        }

        if(
            $oldPath&&
            $oldPath!==$path&&
            Storage::disk('public')->exists($oldPath)
        ){
            Storage::disk('public')->delete($oldPath);
        }

        return response()->json([
            'success'=>true,
            'message'=>'Uploaded successfully.',
            'data'=>[
                'key'=>$setting->key,
                'value'=>$setting->value,
                'url'=>Storage::disk('public')->url($setting->value),
            ],
        ]);
    }

    protected function validateSettingPayload(array $payload): array
    {
        $payload['type']=$this->normalizeType(
            (string)($payload['type']??'string')
        );

        $validated=Validator::make($payload,[
            'key'=>'required|string|max:255',
            'value'=>'nullable',
            'type'=>'required|in:string,boolean,integer,float,json,password,image,select',
            'group'=>'required|string|max:100',
            'description'=>'nullable|string|max:2000',
            'is_public'=>'nullable|boolean',
            'options'=>'nullable|array|max:100',
            'options.*'=>'string|max:255',
        ])->validate();

        $this->validateTypedValue($validated);

        return $validated;
    }

    protected function normalizeType(string $type): string
    {
        $type=strtolower(trim($type));

        return match($type){
            'bool'=>'boolean',
            'int'=>'integer',
            'number','decimal','double'=>'float',
            default=>$type,
        };
    }

    protected function validateTypedValue(array $setting): void
    {
        $type=$setting['type'];
        $value=$setting['value']??null;

        if($value===null||($type==='password'&&$value==='')){
            return;
        }

        $invalid=false;

        if($type==='boolean'){
            $invalid=filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            )===null;
        }elseif($type==='integer'){
            $invalid=filter_var($value,FILTER_VALIDATE_INT)===false;
        }elseif($type==='float'){
            $invalid=!is_numeric($value);
        }elseif($type==='json'){
            if(is_string($value)){
                json_decode($value,true);
                $invalid=json_last_error()!==JSON_ERROR_NONE;
            }else{
                $invalid=!is_array($value)&&!is_object($value);
            }
        }elseif($type==='select'){
            $options=$setting['options']??[];
            $invalid=!empty($options)&&!in_array((string)$value,$options,true);
        }

        if($invalid){
            throw ValidationException::withMessages([
                'value'=>[
                    "Invalid value for {$setting['key']} ({$type}).",
                ],
            ]);
        }
    }
}