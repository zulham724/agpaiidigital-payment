<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BasicAuthenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    // protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct()
    {
        // $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $basicAuthHeader = $request->header('Authorization');
        if(!empty($basicAuthHeader)){
            $exp = explode(' ',$basicAuthHeader);
            if(isset($exp[1])){
                $oauth_client = base64_decode($exp[1]);
                $exp2 = explode(':',$oauth_client);
                if(count($exp2)==2){
                    $sql="SELECT * FROM `oauth_clients` WHERE id=? AND `secret`=?";
                    $run_sql = app('db')->select($sql, [$exp2[0], $exp2[1]]);
                    if(count($run_sql)){
                        $request->user = $run_sql[0];
                        return $next($request);
                    }
                }else{
                    return response('Unauthorized.', 401);
                }
            }else{
                return response('Unauthorized.', 401);
            }
        }
        return response('Unauthorized.', 401);
        // return $next($request);
    }
}
