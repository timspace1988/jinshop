<?php

namespace App\Http\Controllers;

use App\Models\CouponCode;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CouponCodesController extends Controller
{
    public function show($code){
        // try{
        //check if the coupon exists
        if(!$record = CouponCode::where('code', $code)->first()){
            abort(404);//terminate the execution of application
        }

        //if coupon is not enabled, we consider it same as not existing
        if(!$record->enabled){
            abort(404);
        }

        if($record->total - $record->used <= 0){
            return response()->json(['msg' => 'This coupon has been used out.'], 403);
        }

        if($record->not_before && $record->not_before->gt(Carbon::now())){
            return response()->json(['msg' => 'This coupon is not avaiable yet.'], 403);
        }

        if($record->not_after && $record->not_after->lt(Carbon::now())){
            return response()->json(['msg' => 'This coupon has expired.'], 403);
        }

        return $record;

        // }catch(\Throwable $t){
        //     return ['msg' => $t->getMessage(), 'line' => $t->getLine(), 't' => $t];
        // }
    }
}
