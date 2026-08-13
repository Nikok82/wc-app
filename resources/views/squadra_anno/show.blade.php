@extends('layouts.app')

@section('title', $titolo)

@section('content')
    @include('partials.banner-squadra', ['flag' => $flag, 'titolo' => $titolo])

    <div class="tabs tabs-icone">
        <button class="tab-btn active" data-section="info"      title="Info"      aria-label="Info">{!! \App\Support\Icons::svg('info') !!}</button>
        <button class="tab-btn"        data-section="partite"   title="Partite"   aria-label="Partite">{!! \App\Support\Icons::svg('scoreboard') !!}</button>
        <button class="tab-btn"        data-section="convocati" title="Convocati" aria-label="Convocati">{!! \App\Support\Icons::svg('team') !!}</button>
        <button class="tab-btn"        data-section="maglie"    title="Maglie"    aria-label="Maglie">@include('partials.icona-maglia')</button>
        <button class="tab-btn"        data-section="record"    title="Record"    aria-label="Record">{!! \App\Support\Icons::svg('trophy-2') !!}</button>
    </div>

    <div id="tab-content" class="luce-bordo">Caricamento…</div>

    <div class="footer-nav">
        @if ($prev)
            <a href="{{ route('squadra_anno.show', ['code' => $code, 'year' => $prev]) }}">‹ {{ $prev }}</a>
        @else
            <span></span>
        @endif

        <a href="{{ route('squadra.show', $code) }}">Scheda squadra</a>

        @if ($next)
            <a href="{{ route('squadra_anno.show', ['code' => $code, 'year' => $next]) }}">{{ $next }} ›</a>
        @else
            <span></span>
        @endif
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        WC.initTabs({
            base: @json(url('/squadra')),
            id: @json($code.'-'.$year),
            buttons: '.tab-btn[data-section]',
            content: '#tab-content',
            sezioneDefault: 'info'
        });
    });
    </script>
@endsection
