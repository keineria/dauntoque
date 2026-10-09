@extends('layouts.app')

@section('contenido')
    @php
        $fmt = fn ($n) => number_format($n, 0, ',', '.');
        $maxDia = max(1, $stats['ultimos14']->max());
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="metric-card p-6 rounded-3xl">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>
                @if(!is_null($stats['cambioSemanal']))
                    <span class="text-xs font-bold px-2 py-1 rounded-full {{ $stats['cambioSemanal'] >= 0 ? 'text-pastel-green bg-green-500/10' : 'text-red-400 bg-red-500/10' }}">{{ $stats['cambioSemanal'] >= 0 ? '+' : '' }}{{ $stats['cambioSemanal'] }}% esta semana</span>
                @else
                    <span class="text-xs font-bold text-gray-400 bg-white/5 px-2 py-1 rounded-full">{{ $fmt($stats['semana']) }} esta semana</span>
                @endif
            </div>
            <h3 class="text-3xl font-black mb-1">{{ $fmt($stats['vistas']) }}</h3>
            <p class="text-sm text-gray-400">Tags / Vistas de Perfil</p>
            <p class="text-[11px] text-gray-500 mt-1">{{ $fmt($stats['visitantes']) }} visitantes únicos</p>
        </div>

        <div class="metric-card p-6 rounded-3xl relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </div>
                @if(!is_null($stats['tasaVcf']))
                    <span class="text-xs font-bold text-pastel-green bg-green-500/10 px-2 py-1 rounded-full">Tasa {{ $stats['tasaVcf'] }}%</span>
                @endif
            </div>
            <h3 class="text-3xl font-black mb-1 relative z-10">{{ $fmt($stats['vcf']) }}</h3>
            <p class="text-sm text-gray-400 relative z-10">Contactos Guardados</p>
        </div>

        <div class="metric-card p-6 rounded-3xl">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-[#25D366]/20 flex items-center justify-center text-[#4ade80]">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                </div>
                @if($stats['llamadas'])
                    <span class="text-xs font-bold text-gray-400 bg-white/5 px-2 py-1 rounded-full">+{{ $fmt($stats['llamadas']) }} llamadas</span>
                @endif
            </div>
            <h3 class="text-3xl font-black mb-1">{{ $fmt($stats['whatsapp']) }}</h3>
            <p class="text-sm text-gray-400">Chats de WhatsApp</p>
        </div>

        <div class="metric-card p-6 rounded-3xl">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-[#741b8c]/30 flex items-center justify-center text-[#a855f7]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                </div>
            </div>
            <h3 class="text-3xl font-black mb-1">{{ $fmt($stats['pagos']) }}</h3>
            <p class="text-sm text-gray-400">Clics en Zelle / Pagos</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="metric-card p-6 rounded-3xl lg:col-span-2">
            <h3 class="font-bold mb-1">Visitas de los últimos 14 días</h3>
            <p class="text-xs text-gray-500 mb-6">Una visita por persona y día.</p>
            <div class="flex items-end gap-1.5 h-40" role="img" aria-label="Visitas diarias de los últimos 14 días">
                @foreach($stats['ultimos14'] as $dia => $total)
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-1" title="{{ \Illuminate\Support\Carbon::parse($dia)->locale('es')->translatedFormat('d M') }}: {{ $total }}">
                        <span class="text-[10px] text-gray-400 tabular-nums">{{ $total ?: '' }}</span>
                        <div class="w-full rounded-t-md {{ $total ? 'bg-blue-500/70' : 'bg-white/5' }}" style="height: {{ max(4, round($total * 100 / $maxDia)) }}%"></div>
                        <span class="text-[9px] text-gray-600 tabular-nums">{{ \Illuminate\Support\Carbon::parse($dia)->format('d') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="metric-card p-6 rounded-3xl">
            <h3 class="font-bold mb-4">Clics en redes sociales</h3>
            @forelse($stats['redes'] as $red => $total)
                <div class="flex justify-between items-center py-2 border-b border-white/5 last:border-0 text-sm">
                    <span class="text-gray-300">{{ $red }}</span>
                    <span class="font-bold tabular-nums">{{ $fmt($total) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Todavía no hay clics en tus redes.</p>
            @endforelse
        </div>
    </div>

    <p class="text-xs text-gray-600 mt-6">
        @if($stats['desde'])
            Datos registrados desde el {{ \Illuminate\Support\Carbon::parse($stats['desde'])->locale('es')->translatedFormat('j \d\e F \d\e Y') }}. No se cuentan tus propias visitas.
        @else
            La medición está activa: las visitas y clics de tu perfil público aparecerán aquí. No se cuentan tus propias visitas.
        @endif
    </p>
@endsection
