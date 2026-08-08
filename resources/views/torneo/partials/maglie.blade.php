{{-- Tab Maglie del torneo: a blocchi per squadra (ordine alfabetico), solo le
     immagini delle maglie indossate (ordinate per uso). Frammento via fetch:
     niente <script>, solo <style>. --}}

@if (empty($blocchi))
    <p>Nessuna maglia disponibile per questo torneo.</p>
@else
    <div class="maglie-grp">
        @foreach ($blocchi as $b)
            <div class="mg-blocco">
                @if ($b['url'])
                    <a class="mg-testa" href="{{ $b['url'] }}">
                        @if ($b['flag'])<img src="{{ $b['flag'] }}" alt="" onerror="this.style.display='none'">@endif
                        <span>{{ $b['name'] }}</span>
                    </a>
                @else
                    <div class="mg-testa">
                        @if ($b['flag'])<img src="{{ $b['flag'] }}" alt="" onerror="this.style.display='none'">@endif
                        <span>{{ $b['name'] }}</span>
                    </div>
                @endif

                <div class="mg-maglie">
                    @foreach ($b['kits'] as $k)
                        @if ($k['url'])
                            <img class="mg-kit" src="{{ $k['url'] }}" alt="Maglia {{ $b['name'] }}"
                                 loading="lazy" onerror="this.style.display='none'">
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .maglie-grp { display:flex; flex-direction:column; }
        .mg-blocco { padding:14px 0; border-bottom:1px solid var(--line, #e2e8e5); }
        .mg-blocco:last-child { border-bottom:0; }
        .mg-testa { display:flex; align-items:center; gap:9px; text-decoration:none;
            color:#0f6c14; font-weight:700; font-size:1.02rem; margin-bottom:10px; }
        a.mg-testa:hover { text-decoration:none; filter:brightness(1.12); }
        .mg-testa img { width:26px; height:26px; border-radius:50%; object-fit:cover;
            flex:none; box-shadow:0 1px 2px rgba(0,0,0,.25); }
        .mg-maglie { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-start; }
        .mg-kit { width:92px; height:auto; image-rendering:-webkit-optimize-contrast;
            background:#fafcfa; border:1px solid var(--line, #e2e8e5); border-radius:8px; padding:5px; }
        @media (max-width:480px) {
            .mg-kit { width:76px; }
        }
    </style>
@endif
