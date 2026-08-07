@extends('layouts.admin')

@section('title', 'Edit nominee')
@section('heading', 'Edit nominee')

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.nominees.update', $nominee) }}" enctype="multipart/form-data" class="form-grid">
            @csrf
            @method('PUT')
            @include('admin.nominees._form', ['nominee' => $nominee])
            <div class="actions">
                <button class="btn btn-accent" type="submit">Update</button>
                <a class="btn btn-ghost" href="{{ route('admin.nominees.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
