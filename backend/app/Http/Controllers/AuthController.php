<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Nette\Schema\Expect;
use Exception;

class AuthController extends Controller
{
    public function login(Request $request){
        try {
            if(!Auth::guard('web')->attempt($request->only('email', 'password'))){
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }
            
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'massage' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
