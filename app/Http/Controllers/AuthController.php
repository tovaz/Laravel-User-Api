<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;

/** 
*
*  @OA\Server(url=API_HOST)
*  @OA\SecurityScheme(
*    securityScheme="bearerAuth",
*        type="http",
*        scheme="bearer",
*        bearerFormat="JWT"
*    ),
*/
class AuthController extends Controller
{
    /**
    * @OA\Post(
    *     path="/api/auth/signup",
    *     @OA\Parameter(
    *         name="name", in="query", required=true,
    *         @OA\Schema( type="string" ) 
    *      ),
    *     @OA\Parameter(
    *         name="email", in="query", required=true,
    *         @OA\Schema( type="string" ) 
    *      ),
    *     @OA\Parameter(
    *         name="password", in="query", required=true,
    *         @OA\Schema( type="string" ) 
    *      ),
    *
    *     summary="Registrar usuario",
    *     @OA\Response(
    *          response=200,
    *          description="Success",
    *          @OA\MediaType(
    *              mediaType="application/json",
    *          )
    *      ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $user->save();
        //$user->id = 1;
        $user->sendEmailVerificationNotification();
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    /**
    * @OA\Post(
    *     path="/api/auth/login",
    *     @OA\Parameter(
    *         name="email", in="query", required=true,
    *         @OA\Schema( type="string" ) 
    *      ),
    *     @OA\Parameter(
    *         name="password", in="query", required=true,
    *         @OA\Schema( type="string" ) 
    *      ),
    *     @OA\Parameter(
    *         name="remember_me", in="query", required=true,
    *         @OA\Schema( type="string" ) 
    *      ),
    *     summary="Logear usuario",
    *     @OA\Response(
    *          response=200,
    *          description="Success",
    *          @OA\MediaType(
    *              mediaType="application/json",
    *          )
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();
        if ($user->email_verified_at == null)
            return response()->json([
                'message' => 'Email no verificated'
            ], 401);
            
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    /**
    * @OA\Get(
    *     path="/api/auth/logout",
    *     summary="Cerrar sesion",
    *     @OA\Response(
    *          response=200,
    *          description="Success",
    *          @OA\MediaType(
    *              mediaType="application/json",
    *          )
    *      ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
    * @OA\Get(
    *     path="/api/auth/user",
    *     summary="Mostrar usuarios",
    *     @OA\Response(
    *          response=200,
    *          description="Success",
    *          @OA\MediaType(
    *              mediaType="application/json",
    *          )
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
