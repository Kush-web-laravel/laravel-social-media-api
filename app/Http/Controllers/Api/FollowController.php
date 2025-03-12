<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Follow;
use App\Models\User;

class FollowController extends Controller
{
    public function follow(Request $request)
    {
        $request->validate([
            'following_id' => 'required|integer',
        ]);

        $user = User::where('id', $request->following_id)->first();

        if(!$user){
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ]);
        }

        $follower_id = auth()->user()->id;
        $following_id = $request->following_id;

        if($follower_id == $following_id){
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot follow yourself',
            ]);
        }

        $existInFollowing = Follow::where('follower_id', auth()->user()->id)
                                ->where('following_id', $request->following_id)
                                ->first();

        if($existInFollowing){
            return response()->json([
                'status' => 'error',
                'message' => 'You already follow the user',
                'data' => $existInFollowing,
            ]);
        }

        $privateProfile = User::where('id', $following_id)
                            ->where('is_public', 1)
                            ->first();
                            
        if($privateProfile){
           $follow = Follow::create([
            'is_accepted' => 0,
            'follower_id' => auth()->user()->id,
            'following_id' => $following_id,
           ]);

           return response()->json([
                'status' => 'success',
                'message' => 'Follow request has been sent.',
                'data' => $follow,
           ]);
        }else{
            $follow = Follow::create([
                'is_accepted' => 1,
                'follower_id' => auth()->user()->id,
                'following_id' => $following_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Following User.',
                'data' => $follow,
            ]);
        }
    }

    public function unfollow(Request $request)
    {
        $request->validate([
            'following_id' => 'required|integer'
        ]);

        $existInFollowing = Follow::where('follower_id', auth()->user()->id)
                                ->where('following_id', $request->following_id)
                                ->first();

        if(!$existInFollowing){
            return response()->json([
                'status' => 'error',
                'message' => 'You donot follow the user',
                'data' => [],
            ]);
        }

        $existInFollowing->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Unfollowed successfully.',
            'data' => $existInFollowing
        ]);

    }

    public function pendingRequests()
    {
        $user = User::where('id', auth()->user()->id)
                    ->where('is_public', 1)
                    ->first();
        // dd($user);
        if ($user) {
            // dd('in');
            $pendingRequests = Follow::where('following_id', auth()->user()->id)
                                     ->where('is_accepted', 0)
                                     ->get();
    
            if ($pendingRequests->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No pending connection requests.',
                    'pending_requests' => [],
                    'user_details' => [],
                ]);
            }
    
            $userDetails = $pendingRequests->map(function($request) {
                return User::where('id', $request->follower_id)->first();
            });
    
            return response()->json([
                'status' => 'success',
                'message' => 'List of connection requests.',
                'pending_requests' => $pendingRequests,
                'user_details' => $userDetails,
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'Your account is not private',
            ]);
        }
    }
    
    public function acceptRequest(Request $request)
    {

        $existInRequest = Follow::where('follower_id', $request->id)
                                ->where('following_id', auth()->user()->id)
                                ->where('is_accepted', 0)
                                ->first();

        //dd($existInRequest);

        if(!$existInRequest){
            return response()->json([
                'status' => 'error',
                'message' => 'Request does not exist',
            ]);
        }
        
        $existInRequest->update([
            'is_accepted' => 1
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Request accepted.',
            'data' => $existInRequest,
            'user_details' => User::where('id', $existInRequest->follower_id)->first(),
        ]);

        
    }
}

