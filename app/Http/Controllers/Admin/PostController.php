<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Media;
use App\Models\Post;
use App\Services\DeepLTranslateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()->latest()->paginate(15);

        return view('admin.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.posts.create');
    }

    public function store(StorePostRequest $request, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $translator->fillMissingPairs($request->validated(), [
            ['title_sw', 'title_en'],
            ['excerpt_sw', 'excerpt_en'],
            ['body_sw', 'body_en'],
        ]);
        $data['slug'] = $data['slug'] ?: Post::makeSlug($data['title_en'] ?: $data['title_sw']);
        $data['published_at'] = $data['status'] === 'published'
            ? ($data['published_at'] ?? now())
            : null;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeImage($request->file('cover_image'));
        } else {
            unset($data['cover_image']);
        }

        Post::create($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $translator->fillMissingPairs($request->validated(), [
            ['title_sw', 'title_en'],
            ['excerpt_sw', 'excerpt_en'],
            ['body_sw', 'body_en'],
        ]);
        $data['slug'] = $data['slug'] ?: $post->slug;
        $data['published_at'] = $data['status'] === 'published'
            ? ($data['published_at'] ?? $post->published_at ?? now())
            : null;

        if ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $data['cover_image'] = $this->storeImage($request->file('cover_image'));
        } else {
            unset($data['cover_image']);
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted.');
    }

    private function storeImage(UploadedFile $file): string
    {
        $path = $file->store('posts', 'public');

        Media::create([
            'path' => $path,
            'disk' => 'public',
            'mime' => $file->getClientMimeType(),
            'alt' => $file->getClientOriginalName(),
        ]);

        return $path;
    }
}
