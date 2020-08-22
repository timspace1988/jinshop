<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Symfony\Component\ErrorHandler\Debug;
use Yansongda\Pay\Pay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //inject an singleton object named 'alipay' into container
        $this->app->singleton('alipay', function(){
            $config = config('pay.alipay');
            //check the environment to decide if enable alipay's developing mode and set different level of logs
            if(app()->environment() !== 'production'){
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log'] = Logger::WARNING;
            }

            //call Yansongda\Pay to create a alipay object, then we can use app('alipay') to create an aplipay instance
            return Pay::alipay($config);
        });

        //inject an singleton object named 'wechat_pay' into container
        $this->app->singleton('wechat_pay', function(){
            $config = config('pay.wechat');
            //as wechat does not come with a developing mode, we just set the log level depending on different environment
            if(app()->environment() !== 'production'){
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }

            //call Yansongda\Pay to create a wechat_pay object
            return Pay::wechat($config);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
