<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    //
    public function storeData(Request $request)
    {
        $post = Post::withTrashed()->find($request->post_id);
        if (!$post || $post->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post does not exist or has been deleted',
            ], 404);
        }

        $request->validate([
            'comment_text' => 'required|string|max:1000|min:3',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->user()->id,
            'post_id' => $request->post_id,
            'comment_text' => $request->comment_text,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully.',
            'comment' => $comment,
            'commented_by' => $comment->user->name??'Unknown User',
        ]);

    }

    public function updateData(Request $request)
    {
        $request->validate([
            'comment_text' => 'required|string|max:1000|min:3',
        ]);
    
        $comment = Comment::where('id', $request->id)
                          ->where('user_id', auth()->user()->id) // Ensure user owns the comment
                          ->first();
    
        if (!$comment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comment does not exist or you are not authorized to edit it.',
            ], 403);
        }
    
        $comment->update([
            'comment_text' => $request->comment_text,
        ]);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Comment updated successfully.',
            'comment' => $comment,
        ]);
    }

    public function deleteData(Request $request)
    {
        $comment = Comment::withTrashed() // Include soft deleted records
                        ->where('id', $request->id)
                        ->where('user_id', auth()->user()->id) // Ensure user owns the comment
                        ->first();

        if (!$comment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comment not found or already deleted.',
            ], 404);
        }

        if ($comment->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This comment has already been deleted.',
            ], 400);
        }

        $comment->delete(); // Soft delete the comment

        return response()->json([
            'status' => 'success',
            'message' => 'Comment deleted successfully.',
        ], 200);
    }

    public function commentReply(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:comments,id',
            'comment_text' => 'required|string|max:1000|min:3',
        ]);

        $parentComment = Comment::withTrashed()->find($request->parent_id);

        // Ensure the parent comment exists and is not deleted
        if (!$parentComment || $parentComment->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The comment you are replying to does not exist or has been deleted.',
            ], 404);
        }

        $reply = Comment::create([
            'user_id' => auth()->user()->id,
            'post_id' => $parentComment->post_id, // Get post_id from parent comment
            'comment_text' => $request->comment_text,
            'parent_id' => $request->parent_id, // Link to the parent comment
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Reply added successfully.',
            'reply' => $reply,
            'replied_by' => auth()->user()->name,
        ], 201);
    }

}
