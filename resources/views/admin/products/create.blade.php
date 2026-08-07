@extends('layouts.admin')
@section('title', 'New product')
@section('heading', 'New product')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="form-grid">
@csrf
@include('admin.products._form')
<div class="actions">
<button class="btn btn-accent" type="submit">Save</button>
<a class="btn btn-ghost" href="{{ route('admin.products.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection