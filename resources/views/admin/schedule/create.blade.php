@extends('layouts.admin')

@section('title', 'New schedule item')
@section('heading', 'New schedule item')

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.schedule.store') }}" class="form-grid">
            @csrf
            @include('admin.schedule._form')
            <div class="actions">
                <button class="btn btn-accent" type="submit">Save</button>
                <a class="btn btn-ghost" href="{{ route('admin.schedule.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
