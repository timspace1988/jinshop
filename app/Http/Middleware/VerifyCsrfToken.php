<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
        'payment/alipay/notify',//we need alipay to send request to our back-end server via this url, so we exclude it(because alipay doesnt have our csrf token, but we trust it)
        'installments/alipay/notify',
    ];
}
