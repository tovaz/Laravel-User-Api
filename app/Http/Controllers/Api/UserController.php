<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

/**
* @OA\Info(title="Inter Accounts", version="1.0")
*
* @OA\Server(url=API_HOST)
*/
class UserController extends Controller
{
    /**
    * @OA\Get(
    *     path="/api/users",
    *     summary="Mostrar usuarios",
    *     @OA\Response(
    *         response=200,
    *         description="Mostrar todos los usuarios."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function index()
    { 
        return User::all();
    }

    public function verify($user_id, Request $request) {
        if (!$request->hasValidSignature()) {
            return response()->json(["msg" => "Invalid/Expired url provided."], 401);
        }
    
        $user = User::findOrFail($user_id);
    
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
    
        return redirect()->to('/api/user/verifySuccess/'.$user_id);
    }
    
    public function resend() {
        if (auth()->user()->hasVerifiedEmail()) {
            return response()->json(["msg" => "Email already verified."], 400);
        }
    
        auth()->user()->sendEmailVerificationNotification();
    
        return response()->json(["msg" => "Email verification link sent on your email id"]);
    }

    public function verifySuccess($user_id){
        $user = User::findOrFail($user_id);
        
        return \View::make('user/verifySuccess')->with('user', $user);
    }

    
    
}
