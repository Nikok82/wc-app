@extends('layouts.app')

@section('title', $nome)

@section('content')
    <p><a href="{{ route('stadi.index') }}">‹ Tutti gli stadi</a></p>
    <div id="tab-content" class="luce-bordo">
        @include('stadio.scheda')
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.WC && WC.initMappeStadio) WC.initMappeStadio(document);
        });
    </script>
@endsection
