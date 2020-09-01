<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request; 


class CouponCodeUnavailableException extends Exception
{
    public function __construct($message, int $code = 403)
    {
        parent::__construct($message, $code);
    }

    //when this exception is trigered, the render method will be called to output to user
    public function render(Request $request){
        //if request is sent via ajax, return a response with json type message
        if($request->expectsJson()){
            return response()->json(['msg' => $this->message], $this->code);
        }

        //if not ajax, go back to previous page with error info
        return redirect()->back()->withErrors(['coupon_code' => $this->message]);
    }
}
