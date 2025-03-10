<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\Posts\ShowPostRequest;
use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PaginateRequest $request)
    {
        $perpage = $request->input('limit') ?? 10;
        $posts = Post::paginate($perpage);

        // return PostResource::collection($posts);
        return new PostCollection($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request, User $user): JsonResponse
    {
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        $filled  = $request->validated();

        $filled['created_at'] = Carbon::now()->timestamp;
        $filled['updated_at'] = Carbon::now()->timestamp;
        $filled['user_id'] = $user->id;

        Post::create($filled);

        return response()->json(['message' => 'Create posts successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPostRequest $request)
    {
        try {
            $validated = $request->validated();
            $post = Post::where("id", $validated['id'])->firstOrFail();

            return new PostResource($post);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        // Gate::authorize('update-post', $post);
        // $response = Gate::inspect('update-post', $post);

        // Using policy
        // $response = Gate::inspect('update', $post);

        // if ($response->allowed()) {
        //     $data = $request->validated();
        //     unset($data['id']);

        //     $post->update($data);

        //     return response()->json(['message' => 'Update successfully']);
        // } else {
        //     return response()->json(['message' => $response->message()], 403);
        // }

        $data = $request->validated();
        unset($data['id']);

        $post->update($data);

        return response()->json(['message' => 'Update successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            // Gate::authorize('delete-post', $post);
            // $response = Gate::inspect('delete-post', $post);

            // if ($response->allowed()) {
            //     $currentPost = Post::where('id', $post->id)->firstOrFail();
            //     $currentPost->delete();

            //     return response()->json(['message' => 'Deleted post']);
            // }

            // throw new \Exception($response->message());

            $currentPost = Post::where('id', $post->id)->firstOrFail();
            $currentPost->delete();

            return response()->json(['message' => 'Deleted post']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message' => $th->getMessage()], 403);
        }
    }
}
