@extends('voyager::master')

@section('page_title', 'Recepciones de Stock')

@php
    $breadcrumbs = [
        ['url' => route('voyager.dashboard'), 'name' => 'Dashboard'],
        ['url' => '', 'name' => 'Recepciones de Stock'],
    ];
@endphp

@section('page_header')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <h1 class="page_title">
                <i class="voyager-truck"></i> Recepciones de Stock
                <small>Gestión de ingresos desde Farmacia Central</small>
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('recepciones.create') }}" class="btn btn-success btn-add-new">
                <i class="voyager-plus"></i> Nueva Recepción
            </a>
        </div>
    </div>
</div>
@stop
                <i class="voyager-plus"></i> <span>Nueva Recepción</span>
            </a>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="page-content browse container-fluid">
    @include('voyager::alerts')
    
    <!-- Filtros -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-body">
                    <form method="GET" action="{{ route('recepciones.index') }}" class="form-inline">
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select name="estado" id="estado" class="form-control">
                                <option value="">Todos los estados</option>
                                @foreach($estados as $valor => $nombre)
                                    <option value="{{ $valor }}" {{ request('estado') == $valor ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_desde">Desde:</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" 
                                   value="{{ request('fecha_desde') }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_hasta">Hasta:</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" 
                                   value="{{ request('fecha_hasta') }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="nro_remito">Nº Remito:</label>
                            <input type="text" name="nro_remito" id="nro_remito" class="form-control" 
                                   placeholder="Buscar por remito..." value="{{ request('nro_remito') }}">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="voyager-search"></i> Buscar
                        </button>
                        
                        @if(request()->hasAny(['estado', 'fecha_desde', 'fecha_hasta', 'nro_remito']))
                            <a href="{{ route('recepciones.index') }}" class="btn btn-default">
                                <i class="voyager-x"></i> Limpiar
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de recepciones -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-body">
                    @if($recepciones->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nº Remito</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Items</th>
                                        <th>Cantidad Total</th>
                                        <th>Usuario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recepciones as $recepcion)
                                        <tr>
                                            <td>
                                                <strong>{{ $recepcion->nro_remito }}</strong>
                                            </td>
                                            <td>
                                                {{ $recepcion->fecha_recepcion->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                @if($recepcion->estado === 'BORRADOR')
                                                    <span class="label label-warning">{{ $recepcion->descripcion_estado }}</span>
                                                @elseif($recepcion->estado === 'CONFIRMADA')
                                                    <span class="label label-success">{{ $recepcion->descripcion_estado }}</span>
                                                @else
                                                    <span class="label label-danger">{{ $recepcion->descripcion_estado }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge">{{ $recepcion->total_items }}</span>
                                            </td>
                                            <td>
                                                {{ number_format($recepcion->cantidad_total, 0) }} unidades
                                            </td>
                                            <td>
                                                {{ $recepcion->usuario->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('recepciones.show', $recepcion->id_recepcion) }}" 
                                                       class="btn btn-primary btn-sm" title="Ver detalle">
                                                        <i class="voyager-eye"></i>
                                                    </a>
                                                    
                                                    @if($recepcion->puedeSerEditada())
                                                        <a href="{{ route('recepciones.edit', $recepcion->id_recepcion) }}" 
                                                           class="btn btn-warning btn-sm" title="Editar">
                                                            <i class="voyager-edit"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <div class="text-center">
                            {{ $recepciones->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center">
                            <p class="text-muted">
                                <i class="voyager-info-circled" style="font-size: 48px;"></i><br>
                                No se encontraron recepciones con los filtros seleccionados.
                            </p>
                            <a href="{{ route('recepciones.create') }}" class="btn btn-success">
                                <i class="voyager-plus"></i> Crear primera recepción
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .form-inline .form-group {
        margin-right: 15px;
        margin-bottom: 10px;
    }
    .form-inline label {
        margin-right: 5px;
        font-weight: bold;
    }
    .badge {
        font-size: 12px;
        padding: 4px 8px;
    }
</style>
@stop