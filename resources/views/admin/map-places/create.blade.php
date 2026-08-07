@extends('layouts.admin')
@section('title', 'New map place')
@section('heading', 'New map place')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.map-places.store') }}" class="form-grid">
@csrf
@include('admin.map-places._form')
<div class="actions">
<button class="btn btn-accent" type="submit">Save</button>
<a class="btn btn-ghost" href="{{ route('admin.map-places.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection