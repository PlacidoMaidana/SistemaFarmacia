@extends('voyager::master')

@section('page_title', 'Dispensaciones - ' . $tituloOrigen)

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <h1 class="page-title">
                    <i class="voyager-list"></i> Dispensaciones
                </h1>
                <p class="page-subtitle">{{ $tituloOrigen }}</p>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('dispensaciones.create', ['tipo_origen' => $tipoOrigen, 'id_origen' => $idOrigen]) }}" 
                   class="btn btn-success">
                    <i class="voyager-plus"></i> Nueva Dispensación
                </a>
                @if($tipoOrigen == 'receta')
                    <a href="{{ route('voyager.recetas.index') }}" class="btn btn-default">
                        <i class="voyager-back"></i> Volver a Recetas
                    </a>
                @endif
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')

        {{-- Información del Origen --}}
        @if($origen)
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title">Información del Origen</h3>
                </div>
                <div class="panel-body">
                    @if($tipoOrigen == 'receta')
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Interno:</strong> {{ $origen->interno->nombre_completo ?? 'N/A' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Médico:</strong> {{ $origen->medico->nombre_completo ?? 'N/A' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Fecha Receta:</strong> {{ \Carbon\Carbon::parse($origen->fecha)->format('d/m/Y') }}
                            </div>
                        </div>
                        @if($origen->diagnostico)
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-12">
                                    <strong>Diagnóstico:</strong> {{ $origen->diagnostico }}
                                </div>
                            </div>
                        @endif
                    @elseif($tipoOrigen == 'tratamiento')
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Interno:</strong> {{ $origen->interno->nombre_completo ?? 'N/A' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Medicamento:</strong> {{ $origen->medicamento->nombre ?? 'N/A' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Fecha Inicio:</strong> {{ \Carbon\Carbon::parse($origen->fecha_inicio)->format('d/m/Y') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Lista de Dispensaciones --}}
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Dispensaciones Registradas 
                    <span class="badge">{{ $dispensaciones->count() }}</span>
                </h3>
            </div>
            <div class="panel-body">
                @if($dispensaciones->isEmpty())
                    <div class="alert alert-info">
                        <i class="voyager-info-circled"></i> 
                        No hay dispensaciones registradas para este origen.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Interno</th>
                                    <th>Item</th>
                                    <th>Cantidad</th>
                                    <th>Usuario</th>
                                    <th>Observaciones</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dispensaciones as $dispensacion)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($dispensacion->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($dispensacion->hora)->format('H:i') }}</td>
                                        <td>{{ $dispensacion->interno->nombre_completo ?? 'N/A' }}</td>
                                        <td>
                                            @if($dispensacion->medicamento)
                                                <span class="label label-primary">MED</span>
                                                {{ $dispensacion->medicamento->nombre }}
                                                @if($dispensacion->medicamento->nombre_comercial)
                                                    <br><small class="text-muted">{{ $dispensacion->medicamento->nombre_comercial }}</small>
                                                @endif
                                                @if($dispensacion->es_psicotropico)
                                                    <span class="label label-warning">Psicotrópico</span>
                                                @endif
                                            @elseif($dispensacion->material)
                                                <span class="label label-info">MAT</span>
                                                {{ $dispensacion->material->nombre }}
                                            @else
                                                <span class="text-muted">Sin item</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ number_format($dispensacion->cantidad, 2) }} 
                                            {{ $dispensacion->unidad_medida }}
                                            @if($dispensacion->lote)
                                                <br><small class="text-muted">Lote: {{ $dispensacion->lote }}</small>
                                            @endif
                                            @if($dispensacion->fecha_vencimiento)
                                                <br><small class="text-muted">Venc: {{ \Carbon\Carbon::parse($dispensacion->fecha_vencimiento)->format('d/m/Y') }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $dispensacion->usuario->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($dispensacion->observaciones)
                                                <small>{{ Str::limit($dispensacion->observaciones, 50) }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('dispensaciones.destroy', $dispensacion->id_dispensacion) }}" 
                                                  method="POST" 
                                                  style="display: inline-block;"
                                                  onsubmit="return confirm('¿Está seguro de eliminar esta dispensación?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                    <i class="voyager-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .page-subtitle {
        font-size: 14px;
        color: #777;
        margin-top: 5px;
    }
    .label {
        font-size: 10px;
        padding: 3px 6px;
        margin-right: 5px;
    }
</style>
@endsection
