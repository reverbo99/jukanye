@extends('layouts.admin')

@section('title', 'Edit post')
@section('heading', 'Edit post')

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data" class="form-grid">
            @csrf
            @method('PUT')
            @include('admin.posts._form', ['post' => $post])
            <div class="actions">
                <button class="btn btn-accent" type="submit">Update</button>
                <a class="btn btn-ghost" href="{{ route('admin.posts.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
