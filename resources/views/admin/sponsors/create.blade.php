@extends('layouts.admin')
@section('title', 'New sponsor')
@section('heading', 'New sponsor')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.sponsors.store') }}" enctype="multipart/form-data" class="form-grid">
@csrf
@include('admin.sponsors._form')
<div class="actions">
<button class="btn btn-accent" type="submit">Save</button>
<a class="btn btn-ghost" href="{{ route('admin.sponsors.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection