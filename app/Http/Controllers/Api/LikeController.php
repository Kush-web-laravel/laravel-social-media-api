<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Post;

class LikeController extends Controller
{
    //
    public function storeLikeData(Request $request)
    {
        $post = Post::withTrashed()->find($request->post_id);
        if (!$post || $post->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post does not exist or has been deleted',
            ], 404);
        }

        $existingLike = Like::withTrashed()
                        ->where('user_id', auth()->user()->id)
                        ->where('post_id', $request->post_id)
                        ->first();
    
        if ($existingLike) {
            if ($existingLike->trashed()) {
                // If the like exists but was soft-deleted, restore it
                $existingLike->restore();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Post liked again successfully',
                    'like' => $existingLike,
                    'liked_by' => $existingLike->user->name??'Unknown User',
                ], 200);
            }
    
            return response()->json([
                'status' => 'error',
                'message' => 'You have already liked the post',
            ], 409);
        }
    
        // If no like exists, create a new one
        $like = Like::create([
            'user_id' => auth()->user()->id,
            'post_id' => $request->post_id,
        ]);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Post liked successfully',
            'like' => $like,
            'liked_by' => $like->user->name??'Unknown User',
        ], 201);
    }
    

    public function unlikePost(Request $request)
    {
        $post = Post::withTrashed()->find($request->post_id);
        if (!$post || $post->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post does not exist or has been deleted',
            ], 404);
        }
        
        $existingLike = Like::where('user_id', auth()->user()->id)
                            ->where('post_id', $request->post_id)
                            ->first();

        if (!$existingLike) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have not liked this post',
            ], 404);
        }

        // Soft delete the like
        $existingLike->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Post unliked successfully.',
        ], 200);
    }
}
