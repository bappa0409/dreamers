<?php

namespace App\Services;

use App\Models\Land;
use Illuminate\Support\Facades\DB;

class LandService
{
    public function create(array $data): Land
    {
        return DB::transaction(function () use ($data) {

            $lastLand = Land::latest('id')->first();

            $nextNumber = $lastLand
                ? $lastLand->id + 1
                : 1;

            $landCode = 'LAND-' . str_pad(
                $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );

            return Land::create([
                'land_code' => $landCode,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'district' => $data['district'] ?? null,
                'upazila' => $data['upazila'] ?? null,
                'mouza' => $data['mouza'] ?? null,
                'khatian_no' => $data['khatian_no'] ?? null,
                'dag_no' => $data['dag_no'] ?? null,
                'land_area' => $data['land_area'] ?? null,
                'area_unit' => $data['area_unit'] ?? 'decimal',
                'purchase_price' => $data['purchase_price'] ?? 0,
                'purchase_date' => $data['purchase_date'] ?? null,
                'seller_name' => $data['seller_name'] ?? null,
                'seller_phone' => $data['seller_phone'] ?? null,
                'status' => $data['status'] ?? 'planned',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}