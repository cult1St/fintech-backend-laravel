<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    //
   public function register(Request $request)

    {
        try {
            // Validate the user's entries
            $validated = $request->validate([
                "name" => "required|string|max:255",
                "email" => "required|email|string|max:255|unique:users",
                "phone" => "required|numeric|min:11|unique:users",
                "password" => "required|string|min:6|confirmed"
            ]);
        } catch (ValidationException $e) {
            // Return validation error messages
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors(),
            ], 422); // Unprocessable Entity status code
        }

        // Create the user using the Eloquent model
        $create_user = User::create([
            "name" => $validated['name'],
            "email" => $validated['email'],
            "phone" => $validated['phone'],
            "password" => Hash::make($validated['password'])
        ]);

        // Generate JWT token
        $token = JWTAuth::fromUser($create_user);
        event(new Registered($create_user));

        return response()->json([
            'token' => $token,
            'success' => true,
            'message' => 'User Registered Successfully. Confirm Verification On Email'
        ], 201);
    }
    public function login(){
        $credentials = request()->only("email", "password");
        $token = JWTAuth::attempt($credentials);
        if(!$token){
            return response()->json(['success' => false, "message" => "Invalid Credentials"], 401);
        }
        return response()->json(['success' => true, "message" => "Login Successful", "token" => $token], 201);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(["success" => true, 'message' => 'Successfully logged out']);
    }

    public function dashboard(Request $request){
        $user = auth()->user();
        return response()->json(["success" => true, 'message' => 'User Dashboard Loaded Successfully', "data" => $user]);
    }

}
