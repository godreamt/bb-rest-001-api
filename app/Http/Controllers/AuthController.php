<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;

class AuthController extends Controller
{
    public function authenticate(Request $request){
        // assuming that the email or username is passed through a 'login' parameter
        $username = $request->input('username');
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobileNumber';
        $request->merge([$field => $username]);
        $credentials = $request->only($field, 'password');
        
        try {
           if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['invalid credentials'], 400);
           }
        } catch (JWTAuthException $e) {
            return response()->json(['Failed to authenticate'], 400);
        }
        return response()->json(compact('token'));
    }

    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'profilePic' => 'nullable',
            'email' => 'required|string|email|max:255|unique:users',
            'mobileNumber' => 'required|string|min:10',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'firstName' => $request->get('firstName'),
            'lastName' => $request->get('lastName'),
            'profilePic' => '',
            'email' => $request->get('email'),
            'mobileNumber' => $request->get('mobileNumber'),
            'password' => Hash::make($request->get('password')),
            'roles' => 'Customer'
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public function getAuthenticatedUser()
        {
            try {
                    if (! $user = JWTAuth::parseToken()->authenticate()) {
                            return response()->json(['user_not_found'], 404);
                    }
            } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                    return response()->json(['token_expired'], $e->getStatusCode());
            } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                    return response()->json(['token_invalid'], $e->getStatusCode());
            } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
                    return response()->json(['token_absent'], $e->getStatusCode());
            }
            return response()->json(compact('user'));
        }
}
