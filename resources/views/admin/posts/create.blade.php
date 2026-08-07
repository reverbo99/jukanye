@extends('layouts.admin')

@section('title', 'New post')
@section('heading', 'New post')

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" class="form-grid">
            @csrf
            @include('admin.posts._form')
            <div class="actions">
                <button class="btn btn-accent" type="submit">Save</button>
                <a class="btn btn-ghost" href="{{ route('admin.posts.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
