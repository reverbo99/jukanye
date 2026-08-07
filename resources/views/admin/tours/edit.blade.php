@extends('layouts.admin')
@section('title', 'Edit tour')
@section('heading', 'Edit tour')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.tours.update', $tour) }}" enctype="multipart/form-data" class="form-grid">
@csrf @method('PUT')
@include('admin.tours._form', ['tour' => $tour])
<div class="actions">
<button class="btn btn-accent" type="submit">Update</button>
<a class="btn btn-ghost" href="{{ route('admin.tours.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection