@extends('site.layout')

@section('title', $postTitle)

@section('content')
    <h2>{{ $postTitle }}</h2>
    @if($dateLabel)
        <p class="jk-cms-meta">{{ $dateLabel }}</p>
    @endif
    @if($cover)
        <p><img src="{{ $cover }}" alt="{{ $postTitle }}" style="max-width:100%;border-radius:14px"></p>
    @endif
    @if($excerpt)
        <p><strong>{{ $excerpt }}</strong></p>
    @endif
    @if($body)
        <p>{!! nl2br(e($body)) !!}</p>
    @endif
    <p><a class="jk-more" href="{{ $backUrl }}">← {{ $locale === 'sw' ? 'Habari zote' : 'All news' }}</a></p>
@endsection
