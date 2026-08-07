@extends('layouts.admin')
@section('title', 'Edit person')
@section('heading', 'Edit person')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.people.update', $person) }}" enctype="multipart/form-data" class="form-grid">
@csrf @method('PUT')
@include('admin.people._form', ['person' => $person])
<div class="actions">
<button class="btn btn-accent" type="submit">Update</button>
<a class="btn btn-ghost" href="{{ route('admin.people.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection