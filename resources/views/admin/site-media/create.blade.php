@extends('layouts.admin')
@section('title', 'Add media')
@section('heading', 'Add media')
@section('content')
<div class="page-head"><h1>Add media item</h1></div>
<div class="admin-card">
<form method="POST" action="{{ route('admin.site-media.store') }}" enctype="multipart/form-data">
@csrf
@include('admin.site-media._form')
<button class="btn btn-accent" type="submit">Save</button>
<a class="btn btn-ghost" href="{{ route('admin.site-media.index') }}">Cancel</a>
</form>
</div>
@endsection
