@extends('layouts.app')

@section('title', $nome)

@section('content')
    <p><a href="{{ route('arbitri.index') }}">‹ Tutti gli arbitri</a></p>
    <div id="tab-content" class="luce-bordo">
        @include('arbitro.scheda')
    </div>
@endsection
