@extends('layouts.admin')

@section('title', 'Posts')
@section('heading', 'Posts / Habari')

@section('content')
    <div class="page-head">
        <h1>Posts</h1>
        <a class="btn btn-accent" href="{{ route('admin.posts.create') }}">Add post</a>
    </div>
    <div class="admin-card">
        <table class="admin-table">
            <thead>
            <tr>
                <th>Title (EN)</th>
                <th>Status</th>
                <th>Published</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($posts as $post)
                <tr>
                    <td>
                        <strong>{{ $post->title_en }}</strong>
                        <div class="muted">{{ $post->slug }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $post->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $post->status }}</span>
                    </td>
                    <td>{{ optional($post->published_at)?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="actions">
                        <a class="btn btn-ghost" href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No posts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:1rem;">{{ $posts->links() }}</div>
    </div>
@endsection
