@extends('layouts.app')

@section('title', $titolo)

@section('content')
    @include('partials.banner-squadra', ['flag' => $flag ?? null, 'titolo' => $titolo])

    <div class="tabs">
        <button class="tab-btn active" data-section="info">Info</button>
        <button class="tab-btn" data-section="partite">Partite</button>        <button class="tab-btn" data-section="presenze">Presenze</button>
        <button class="tab-btn" data-section="giocatori">Giocatori</button>
        <button class="tab-btn" data-section="managers">Managers</button>
        <button class="tab-btn" data-section="record">Record</button>
    </div>

    <div id="tab-content" class="luce-bordo">Caricamento…</div>

    <div class="footer-nav">
        @if ($prev)
            <a href="{{ route('squadra.show', $prev->team_code) }}">‹ {{ $prev->team_name }}</a>
        @else
            <span></span>
        @endif

        @if ($next)
            <a href="{{ route('squadra.show', $next->team_code) }}">{{ $next->team_name }} ›</a>
        @endif
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        WC.initTabs({
            base: @json(url('/squadra')),
            id: @json($code),
            buttons: '.tab-btn[data-section]',
            content: '#tab-content',
            sezioneDefault: 'info'
        });
    });
    </script>
@endsection
