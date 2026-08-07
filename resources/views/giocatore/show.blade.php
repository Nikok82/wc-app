@extends('layouts.app')

@section('title', $nome)

@section('content')
    <style>
        /* Mobile: pagina a piena larghezza (header gia' full width, qui il contenuto) */
        @media (max-width:640px) {
            .wrap { padding-left:0; padding-right:0; }
            .wrap > p { padding-left:10px; }
            #tab-content { border-left:0; border-right:0; border-radius:0;
                           padding:14px 10px; }
        }
    </style>
    <p><a href="{{ route('giocatori.index') }}">‹ Tutti i giocatori</a></p>
    <div id="tab-content" class="luce-bordo">
        @include('giocatore.scheda')
    </div>
@endsection
