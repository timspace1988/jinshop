<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InvalidRequestException extends Exception
{
    public function __construct(string $message = "", int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request){
        //check if the request expect a json response, if yes, this means the request is sent by ajax, and we need return json type data
        //if no, we return a page
        if($request->expectsJson()){
            //the second param is Http status code
            return response()->json(['msg' => $this->message], $this->code); 
        }

        return view('pages.error', ['msg' =>$this->message] );
    }
}
