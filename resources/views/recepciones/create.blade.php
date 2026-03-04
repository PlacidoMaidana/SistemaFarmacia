@extends('voyager::master')

@section('page_title', 'Nueva Recepción de Stock')

@php
    $breadcrumbs = [
        ['url' => route('voyager.dashboard'), 'name' => 'Dashboard'],
        ['url' => route('recepciones.index'), 'name' => 'Recepciones de Stock'],
        ['url' => '', 'name' => 'Nueva Recepción'],
    ];
@endphp

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
<div class="container-fluid">
    <h1 class="page_title">
        <i class="voyager-plus"></i> Nueva Recepción de Stock
        <small>Registrar ingreso desde Farmacia Central</small>
        <a href="{{ route('recepciones.index') }}" class="btn btn-warning btn-sm pull-right">
            <i class="voyager-list"></i> Volver al Listado
        </a>
    </h1>
</div>
@stop

@section('content')
<div class="page-content edit-add container-fluid">
    @include('voyager::alerts')
    
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-bordered">
                <div class="panel-body">
                    <form action="{{ route('recepciones.store') }}" method="POST" role="form">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('nro_remito') has-error @enderror">
                                    <label for="nro_remito" class="control-label">
                                        <i class="voyager-receipt"></i> Número de Remito *
                                    </label>
                                    <input type="text" class="form-control" id="nro_remito" name="nro_remito" 
                                           placeholder="Ej: REM-2026-001" value="{{ old('nro_remito') }}" required>
                                    @error('nro_remito')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group @error('fecha_recepcion') has-error @enderror">
                                    <label for="fecha_recepcion" class="control-label">
                                        <i class="voyager-calendar"></i> Fecha de Recepción *
                                    </label>
                                    <input type="date" class="form-control" id="fecha_recepcion" name="fecha_recepcion" 
                                           value="{{ old('fecha_recepcion', date('Y-m-d')) }}" required>
                                    @error('fecha_recepcion')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group @error('observaciones') has-error @enderror">
                            <label for="observaciones" class="control-label">
                                <i class="voyager-documentation"></i> Observaciones
                            </label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                      placeholder="Observaciones adicionales sobre la recepción...">{{ old('observaciones') }}</textarea>
                            <span class="help-block">
                                <small class="text-muted">Información adicional sobre el ingreso, estado de la mercadería, etc.</small>
                            </span>
                            @error('observaciones')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="voyager-check"></i> Crear Recepción
                            </button>
                            
                            <a href="{{ route('recepciones.index') }}" class="btn btn-default">
                                <i class="voyager-x"></i> Cancelar
                            </a>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="voyager-info-circled"></i>
                            <strong>Siguiente paso:</strong> Una vez creada la recepción, podrá agregar los medicamentos y materiales recibidos.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script>
$(document).ready(function() {
    // Auto-generar número de remito si está vacío
    if (!$('#nro_remito').val()) {
        var fecha = new Date();
        var year = fecha.getFullYear();
        var month = String(fecha.getMonth() + 1).padStart(2, '0');
        var day = String(fecha.getDate()).padStart(2, '0');
        var time = String(fecha.getHours()).padStart(2, '0') + String(fecha.getMinutes()).padStart(2, '0');
        
        $('#nro_remito').attr('placeholder', 'REM-' + year + month + day + '-' + time);
    }
});
</script>
@stop

@section('css')
<style>
    .required-asterisk {
        color: #e74c3c;
    }
    .form-group label {
        font-weight: 600;
    }
</style>
@stop