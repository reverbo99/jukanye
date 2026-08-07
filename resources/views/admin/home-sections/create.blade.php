@extends('layouts.admin')
@section('title', 'New home section')
@section('heading', 'New home section')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.home-sections.store') }}" class="form-grid">
@csrf
@include('admin.home-sections._form')
<div class="actions">
<button class="btn btn-accent" type="submit">Save</button>
<a class="btn btn-ghost" href="{{ route('admin.home-sections.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection