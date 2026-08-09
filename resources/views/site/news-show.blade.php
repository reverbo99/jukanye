@extends('site.layout')

@section('title', $postTitle)

@section('content')
    <h1>{{ $postTitle }}</h1>
    @if($dateLabel)
        <p class="jk-lead">{{ $dateLabel }}</p>
    @endif
    @if($cover)
        <p><img src="{{ $cover }}" alt="{{ $postTitle }}" style="max-width:100%;border-radius:.55rem"></p>
    @endif
    <div class="jk-list">
        <article>
            @if($excerpt)
                <p><strong>{{ $excerpt }}</strong></p>
            @endif
            @if($body)
                <p>{!! nl2br(e($body)) !!}</p>
            @endif
        </article>
    </div>
    <p><a href="{{ $backUrl }}">← {{ $locale === 'sw' ? 'Habari zote' : 'All news' }}</a></p>
@endsection
