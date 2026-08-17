@extends('site.layout')

@section('title', $heading)

@section('content')
    <h2>{{ $heading }}</h2>
    @if(!empty($lead))
        <p class="jk-cms-lead">{{ $lead }}</p>
    @endif

    @if(empty($cards))
        <p>{{ $empty }}</p>
    @else
        <div class="jk-row-list">
            @foreach($cards as $card)
                <article class="jk-row-card">
                    @if(!empty($card['url']))
                        <a class="jk-row-card__media" href="{{ $card['url'] }}" tabindex="-1" aria-hidden="true">
                    @else
                        <div class="jk-row-card__media">
                    @endif
                        @if(!empty($card['image']))
                            <img src="{{ $card['image'] }}" alt="" loading="lazy" decoding="async">
                        @else
                            <span class="jk-row-card__placeholder" aria-hidden="true"></span>
                        @endif
                    @if(!empty($card['url']))
                        </a>
                    @else
                        </div>
                    @endif
                    <div class="jk-row-card__body">
                        @if(!empty($card['meta']))
                            <div class="jk-row-card__meta">{{ $card['meta'] }}</div>
                        @endif
                        <h3 class="jk-row-card__title">
                            @if(!empty($card['url']))
                                <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
                            @else
                                {{ $card['title'] }}
                            @endif
                        </h3>
                        @if(!empty($card['body']))
                            <p class="jk-row-card__excerpt">{{ Str::limit(strip_tags($card['body']), 160) }}</p>
                        @endif
                        @if(!empty($card['url']) && !empty($card['cta']))
                            <a class="jk-row-card__more" href="{{ $card['url'] }}">{{ $card['cta'] }}</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
