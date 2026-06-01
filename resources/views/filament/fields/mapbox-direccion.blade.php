@php
    $statePath = $getStatePath();            // ej: "data.direccion"
    $state     = $getState() ?? '';
    $parts     = explode('.', $statePath);
    $prefix    = implode('.', array_slice($parts, 0, -1)); // "data"
    $latPath   = $prefix ? $prefix . '.latitud'  : 'latitud';
    $lngPath   = $prefix ? $prefix . '.longitud' : 'longitud';
@endphp

<livewire:mapbox-direccion
    :state-path="$statePath"
    :lat-path="$latPath"
    :lng-path="$lngPath"
    :state="$state"
    :key="'mapbox-' . $statePath"
/>
