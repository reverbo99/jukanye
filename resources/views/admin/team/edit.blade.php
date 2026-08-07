@extends('layouts.admin')
@section('title', 'Edit team member')
@section('heading', 'Edit team member')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.team.update', $member) }}" enctype="multipart/form-data" class="form-grid">
@csrf @method('PUT')
@include('admin.team._form', ['member' => $member])
<div class="actions">
<button class="btn btn-accent" type="submit">Update</button>
<a class="btn btn-ghost" href="{{ route('admin.team.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection