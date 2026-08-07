<x-filament-widgets::widget>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;">
        @foreach ($cards as $card)
            <x-filament::section>
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                    <a href="{{ $card['url'] }}" style="display:flex;align-items:center;gap:10px;min-width:0;">
                        <x-filament::icon :icon="$card['icon']" style="width:28px;height:28px;flex:none;color:rgb(217 119 6);" />
                        <div style="min-width:0;">
                            <div style="font-weight:700;line-height:1.2;">{{ $card['label'] }}</div>
                            @if ($card['gruppo'])
                                <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;">{{ $card['gruppo'] }}</div>
                            @endif
                        </div>
                    </a>
                    <div style="flex:none;">
                        <x-filament::badge color="gray">{{ number_format($card['conteggio'], 0, ',', '.') }}</x-filament::badge>
                    </div>
                </div>

                <div style="display:flex;gap:8px;margin-top:14px;">
                    <x-filament::button tag="a" href="{{ $card['url'] }}" size="sm" color="gray" outlined>
                        Apri elenco
                    </x-filament::button>
                    @if ($card['urlNuovo'])
                        <x-filament::button tag="a" href="{{ $card['urlNuovo'] }}" size="sm">
                            + Nuovo
                        </x-filament::button>
                    @endif
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-widgets::widget>
