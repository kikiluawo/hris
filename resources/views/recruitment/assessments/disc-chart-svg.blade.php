@props(['scores', 'color' => '#10b981', 'label' => 'Grafik'])

@php
    $d = max(1, min(28, (int) ($scores['D'] ?? 14)));
    $i = max(1, min(28, (int) ($scores['I'] ?? 14)));
    $s = max(1, min(28, (int) ($scores['S'] ?? 14)));
    $c = max(1, min(28, (int) ($scores['C'] ?? 14)));

    // Mapping koordinat Y: Nilai 28 -> Y=25, Nilai 14 -> Y=105, Nilai 1 -> Y=180
    $calcY = fn($val) => round(185 - (($val - 1) / 27) * 155);

    $xD = 45;  $yD = $calcY($d);
    $xI = 110; $yI = $calcY($i);
    $xS = 175; $yS = $calcY($s);
    $xC = 240; $yC = $calcY($c);

    $midlineY = $calcY(14);
@endphp

<div class="relative w-full overflow-hidden">
    <svg viewBox="0 0 280 215" class="w-full h-auto select-none font-sans" xmlns="http://www.w3.org/2000/svg">
        
        <rect x="25" y="25" width="235" height="{{ $midlineY - 25 }}" fill="currentColor" class="text-emerald-500/5" />
        <rect x="25" y="{{ $midlineY }}" width="235" height="{{ 185 - $midlineY }}" fill="currentColor" class="text-rose-500/5" />

        <line x1="25" y1="25" x2="260" y2="25" stroke="currentColor" class="text-line" stroke-width="1" />
        <line x1="25" y1="{{ $calcY(21) }}" x2="260" y2="{{ $calcY(21) }}" stroke="currentColor" class="text-line/40" stroke-width="1" stroke-dasharray="2,2" />
        <line x1="25" y1="{{ $calcY(7) }}" x2="260" y2="{{ $calcY(7) }}" stroke="currentColor" class="text-line/40" stroke-width="1" stroke-dasharray="2,2" />
        <line x1="25" y1="185" x2="260" y2="185" stroke="currentColor" class="text-line" stroke-width="1" />

        <line x1="25" y1="{{ $midlineY }}" x2="260" y2="{{ $midlineY }}" stroke="#ef4444" stroke-width="1.5" stroke-dasharray="4,3" />
        <text x="258" y="{{ $midlineY - 4 }}" font-size="9" fill="#ef4444" font-weight="700" text-anchor="end">MIDLINE (14)</text>

        <line x1="{{ $xD }}" y1="25" x2="{{ $xD }}" y2="185" stroke="currentColor" class="text-line/50" stroke-width="1" />
        <line x1="{{ $xI }}" y1="25" x2="{{ $xI }}" y2="185" stroke="currentColor" class="text-line/50" stroke-width="1" />
        <line x1="{{ $xS }}" y1="25" x2="{{ $xS }}" y2="185" stroke="currentColor" class="text-line/50" stroke-width="1" />
        <line x1="{{ $xC }}" y1="25" x2="{{ $xC }}" y2="185" stroke="currentColor" class="text-line/50" stroke-width="1" />

        <polyline
            points="{{ $xD }},{{ $yD }} {{ $xI }},{{ $yI }} {{ $xS }},{{ $yS }} {{ $xC }},{{ $yC }}"
            fill="none"
            stroke="{{ $color }}"
            stroke-width="3.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

        <circle cx="{{ $xD }}" cy="{{ $yD }}" r="5.5" fill="#ffffff" stroke="{{ $color }}" stroke-width="3" />
        <text x="{{ $xD }}" y="{{ $yD - 9 }}" font-size="11" font-weight="800" fill="{{ $color }}" text-anchor="middle">{{ $d }}</text>

        <circle cx="{{ $xI }}" cy="{{ $yI }}" r="5.5" fill="#ffffff" stroke="{{ $color }}" stroke-width="3" />
        <text x="{{ $xI }}" y="{{ $yI - 9 }}" font-size="11" font-weight="800" fill="{{ $color }}" text-anchor="middle">{{ $i }}</text>

        <circle cx="{{ $xS }}" cy="{{ $yS }}" r="5.5" fill="#ffffff" stroke="{{ $color }}" stroke-width="3" />
        <text x="{{ $xS }}" y="{{ $yS - 9 }}" font-size="11" font-weight="800" fill="{{ $color }}" text-anchor="middle">{{ $s }}</text>

        <circle cx="{{ $xC }}" cy="{{ $yC }}" r="5.5" fill="#ffffff" stroke="{{ $color }}" stroke-width="3" />
        <text x="{{ $xC }}" y="{{ $yC - 9 }}" font-size="11" font-weight="800" fill="{{ $color }}" text-anchor="middle">{{ $c }}</text>

        <text x="{{ $xD }}" y="206" font-size="13" font-weight="800" fill="currentColor" class="text-ink" text-anchor="middle">D</text>
        <text x="{{ $xI }}" y="206" font-size="13" font-weight="800" fill="currentColor" class="text-ink" text-anchor="middle">I</text>
        <text x="{{ $xS }}" y="206" font-size="13" font-weight="800" fill="currentColor" class="text-ink" text-anchor="middle">S</text>
        <text x="{{ $xC }}" y="206" font-size="13" font-weight="800" fill="currentColor" class="text-ink" text-anchor="middle">C</text>
    </svg>
</div>
