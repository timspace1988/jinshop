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
            //get configs from 'config/pay'
            $config = config('pay.alipay');
            //alipay's payment callback request address to our backend  
            //$config['notify_url'] = route('payment.alipay.notify');
            //$config['notify_url'] = 'http://requestbin.net/r/yoe8dpyo';
            if(app()->environment() === 'production'){
                $config['notify_url'] = route('payment.alipay.notify');
            }else{
                $config['notify_url'] = 'http://requestbin.net/r/15wn8gv1';
                //request bin only available for 48 hours, after it, you need to recreate the link on request bin and update it here
            }
            //alipay's payment callback request address to our browser
            $config['return_url'] = route('payment.alipay.return');
            //check the environment to decide if enable alipay's developing mode and set different level of logs
            if(app()->environment() !== 'production'){
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            }else{
                /*
                Note: we should't set $config['mode'] = 'dev' here for production environment
                      but we need to use alipay sandbox to do test on heroku, we wil(want to) be directed to 'https://openapi.alipaydev.com' to make payment,
                      if we comment this line(withoud $config['mode'] = 'dev';)we will be directed to https://openapi.alipay.com, then get a 'valid-app-id' error
                      After you have applied for and set up a real alipay service, 
                      you need to comment this line, so that your online-app can direct real customer to  real payment page
                 */
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::WARNING;
            }

            //dd($config);

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
