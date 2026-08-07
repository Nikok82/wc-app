@extends('layouts.app')

@section('title', $testata['home']['name'].' - '.$testata['away']['name'].' ('.$testata['anno'].')')

@section('content')
    {{-- ================= TESTATA FISSA (risultato + marcatori) ================= --}}
    <div class="pt-testata luce-bordo">
        <div class="pt-torneo">
            <a href="{{ route('torneo.show', $testata['tid']) }}">Coppa del Mondo {{ $testata['anno'] }}</a>
            <span class="pt-stage">{{ $testata['stage'] }}</span>
        </div>

        <div class="pt-match">
            <div class="pt-squadra casa">
                @if ($testata['home']['flag'])
                    <img class="flag" src="{{ $testata['home']['flag'] }}" alt="" onerror="this.style.display='none'">
                @endif
                <span class="pt-nome">
                    @if ($testata['home']['url'])<a href="{{ $testata['home']['url'] }}">{{ $testata['home']['name'] }}</a>@else{{ $testata['home']['name'] }}@endif
                </span>
            </div>

            <div class="pt-risultato">
                <span class="pt-score">{{ $testata['score'] }}</span>
                @if ($testata['dcr'])
                    <span class="pt-nota">({{ $testata['ris_rigori'] }} d.c.r.)</span>
                @elseif ($testata['dts'])
                    <span class="pt-nota">(d.t.s.)</span>
                @endif
                @if ($testata['replay'])
                    <span class="pt-nota">replay</span>
                @endif
                @if ($nonGiocata)
                    <span class="pt-nota">non giocata</span>
                @endif
            </div>

            <div class="pt-squadra ospite">
                <span class="pt-nome">
                    @if ($testata['away']['url'])<a href="{{ $testata['away']['url'] }}">{{ $testata['away']['name'] }}</a>@else{{ $testata['away']['name'] }}@endif
                </span>
                @if ($testata['away']['flag'])
                    <img class="flag" src="{{ $testata['away']['flag'] }}" alt="" onerror="this.style.display='none'">
                @endif
            </div>
        </div>

        @if (count($testata['marcatori']['home']) || count($testata['marcatori']['away']))
            <div class="pt-marcatori">
                <div class="pt-mar casa">
                    @foreach ($testata['marcatori']['home'] as $g)
                        <span class="pt-gol">
                            <b>{{ $g['minuto'] }}</b>
                            <a href="{{ route('giocatore.show', $g['player_id']) }}">{{ $g['nome'] }}</a>@if($g['rigore']) <em>(rig.)</em>@endif @if($g['autogol'])<em>(aut.)</em>@endif
                        </span>
                    @endforeach
                </div>
                <div class="pt-mar ospite">
                    @foreach ($testata['marcatori']['away'] as $g)
                        <span class="pt-gol">
                            <a href="{{ route('giocatore.show', $g['player_id']) }}">{{ $g['nome'] }}</a>@if($g['rigore']) <em>(rig.)</em>@endif @if($g['autogol'])<em>(aut.)</em>@endif
                            <b>{{ $g['minuto'] }}</b>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- ================= TAB ================= --}}
    <div class="tabs">
        <button class="tab-btn active" data-section="info">Info</button>
        <button class="tab-btn" data-section="formazioni">Formazioni</button>
        <button class="tab-btn" data-section="eventi">Eventi</button>
        <button class="tab-btn" data-section="situazione">Situazione</button>
    </div>

    <div id="tab-content" class="luce-bordo">Caricamento…</div>

    <style>
        .pt-testata { background:#fff; border:1px solid var(--line); border-radius:12px;
                      padding:14px 16px 12px; margin-bottom:16px; }
        .pt-torneo { text-align:center; font-size:13px; color:var(--muted); margin-bottom:10px; }
        .pt-torneo a { font-weight:700; }
        .pt-torneo .pt-stage { display:block; font-size:12px; letter-spacing:.4px;
                               text-transform:uppercase; margin-top:2px; }

        .pt-match { display:flex; align-items:center; gap:10px; }
        .pt-squadra { flex:1; display:flex; align-items:center; gap:10px; min-width:0; }
        .pt-squadra.casa   { justify-content:flex-start; }
        .pt-squadra.ospite { justify-content:flex-end; text-align:right; }
        .pt-squadra .flag { width:46px; height:auto; border-radius:4px; flex:none;
                            box-shadow:0 1px 4px rgba(0,0,0,.3); }
        .pt-nome { font-weight:800; font-size:18px; line-height:1.2; overflow-wrap:anywhere; }
        .pt-nome a { color:var(--ink); }

        .pt-risultato { flex:none; text-align:center; min-width:86px; }
        .pt-score { font-size:34px; font-weight:800; letter-spacing:1px;
                    font-variant-numeric:tabular-nums; white-space:nowrap; }
        .pt-nota { display:block; font-size:12px; color:var(--muted); }

        .pt-marcatori { display:flex; gap:14px; margin-top:10px; padding-top:9px;
                        border-top:1px solid var(--line); }
        .pt-mar { flex:1; display:flex; flex-direction:column; gap:2px; font-size:13px; }
        .pt-mar.casa   { align-items:flex-start; }
        .pt-mar.ospite { align-items:flex-end; text-align:right; }
        .pt-gol b { color:var(--muted); font-weight:700; font-variant-numeric:tabular-nums; }
        .pt-gol em { color:var(--muted); font-style:normal; font-size:11px; }

        @media (max-width:560px) {
            .pt-squadra { flex-direction:column; gap:5px; }
            .pt-squadra.casa   { align-items:flex-start; text-align:left; }
            .pt-squadra.ospite { align-items:flex-end; }
            .pt-squadra.ospite .pt-nome { order:2; }
            .pt-squadra.ospite .flag    { order:1; }
            .pt-nome { font-size:15px; }
            .pt-score { font-size:26px; }
            .pt-risultato { min-width:64px; }
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        WC.initTabs({
            base: @json(url('/partita')),
            id: @json($matchId),
            buttons: '.tab-btn[data-section]',
            content: '#tab-content',
            sezioneDefault: 'info'
        });
    });
    </script>
@endsection
