<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\ApiMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 50);

        $posts = Post::published()
            ->latest('published_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $posts->getCollection()->map(fn (Post $post) => $this->transform($post))->values(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return response()->json([
            'data' => $this->transform($post, true),
            'meta' => (object) [],
        ]);
    }

    private function transform(Post $post, bool $full = false): array
    {
        $data = [
            'id' => $post->id,
            'title_en' => $post->title_en,
            'title_sw' => $post->title_sw,
            'slug' => $post->slug,
            'excerpt_en' => $post->excerpt_en,
            'excerpt_sw' => $post->excerpt_sw,
            'cover_image' => ApiMedia::url($post->cover_image),
            'published_at' => optional($post->published_at)?->toIso8601String(),
        ];

        if ($full) {
            $data['body_en'] = $post->body_en;
            $data['body_sw'] = $post->body_sw;
        }

        return $data;
    }
}
