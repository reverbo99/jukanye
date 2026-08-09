@extends('site.layout')

@section('title', $heading)

@section('content')
    <h1>{{ $heading }}</h1>
    @if(!empty($lead))
        <p class="jk-lead">{{ $lead }}</p>
    @endif

    @if(empty($cards))
        <p class="jk-empty">{{ $empty }}</p>
    @else
        <div class="jk-grid">
            @foreach($cards as $card)
                <div class="jk-card">
                    @if(!empty($card['image']))
                        <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}">
                    @endif
                    <h3>
                        @if(!empty($card['url']))
                            <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
                        @else
                            {{ $card['title'] }}
                        @endif
                    </h3>
                    @if(!empty($card['meta']))
                        <div class="jk-meta">{{ $card['meta'] }}</div>
                    @endif
                    @if(!empty($card['body']))
                        <p>{!! nl2br(e($card['body'])) !!}</p>
                    @endif
                    @if(!empty($card['url']) && !empty($card['cta']))
                        <p style="margin-top:.75rem"><a class="jk-more" href="{{ $card['url'] }}">{{ $card['cta'] }}</a></p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@endsection
