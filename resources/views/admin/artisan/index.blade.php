@extends('layouts.admin')
@section('title', 'Artisan Commands')
@section('heading', 'Artisan Commands')
@section('content')
<div class="admin-card" style="margin-bottom:1rem;">
    <p class="muted" style="margin-top:0;">Run allowed Laravel Artisan commands from the admin panel. Use with care — migrations change the database.</p>
    <div class="actions" style="margin-top:1rem;">
        <form method="POST" action="{{ route('admin.artisan.migrate') }}" onsubmit="return confirm('Run php artisan migrate?')">
            @csrf
            <button class="btn btn-accent" type="submit">migrate</button>
        </form>
        <form method="POST" action="{{ route('admin.artisan.migrate-force') }}" onsubmit="return confirm('Run php artisan migrate --force?\n\nThis runs migrations without confirmation (needed in production).')">
            @csrf
            <button class="btn btn-danger" type="submit">migrate --force</button>
        </form>
    </div>
</div>

@if (session('artisan_output'))
<div class="admin-card">
    <h2 style="margin:0 0 0.75rem;font-size:1rem;">Command output</h2>
    <pre style="margin:0;padding:1rem;background:#14221f;color:#d7e3df;border-radius:0.4rem;overflow:auto;font-size:0.85rem;line-height:1.45;white-space:pre-wrap;">{{ session('artisan_output') }}</pre>
</div>
@endif
@endsection
