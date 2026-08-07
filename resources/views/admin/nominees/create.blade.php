@extends('layouts.admin')

@section('title', 'New nominee')
@section('heading', 'New nominee')

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.nominees.store') }}" enctype="multipart/form-data" class="form-grid">
            @csrf
            @include('admin.nominees._form')
            <div class="actions">
                <button class="btn btn-accent" type="submit">Save</button>
                <a class="btn btn-ghost" href="{{ route('admin.nominees.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
