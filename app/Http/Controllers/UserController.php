<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    // public $auth;
    public function __construct()
    {
        // $this->middleware('authbasic');
        // $this->auth=$request->user;
    }
    public function index(){
        return auth('api')->user();
    }
    public function store(Request $request)
    {
        //return $request->all();
        $this->validate($request, [
            'name' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users',
            'password'=>'required|confirmed'
        ]);
        $user = new User;
        $user->fill($request->all());
        $user->password = Hash::make($user->password);
        $user->save();
        return $user;
        // $user->fill
    }
    public function test(Request $request)
    {
       // $user = User::findOrFail(1);
        return $request->inputs;
        // $user->fill
        // return response()->json($this->test->user);
    }


}
