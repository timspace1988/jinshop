<?php
// function test_helper(){
//     return 'OK';
// }

//Convert route name from xx.yy to xx-yy
function route_class(){
    return str_replace('.', '-', Route::currentRouteName());
}

//Help choose database config in development and product enviroment
function get_db_config(){
    if(getenv('IS_IN_HEROKU')){
        $url = parse_url(getenv("DATABASE_URL"));

        return $db_config = [
            'connection' => 'pgsql',
            'host' => $url["host"],
            'database' => substr($url["path"], 1),
            'username' =>$url["user"],
            'password' =>$url["pass"],
        ];
    }else{
        return $db_config = [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', '')
        ];
    }
}

//Get redis instance config info
function get_redis_config(){
    if(getenv('IS_IN_HEROKU')){
        $url = parse_url(getenv('REDIS_URL'));


        return [
            'url' => getenv('REDIS_URL'),
            'host' => $url['host'],
            'password' => $url['pass'],
            'port' => $url['port'],
        ];
    }else{
        return [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
        ];
    }
}

//get ngrok url, this url is a forwarding url which help the web(e.g. alipay callback) visit our page on homestead (we use this for development)
function ngrok_url($routeName, $parameters = []){
    //if we are in develop environment, and have set up the configuration for NGROK_URL in config/app.php file 
    if(app()->environment('local') && $url = config('app.ngrok_url')){
        //the 3rd param in route() stands for if it is absolute path
        return $url . route($routeName, $parameters, false);
    }

    //in other cases, just return the normal url
    return route($routeName, $parameters);
}

//Create a BigNumber for decimal calculation, default precision scale is 2
function big_number($number, $scale = 2){
    return new \Moontoast\Math\BigNumber($number, $scale);
}
