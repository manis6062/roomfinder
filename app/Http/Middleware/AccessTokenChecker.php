<?php

namespace App\Http\Middleware;

use Closure;
use App\Library\RoomFinderFunctions;
class AccessTokenChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $access_token = $request->header('access-token');
        $user_id = false; 
        if($request->user_id){
            $user_id = $request->user_id; 
        }
        if(isset($access_token)){
            if(!RoomFinderFunctions::checkToken($access_token,$user_id)){
                return \Response::json(array(  'error' => true,   'message' => 'Invalid access token' ));
            }          
        }else{
            return \Response::json(array(  'error' => true,   'message' => 'Access token not found' ));
        }
        return $next($request);
    }
}
