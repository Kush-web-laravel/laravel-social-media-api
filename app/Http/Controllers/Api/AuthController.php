<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /* -------- Register user function -------- */
    public function register(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:25|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:24|confirmed',
        ]);
    
        // Create user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);
    
        // Return response with user details
        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful!',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }
}
