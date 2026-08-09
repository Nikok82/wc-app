@extends('layouts.app')

@section('title', $titolo)

@section('content')
    @include('partials.banner-squadra', ['flag' => $flag ?? null, 'titolo' => $titolo])

    <div class="tabs">
        <button class="tab-btn active" data-section="info">Info</button>
        <button class="tab-btn" data-section="partite">Partite</button>        <button class="tab-btn" data-section="presenze">Presenze</button>
        <button class="tab-btn" data-section="giocatori">Giocatori</button>
        <button class="tab-btn" data-section="managers">Managers</button>
        <button class="tab-btn" data-section="maglie">Maglie</button>
        <button class="tab-btn" data-section="risultati">Risultati</button>
    </div>

    <div id="tab-content" class="luce-bordo">Caricamento…</div>

    <div class="footer-nav">
        @if ($prev)
            <a class="fn-link" href="{{ route('squadra.show', $prev->team_code) }}"
               title="{{ $prev->team_name }}" aria-label="Precedente: {{ $prev->team_name }}">
                <span class="fn-fr">‹</span>
                @if ($prevFlag ?? null)
                    <img src="{{ $prevFlag }}" alt="{{ $prev->team_name }}" onerror="this.style.display='none'">
                @else
                    {{ $prev->team_name }}
                @endif
            </a>
        @else
            <span></span>
        @endif

        @if ($next)
            <a class="fn-link" href="{{ route('squadra.show', $next->team_code) }}"
               title="{{ $next->team_name }}" aria-label="Successiva: {{ $next->team_name }}">
                @if ($nextFlag ?? null)
                    <img src="{{ $nextFlag }}" alt="{{ $next->team_name }}" onerror="this.style.display='none'">
                @else
                    {{ $next->team_name }}
                @endif
                <span class="fn-fr">›</span>
            </a>
        @endif
    </div>

    <style>
        .footer-nav .fn-link { display:inline-flex; align-items:center; gap:8px; }
        .footer-nav .fn-link img { height:26px; width:auto; border-radius:3px;
                                   box-shadow:0 1px 3px rgba(0,0,0,.28); display:block; }
        .footer-nav .fn-fr { color:var(--muted); font-weight:700; font-size:16px; }
    </style>

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
