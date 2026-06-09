@extends('reports.layout')
@section('content')
<div class="nota">Informe: {{ $data['titulo'] ?? 'Sin título' }} — sin vista específica configurada.</div>
@endsection
