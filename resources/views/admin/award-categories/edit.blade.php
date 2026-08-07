@extends('layouts.admin')

@section('title', 'Edit category')
@section('heading', 'Edit category')

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.award-categories.update', $category) }}" class="form-grid">
            @csrf
            @method('PUT')
            @include('admin.award-categories._form', ['category' => $category])
            <div class="actions">
                <button class="btn btn-accent" type="submit">Update</button>
                <a class="btn btn-ghost" href="{{ route('admin.award-categories.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
