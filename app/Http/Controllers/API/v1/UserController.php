<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
class UserController extends Controller
{
    public $auth;
    public function __construct(Request $request)
    {
        $this->middleware('authbasic');
        $this->auth=$request->user;
    }
    public function store(Request $request)
    {
        $user = new User;
        //$user->fill($request)
        return $user;
        // $user->fill
        // return response()->json($this->test->user);
    }
    public function test(Request $request)
    {
       // $user = User::findOrFail(1);
        return $request->inputs;
        // $user->fill
        // return response()->json($this->test->user);
    }


}
