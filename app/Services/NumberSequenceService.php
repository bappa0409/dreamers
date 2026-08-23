<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\DB;

class NumberSequenceService
{
    public function next(
        string $key,
        string $prefix,
        int $digits=6,
        ?Closure $initialValue=null
    ): string{
        return DB::transaction(function()use(
            $key,
            $prefix,
            $digits,
            $initialValue
        ){
            DB::table('number_sequences')->insertOrIgnore([
                'sequence_key'=>$key,
                'current_value'=>0,
                'created_at'=>now(),
                'updated_at'=>now()
            ]);

            $sequence=DB::table('number_sequences')
                ->where('sequence_key',$key)
                ->lockForUpdate()
                ->first();

            $current=(int)$sequence->current_value;

            if($current===0&&$initialValue){
                $current=max(
                    0,
                    (int)$initialValue()
                );
            }

            $next=$current+1;

            DB::table('number_sequences')
                ->where('sequence_key',$key)
                ->update([
                    'current_value'=>$next,
                    'updated_at'=>now()
                ]);

            return $prefix.str_pad(
                (string)$next,
                $digits,
                '0',
                STR_PAD_LEFT
            );
        });
    }
}