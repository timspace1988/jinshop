<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InternalException extends Exception
{
    protected $msgForUser;

    //first param $message is for real error message sent by system and it will be printed in system logs, second param is the error message we will tell user
    public function __construct(string $message, string $msgForUser = 'System internal error', int $code = 500){
        parent::__construct($message, $code);
        $this->msgForUser = $msgForUser;
    }

    public function render(Request $request){
        if($request->expectsJson()){
            return response()->json(['msg' => $this->msgForUser], $this->code);
        }

        return view('pages.error', ['msg' => $this->msgForUser]);
    }
}
