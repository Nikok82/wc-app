@extends('layouts.app')

@section('title', $titolo)

@section('content')
    @include('partials.banner-squadra', ['flag' => $flag, 'titolo' => $titolo])

    <div class="tabs">
        <button class="tab-btn active" data-section="info">Info</button>
        <button class="tab-btn" data-section="partite">Partite</button>
        <button class="tab-btn" data-section="convocati">Convocati</button>
        <button class="tab-btn" data-section="maglie">Maglie</button>
        <button class="tab-btn" data-section="record">Record</button>
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
