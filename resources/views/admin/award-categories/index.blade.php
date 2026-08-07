@extends('layouts.admin')

@section('title', 'Award Categories')
@section('heading', 'Award Categories')

@section('content')
    <div class="page-head">
        <h1>Award Categories</h1>
        <a class="btn btn-accent" href="{{ route('admin.award-categories.create') }}">Add category</a>
    </div>
    <div class="admin-card">
        <table class="admin-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Order</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>
                        <strong>{{ $category->name_en }}</strong>
                        <div class="muted">{{ $category->name_sw }}</div>
                    </td>
                    <td>{{ $category->slug }}</td>
                    <td>{{ $category->sort_order }}</td>
                    <td class="actions">
                        <a class="btn btn-ghost" href="{{ route('admin.award-categories.edit', $category) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.award-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No categories yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:1rem;">{{ $categories->links() }}</div>
    </div>
@endsection
