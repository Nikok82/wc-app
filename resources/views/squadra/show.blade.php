@extends('layouts.app')

@section('title', $titolo)

@section('content')
    @include('partials.banner-squadra', ['flag' => $flag ?? null, 'titolo' => $titolo])

    {{-- Tab a icona, come quelle del torneo. La barra sta sempre su una riga
         sola: niente wrap, i bottoni si dividono lo spazio disponibile. --}}
    <div class="tabs tabs-icone">
        <button class="tab-btn active" data-section="info"      title="Info"      aria-label="Info">{!! \App\Support\Icons::svg('info') !!}</button>
        <button class="tab-btn"        data-section="partite"   title="Partite"   aria-label="Partite">{!! \App\Support\Icons::svg('scoreboard') !!}</button>
        <button class="tab-btn"        data-section="presenze"  title="Presenze"  aria-label="Presenze">{!! \App\Support\Icons::svg('competition') !!}</button>
        <button class="tab-btn"        data-section="giocatori" title="Giocatori" aria-label="Giocatori">{!! \App\Support\Icons::svg('team') !!}</button>
        <button class="tab-btn"        data-section="managers"  title="Managers"  aria-label="Managers">{!! \App\Support\Icons::svg('strategy') !!}</button>
        <button class="tab-btn"        data-section="maglie"    title="Maglie"    aria-label="Maglie">@include('partials.icona-maglia')</button>
        <button class="tab-btn"        data-section="risultati" title="Risultati" aria-label="Risultati">{!! \App\Support\Icons::svg('chart') !!}</button>
        <button class="tab-btn"        data-section="record"    title="Record"    aria-label="Record">{!! \App\Support\Icons::svg('trophy-2') !!}</button>
    </div>

    <div id="tab-content" class="luce-bordo">Caricamento…</div>

    {{-- Il footer con le bandiere prev/next e' stato rimosso: la navigazione
         alfabetica fra squadre vive gia' nella barra bottoni globale, che
         mostra le stesse bandiere (tonde) in fondo allo schermo. --}}

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
