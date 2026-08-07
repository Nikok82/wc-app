@extends('layouts.app')

@section('title', $nome)

@section('content')
    <p><a href="{{ route('allenatori.index') }}">‹ Tutti gli allenatori</a></p>
    <div id="tab-content" class="luce-bordo">
        @include('allenatore.scheda')
    </div>
@endsection
