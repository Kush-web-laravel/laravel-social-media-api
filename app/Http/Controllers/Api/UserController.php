<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //

    /*** ---------------------Setup Profile Function ------------------ */
    public function setupProfile(Request $request)
    {
        // Validate input fields
        $validatedData = $request->validate([
            'profile_picture' => 'nullable|image|mimes:png,jpeg,jpg|max:2048',
            'bio' => 'string|max:200',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'location' => 'required|string'
        ]);
    
        $user = auth()->user();
        $userId = $user->id;
    
        // Define user's profile picture directory
        $destinationPath = public_path('profile_pictures/' . $userId);
    
        // Ensure the directory exists
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
    
        // Handle profile picture update
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if it exists and is not default
            if ($user->profile_picture && file_exists(public_path($user->profile_picture)) && basename($user->profile_picture) !== 'default.jpg') {
                unlink(public_path($user->profile_picture));
            }
    
            // Upload new profile picture
            $fileName = time() . '.' . $request->file('profile_picture')->getClientOriginalExtension();
            $request->file('profile_picture')->move($destinationPath, $fileName);
            $filePath = 'profile_pictures/' . $userId . '/' . $fileName;
        } else {
            // Keep existing profile picture, or copy default if it's not already set
            if (!$user->profile_picture || !file_exists(public_path($user->profile_picture))) {
                $fileName = 'default.jpg';
                if (!file_exists($destinationPath . '/' . $fileName)) {
                    copy(public_path('profile_pictures/default.jpg'), $destinationPath . '/' . $fileName);
                }
                $filePath = 'profile_pictures/' . $userId . '/' . $fileName;
            } else {
                $filePath = $user->profile_picture; // Keep existing image
            }
        }
    
        // Update user profile in the database
        $user->update([
            'profile_picture' => $filePath,
            'bio' => $validatedData['bio'],
            'date_of_birth' => $validatedData['date_of_birth'],
            'gender' => $validatedData['gender'],
            'location' => $validatedData['location']
        ]);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => $user,
            'profile_picture_name' => basename($filePath),
            'profile_picture_link' => asset($filePath) // Generate accessible URL
        ]);
    }
    
    
}
