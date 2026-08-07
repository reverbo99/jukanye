@extends('layouts.admin')
@section('title', 'Edit product')
@section('heading', 'Edit product')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="form-grid">
@csrf @method('PUT')
@include('admin.products._form', ['product' => $product])
<div class="actions">
<button class="btn btn-accent" type="submit">Update</button>
<a class="btn btn-ghost" href="{{ route('admin.products.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection