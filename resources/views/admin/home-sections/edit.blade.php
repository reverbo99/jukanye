@extends('layouts.admin')
@section('title', 'Edit home section')
@section('heading', 'Edit home section')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.home-sections.update', $section) }}" class="form-grid">
@csrf @method('PUT')
@include('admin.home-sections._form', ['section' => $section])
<div class="actions">
<button class="btn btn-accent" type="submit">Update</button>
<a class="btn btn-ghost" href="{{ route('admin.home-sections.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection