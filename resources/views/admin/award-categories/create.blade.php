@extends('layouts.admin')

@section('title', 'New category')
@section('heading', 'New category')

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.award-categories.store') }}" class="form-grid">
            @csrf
            @include('admin.award-categories._form')
            <div class="actions">
                <button class="btn btn-accent" type="submit">Save</button>
                <a class="btn btn-ghost" href="{{ route('admin.award-categories.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
