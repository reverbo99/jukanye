@extends('layouts.admin')

@section('title', 'Edit schedule item')
@section('heading', 'Edit schedule item')

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.schedule.update', $item) }}" class="form-grid">
            @csrf
            @method('PUT')
            @include('admin.schedule._form', ['item' => $item])
            <div class="actions">
                <button class="btn btn-accent" type="submit">Update</button>
                <a class="btn btn-ghost" href="{{ route('admin.schedule.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
