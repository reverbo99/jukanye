@extends('layouts.admin')
@section('title', 'Edit sponsor')
@section('heading', 'Edit sponsor')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.sponsors.update', $sponsor) }}" enctype="multipart/form-data" class="form-grid">
@csrf @method('PUT')
@include('admin.sponsors._form', ['sponsor' => $sponsor])
<div class="actions">
<button class="btn btn-accent" type="submit">Update</button>
<a class="btn btn-ghost" href="{{ route('admin.sponsors.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection