@extends('layouts.torneo')

@section('title', $titolo)

@section('content')
    <div class="torneo-wrap">
        @if ($sfondo)
            <div class="torneo-bg" style="background-image:url('{{ $sfondo }}')"></div>
        @endif

        <div class="torneo-title">{{ $titolo }}</div>

        <div class="buttons-box">
            <div class="button active" data-section="info"      title="Info">{!! \App\Support\Icons::svg('info') !!}</div>
            <div class="button"        data-section="stadi"     title="Stadi">{!! \App\Support\Icons::svg('field') !!}</div>
            <div class="button"        data-section="partite"   title="Partite">{!! \App\Support\Icons::svg('scoreboard') !!}</div>
            <div class="button"        data-section="squadre"   title="Squadre">{!! \App\Support\Icons::svg('team') !!}</div>
            <div class="button"        data-section="managers"  title="Managers">{!! \App\Support\Icons::svg('strategy') !!}</div>
            <div class="button"        data-section="arbitri"   title="Arbitri">{!! \App\Support\Icons::svg('whistle') !!}</div>
            <div class="button"        data-section="classifica" title="Classifica">{!! \App\Support\Icons::svg('chart') !!}</div>
            <div class="button"        data-section="record"    title="Record">{!! \App\Support\Icons::svg('trophy-2') !!}</div>
            <div class="button"        data-section="marcatori" title="Marcatori">{!! \App\Support\Icons::svg('foot-shoe') !!}</div>
        </div>

        <div id="tab-content"><div class="caric">Caricamento…</div></div>
    </div>

    {{-- Il vecchio footer prev/next (.torneo-footer) è stato sostituito
         dalla barra bottoni globale inclusa dal layout (05/08). --}}

    <script>
    /* Il loader dei tab e tutta l'interattivita' condivisa vivono in
       public/js/wc.js (incluso dal layout DOPO questo blocco): per questo
       l'inizializzazione avviene su DOMContentLoaded. */
    document.addEventListener('DOMContentLoaded', function () {
        WC.initTabs({
            base: @json(url('/torneo')),
            id: @json($tournamentId),
            buttons: '.buttons-box .button[data-section]',
            content: '#tab-content',
            sezioneDefault: 'info'
        });
    });
    </script>
@endsection
