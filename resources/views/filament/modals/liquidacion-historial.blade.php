<div class="relative py-4">

    {{-- Línea vertical --}}
    <div class="absolute left-5 top-0 h-full w-px bg-gray-200 dark:bg-gray-700"></div>

    <div class="space-y-6">
        @forelse ($historial as $h)

            @php
                $colorDot = match($h->estado_nuevo) {
                    'aprobada' => 'bg-blue-500',
                    'pagada'   => 'bg-green-500',
                    'anulada'  => 'bg-red-500',
                    default    => 'bg-yellow-500',
                };

                $badge = match($h->estado_nuevo) {
                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                    'aprobada'  => 'bg-blue-100 text-blue-800',
                    'pagada'    => 'bg-green-100 text-green-800',
                    'anulada'   => 'bg-red-100 text-red-800',
                    default     => 'bg-gray-100 text-gray-800',
                };
            @endphp

            <div class="relative flex items-start gap-4">

                {{-- Punto + icono --}}
                <div class="relative z-10 flex items-center justify-center w-10 h-10 rounded-full {{ $colorDot }} shadow-md flex-none">
                    <x-heroicon-m-arrow-right class="w-5 h-5 text-white" />
                </div>

                {{-- Card --}}
                <div class="flex-1 min-w-0 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm hover:shadow-md transition">

                    {{-- Header --}}
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $h->cambiado_en->format('d/m/Y H:i') }}
                        </span>

                        @if($h->estado_anterior)
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $h->estado_anterior_label }}
                            </span>

                            <x-heroicon-m-arrow-right class="w-3 h-3 text-gray-400" />
                        @endif

                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badge }}">
                            {{ $h->estado_nuevo_label }}
                        </span>
                    </div>

                    {{-- Usuario --}}
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Por: <span class="font-semibold">{{ $h->usuario?->name ?? 'Sistema' }}</span>

                        @if($h->ip)
                            <span class="text-gray-400 text-xs ml-2">({{ $h->ip }})</span>
                        @endif
                    </div>

                    {{-- Notas --}}
                    @if($h->notas)
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 italic border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                            {{ $h->notas }}
                        </p>
                    @endif

                </div>
            </div>

        @empty
            <div class="text-center py-10">
                <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-gray-300" />
                <p class="mt-2 text-sm text-gray-400">Sin historial de cambios registrado.</p>
            </div>
        @endforelse
    </div>
</div>