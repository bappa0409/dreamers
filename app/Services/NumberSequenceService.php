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

            // Self-healing floor: some callers (e.g. member shares) have
            // more than one code path that can create a record carrying
            // this prefix, and not all of them necessarily go through this
            // sequence. If the stored counter ever falls behind the actual
            // highest number already in use (drift), blindly doing
            // $current+1 can reissue a number that already exists and
            // trip a unique-constraint violation. So on every call — not
            // just when the counter is still at its initial 0 — we take
            // whichever is higher: the stored counter, or whatever
            // $initialValue() reports as the real current max. This keeps
            // the sequence self-correcting without needing a one-off data
            // migration.
            if($initialValue){
                $current=max(
                    $current,
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