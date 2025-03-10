<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;

class PostController extends Controller
{
    //
    public function createPost(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('image')) {
            $userId = Auth::id();
            $fileName = time() . '.' . $request->file('image')->getClientOriginalExtension();
            $destinationPath = public_path('post_images/' . $userId);

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $request->file('image')->move($destinationPath, $fileName);
            $filePath = 'post_images/' . $userId . '/' . $fileName;
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'image' => $filePath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Post created successfully', 
            'post' => $post,
            'post_image_name' => basename($post->image),
            'post_image_link' => url($post->image),
        ], 201);
    }

    public function postDetails(Request $request, $id)
    {
        $post = Post::find($id);
        
        
        if($post){
            return response()->json([
                'status' => 'success',
                'message' => 'Post details have been fetched',
                'post' => $post,
                'post_image_name' => basename($post->image),
                'post_image_link' => url($post->image),
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Post does not exist',
                'post' => [],
            ]);
        }
    }

    public function listPosts()
    {
        $posts = Post::where('user_id', auth()->user()->id)->get();

       
        if($posts){
            $formattedPosts = $posts->map(function ($post) {
                $formattedPost = $post->toArray();
        
                if ($post->image != null) {
                    $formattedPost['post_image_name'] = basename($post->image);
                    $formattedPost['post_image_link'] = url($post->image);
                }
        
                return $formattedPost;
            });
        
            return response()->json([
                'status' => 'success',
                'message' => 'List of Posts..',
                'post' => $formattedPosts
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'No Posts..',
                'post' => []
            ]);
        }
    }

    public function updatePost(Request $request, $id)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'content' => 'nullable|string|max:1000',
        ]);

        $post = Post::findOrFail($id); // Get the post or return 404

        if ($request->hasFile('image')) {
            // Delete existing image if it exists
            if ($post->image && file_exists(public_path($post->image))) {
                unlink(public_path($post->image));
            }

            // Upload new image
            $userId = auth()->user()->id;
            $fileName = time() . '.' . $request->file('image')->getClientOriginalExtension();
            $destinationPath = public_path('post_images/' . $userId);

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $request->file('image')->move($destinationPath, $fileName);
            $post->image = 'post_images/' . $userId . '/' . $fileName;
        }

        // Update content if provided
        if ($request->has('content')) {
            $post->content = $request->content;
        }

        $post->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Post updated successfully',
            'post' => $post,
            'post_image_name' => basename($post->image),
            'post_image_link' => url($post->image),
        ]);
    }

    public function deletePost($id)
    {
        $post = Post::find($id);

        if($post){
            if ($post->image && file_exists(public_path($post->image))) {
                unlink(public_path($post->image));
            }

            $post->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Post deleted successfully..',
                'post' => $post,
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Post does not exist',
                'post' => [],
            ]);
        }
    }

}
