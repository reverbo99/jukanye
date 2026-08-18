@extends('layouts.admin')
@section('title', 'New person')
@section('heading', 'New person')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.people.store') }}" enctype="multipart/form-data" class="form-grid">
@csrf
@include('admin.people._form')
<div class="actions">
<button class="btn btn-accent" type="submit">Save</button>
<a class="btn btn-ghost" href="{{ route('admin.people.index', ['type' => $defaultType ?? 'speaker']) }}">Cancel</a>
</div>
</form>
</div>
@endsection