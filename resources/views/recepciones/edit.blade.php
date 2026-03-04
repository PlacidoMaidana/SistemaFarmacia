@extends('voyager::master')

@section('page_title', 'Editar Recepción de Stock')

@php
    $breadcrumbs = [
        ['url' => route('voyager.dashboard'), 'name' => 'Dashboard'],
        ['url' => route('recepciones.index'), 'name' => 'Recepciones de Stock'],
        ['url' => route('recepciones.show', $recepcion->id_recepcion), 'name' => $recepcion->nro_remito],
        ['url' => '', 'name' => 'Editar'],
    ];
@endphp

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
<div class="container-fluid">
    <h1 class="page_title">
        <i class="voyager-edit"></i> Editar Recepción de Stock
        <small>{{ $recepcion->nro_remito }} - {{ $recepcion->fecha_recepcion->format('d/m/Y') }}</small>
        <div class="pull-right">
            <a href="{{ route('recepciones.show', $recepcion->id_recepcion) }}" class="btn btn-primary btn-sm">
                <i class="voyager-eye"></i> Ver Detalle
            </a>
            <a href="{{ route('recepciones.index') }}" class="btn btn-warning btn-sm">
                <i class="voyager-list"></i> Volver al Listado
            </a>
        </div>
    </h1>
</div>
@stop

@section('content')
<div class="page-content edit-add container-fluid">
    @include('voyager::alerts')
    
    <!-- Datos de la recepción -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="voyager-receipt"></i> Datos de la Recepción</h3>
                </div>
                <div class="panel-body">
                    <form action="{{ route('recepciones.update', $recepcion->id_recepcion) }}" method="POST" role="form">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group @error('nro_remito') has-error @enderror">
                                    <label for="nro_remito" class="control-label">Número de Remito *</label>
                                    <input type="text" class="form-control" id="nro_remito" name="nro_remito" 
                                           value="{{ old('nro_remito', $recepcion->nro_remito) }}" required>
                                    @error('nro_remito')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group @error('fecha_recepcion') has-error @enderror">
                                    <label for="fecha_recepcion" class="control-label">Fecha de Recepción *</label>
                                    <input type="date" class="form-control" id="fecha_recepcion" name="fecha_recepcion" 
                                           value="{{ old('fecha_recepcion', $recepcion->fecha_recepcion->format('Y-m-d')) }}" required>
                                    @error('fecha_recepcion')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Estado</label>
                                    <div class="form-control-static">
                                        @if($recepcion->estado === 'BORRADOR')
                                            <span class="label label-warning">{{ $recepcion->descripcion_estado }}</span>
                                        @elseif($recepcion->estado === 'CONFIRMADA') 
                                            <span class="label label-success">{{ $recepcion->descripcion_estado }}</span>
                                        @else
                                            <span class="label label-danger">{{ $recepcion->descripcion_estado }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group @error('observaciones') has-error @enderror">
                            <label for="observaciones" class="control-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2" 
                                      placeholder="Observaciones sobre la recepción...">{{ old('observaciones', $recepcion->observaciones) }}</textarea>
                            @error('observaciones')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="voyager-check"></i> Actualizar Recepción
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Gestión de items -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="voyager-list"></i> Items de la Recepción 
                        <span class="badge">{{ $recepcion->items->count() }}</span>
                    </h3>
                </div>
                <div class="panel-body">
                    <!-- Formulario para agregar items -->
                    <div class="well">
                        <h4><i class="voyager-plus"></i> Agregar Item</h4>
                        <form id="formAgregarItem" class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="tipo_item" class="control-label">Tipo *</label>
                                        <select class="form-control" id="tipo_item" name="tipo_item" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="MEDICAMENTO">Medicamento</option>
                                            <option value="MATERIAL">Material</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="item_select" class="control-label">Item *</label>
                                        <select class="form-control" id="item_select" name="item_id" required disabled>
                                            <option value="">Seleccione tipo primero...</option>
                                        </select>
                                        <small class="help-block">Stock actual: <span id="stock_actual">-</span></small>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="cantidad" class="control-label">Cantidad *</label>
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                               step="0.01" min="0.01" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="nro_lote" class="control-label">Nº Lote</label>
                                        <input type="text" class="form-control" id="nro_lote" name="nro_lote" 
                                               placeholder="Opcional">
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="fecha_vencimiento" class="control-label">Vencimiento</label>
                                        <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento">
                                    </div>
                                </div>
                                
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="control-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-success form-control">
                                            <i class="voyager-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de items -->
                    <div id="items-container">
                        @if($recepcion->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Item</th>
                                            <th>Cantidad</th>
                                            <th>Lote</th>
                                            <th>Vencimiento</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-table-body">
                                        @foreach($recepcion->items as $item)
                                            @include('recepciones.partials.item_row', ['item' => $item])
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div id="no-items-message" class="text-center text-muted">
                                <i class="voyager-info-circled" style="font-size: 48px;"></i><br>
                                No se han agregado items aún.<br>
                                <small>Use el formulario anterior para agregar medicamentos y materiales.</small>
                            </div>
                        @endif
                    </div>

                    <!-- Resumen -->
                    @if($recepcion->items->count() > 0)
                        <div class="well well-sm">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Total items:</strong> <span class="badge">{{ $recepcion->total_items }}</span>
                                </div>
                                <div class="col-md-6 text-right">
                                    <strong>Cantidad total:</strong> {{ number_format($recepcion->cantidad_total, 0) }} unidades
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-body text-center">
                    @if($recepcion->puedeSerConfirmada())
                        <form action="{{ route('recepciones.confirmar', $recepcion->id_recepcion) }}" 
                              method="POST" style="display: inline-block;" 
                              onsubmit="return confirm('¿Está seguro de confirmar esta recepción? Esta acción aplicará todos los items al stock y no se podrá deshacer.')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="voyager-check"></i> Confirmar Recepción
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('recepciones.show', $recepcion->id_recepcion) }}" class="btn btn-primary btn-lg">
                        <i class="voyager-eye"></i> Ver Detalle
                    </a>
                    
                    <a href="{{ route('recepciones.index') }}" class="btn btn-default btn-lg">
                        <i class="voyager-list"></i> Volver al Listado
                    </a>

                    @if($recepcion->puedeSerEditada() && $recepcion->items->count() == 0)
                        <form action="{{ route('recepciones.destroy', $recepcion->id_recepcion) }}" 
                              method="POST" style="display: inline-block;" 
                              onsubmit="return confirm('¿Está seguro de eliminar esta recepción?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="voyager-trash"></i> Eliminar Recepción
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .well h4 {
        margin-top: 0;
    }
    .badge {
        font-size: 12px;
    }
    #stock_actual {
        font-weight: bold;
        color: #333;
    }
</style>
@stop

@section('javascript')
<script>
$(document).ready(function() {
    var medicamentos = @json($medicamentos);
    var materiales = @json($materiales);
    
    // Cambiar opciones cuando se selecciona tipo
    $('#tipo_item').change(function() {
        var tipo = $(this).val();
        var $itemSelect = $('#item_select');
        var $stockActual = $('#stock_actual');
        
        $itemSelect.empty().prop('disabled', false);
        $stockActual.text('-');
        
        if (tipo === 'MEDICAMENTO') {
            $itemSelect.append('<option value="">Seleccionar medicamento...</option>');
            medicamentos.forEach(function(med) {
                $itemSelect.append('<option value="' + med.id_medicamento + '" data-stock="' + med.stock_actual + '">' + med.nombre + '</option>');
            });
        } else if (tipo === 'MATERIAL') {
            $itemSelect.append('<option value="">Seleccionar material...</option>');
            materiales.forEach(function(mat) {
                $itemSelect.append('<option value="' + mat.id_material + '" data-stock="' + mat.stock_actual + '">' + mat.nombre + '</option>');
            });
        } else {
            $itemSelect.append('<option value="">Seleccione tipo primero...</option>').prop('disabled', true);
        }
    });
    
    // Mostrar stock actual al seleccionar item
    $('#item_select').change(function() {
        var stock = $(this).find(':selected').data('stock') || 0;
        $('#stock_actual').text(stock + ' unidades');
    });
    
    // Agregar item via AJAX
    $('#formAgregarItem').submit(function(e) {
        e.preventDefault();
        
        var tipoItem = $('#tipo_item').val();
        var itemId = $('#item_select').val();
        var cantidad = $('#cantidad').val();
        var nroLote = $('#nro_lote').val();
        var fechaVencimiento = $('#fecha_vencimiento').val();
        
        // Validar campos requeridos
        if (!tipoItem || !itemId || !cantidad) {
            toastr.error('Complete todos los campos requeridos');
            return;
        }
        
        // Validar cantidad
        if (parseFloat(cantidad) <= 0) {
            toastr.error('La cantidad debe ser mayor a 0');
            return;
        }
        
        // Validar fecha de vencimiento
        if (fechaVencimiento) {
            var hoy = new Date();
            var fechaIngresada = new Date(fechaVencimiento);
            hoy.setHours(0,0,0,0);
            fechaIngresada.setHours(0,0,0,0);
            
            if (fechaIngresada < hoy) {
                toastr.error('La fecha de vencimiento no puede ser anterior a hoy');
                return;
            }
        }
        
        // Crear objeto de datos limpio
        var data = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            tipo_item: tipoItem,
            cantidad: cantidad
        };
        
        // Agregar campo específico según tipo
        if (tipoItem === 'MEDICAMENTO') {
            data.id_medicamento = itemId;
        } else {
            data.id_material = itemId;
        }
        
        // Campos opcionales
        if (nroLote) data.nro_lote = nroLote;
        if (fechaVencimiento) data.fecha_vencimiento = fechaVencimiento;
        
        $.ajax({
            url: '{{ route("recepciones.items.store", $recepcion->id_recepcion) }}',
            method: 'POST',
            data: data,
            beforeSend: function() {
                $('button[type="submit"]', '#formAgregarItem').prop('disabled', true);
            },
            success: function(response) {
                // Agregar nueva fila
                if ($('#items-table-body').length === 0) {
                    // Crear tabla si no existe
                    $('#items-container').html(`
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Item</th>
                                        <th>Cantidad</th>
                                        <th>Lote</th>
                                        <th>Vencimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="items-table-body"></tbody>
                            </table>
                        </div>
                    `);
                }
                
                $('#items-table-body').append(response.html);
                $('#no-items-message').hide();
                
                // Limpiar formulario
                $('#formAgregarItem')[0].reset();
                $('#tipo_item').trigger('change');
                
                // Mostrar mensaje de éxito
                toastr.success('Item agregado exitosamente');
                
                // Actualizar página para refresh de contadores
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            },
            error: function(xhr) {
                console.log('Error AJAX:', xhr); // Debug temporal
                
                var error = 'Error al agregar el item';
                
                // Manejar diferentes tipos de error
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.error) {
                        error = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.errors) {
                        // Errores de validación Laravel
                        var errors = xhr.responseJSON.errors;
                        error = 'Errores de validación: ';
                        Object.keys(errors).forEach(function(key) {
                            error += errors[key].join(', ') + ' ';
                        });
                    } else if (xhr.responseJSON.message) {
                        error = xhr.responseJSON.message;
                    }
                } else if (xhr.status === 422) {
                    error = 'Datos inv&aacute;lidos. Verifique los campos.';
                } else if (xhr.status === 500) {
                    error = 'Error interno del servidor.';
                } else if (xhr.status === 419) {
                    error = 'Sesi&oacute;n expirada. Recargue la p&aacute;gina.';
                }
                
                toastr.error(error);
            },
            complete: function() {
                $('button[type="submit"]', '#formAgregarItem').prop('disabled', false);
            }
        });
    });
    
    // Eliminar item via AJAX
    $(document).on('click', '.btn-eliminar-item', function(e) {
        e.preventDefault();
        
        if (!confirm('¿Está seguro de eliminar este item?')) {
            return;
        }
        
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var url = $btn.data('url');
        
        $.ajax({
            url: url,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $btn.prop('disabled', true);
            },
            success: function(response) {
                $row.fadeOut(300, function() {
                    $(this).remove();
                    
                    // Si no quedan items, mostrar mensaje
                    if ($('#items-table-body tr').length === 0) {
                        $('#items-container').html(`
                            <div id="no-items-message" class="text-center text-muted">
                                <i class="voyager-info-circled" style="font-size: 48px;"></i><br>
                                No se han agregado items aún.<br>
                                <small>Use el formulario anterior para agregar medicamentos y materiales.</small>
                            </div>
                        `);
                    }
                });
                
                toastr.success('Item eliminado exitosamente');
            },
            error: function(xhr) {
                var error = xhr.responseJSON?.error || 'Error al eliminar el item';
                toastr.error(error);
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
@stop