<?php
use \Illuminate\Http\Request;
/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->get('/profile',  ['middleware' => 'auth', function(){
    return Auth::user();
}]);
// $router->group(['namespace'=>'API\\v1'],function()use($router){
//     $router->post('/register', 'UserController@test');
// });
$router->post('/user', 'UserController@store');
$router->group(['middleware'=>'auth'], function()use($router){
    $router->get('/user', 'UserController@index');
});


$router->get('/testgan',  ['middleware' => 'authbasic', function(Request $request){
    return response()->json($request->user);
   
    //print_r($oauth_client);
}]);