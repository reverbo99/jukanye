@extends('layouts.admin')

@section('title', $title)
@section('heading', $title)

@section('content')
    <div class="admin-card">
        <h1 style="margin-top:0;font-size:1.25rem;">{{ $title }}</h1>
        <p class="muted">Coming soon — this section will be available in a later phase. Content for this public page will be managed here and exposed via the same API used by the mobile app.</p>
    </div>
@endsection
