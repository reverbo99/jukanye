@extends('layouts.admin')
@section('title', 'New team member')
@section('heading', 'New team member')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.team.store') }}" enctype="multipart/form-data" class="form-grid">
@csrf
@include('admin.team._form')
<div class="actions">
<button class="btn btn-accent" type="submit">Save</button>
<a class="btn btn-ghost" href="{{ route('admin.team.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection