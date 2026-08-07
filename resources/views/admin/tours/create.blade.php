@extends('layouts.admin')
@section('title', 'New tour')
@section('heading', 'New tour')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.tours.store') }}" enctype="multipart/form-data" class="form-grid">
@csrf
@include('admin.tours._form')
<div class="actions">
<button class="btn btn-accent" type="submit">Save</button>
<a class="btn btn-ghost" href="{{ route('admin.tours.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection