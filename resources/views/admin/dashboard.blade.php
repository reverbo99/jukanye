@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="stats">
        <div class="stat"><span class="muted">Posts</span><strong>{{ $postsCount }}</strong></div>
        <div class="stat"><span class="muted">Published posts</span><strong>{{ $publishedPosts }}</strong></div>
        <div class="stat"><span class="muted">Nominees</span><strong>{{ $nomineesCount }}</strong></div>
        <div class="stat"><span class="muted">Schedule items</span><strong>{{ $scheduleCount }}</strong></div>
    </div>

    <div class="admin-card">
        <h2 style="margin-top:0;font-size:1.1rem;">Quick actions</h2>
        <div class="actions">
            <a class="btn btn-accent" href="{{ route('admin.posts.create') }}">New post</a>
            <a class="btn btn-primary" href="{{ route('admin.nominees.create') }}">New nominee</a>
            <a class="btn btn-primary" href="{{ route('admin.schedule.create') }}">New schedule item</a>
            <a class="btn btn-ghost" href="{{ url('/site') }}" target="_blank">View website</a>
        </div>
    </div>
@endsection
