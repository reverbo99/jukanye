@extends('layouts.admin')
@section('title', 'Edit media')
@section('heading', 'Edit media')
@section('content')
<div class="page-head"><h1>Edit media item</h1></div>
<div class="admin-card">
<form method="POST" action="{{ route('admin.site-media.update', $item) }}" enctype="multipart/form-data">
@csrf @method('PUT')
@include('admin.site-media._form', ['item' => $item])
<button class="btn btn-accent" type="submit">Update</button>
<a class="btn btn-ghost" href="{{ route('admin.site-media.index', ['slot' => $item->slot]) }}">Cancel</a>
</form>
</div>
@endsection
