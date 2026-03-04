@extends('voyager::master')

@section('page_title', 'Detalle de Recepción')

@php
    $breadcrumbs = [
        ['url' => route('voyager.dashboard'), 'name' => 'Dashboard'],
        ['url' => route('recepciones.index'), 'name' => 'Recepciones de Stock'],
        ['url' => '', 'name' => $recepcion->nro_remito],
    ];
@endphp

@section('page_header')
<div class="container-fluid">
    <h1 class="page_title">
        <i class="voyager-eye"></i> Recepción de Stock 
        <small>{{ $recepcion->nro_remito }}</small>
        <div class="pull-right">
            @if($recepcion->puedeSerEditada())
                <a href="{{ route('recepciones.edit', $recepcion->id_recepcion) }}" class="btn btn-primary btn-sm">
                    <i class="voyager-edit"></i> Editar
                </a>
            @endif
            <a href="{{ route('recepciones.index') }}" class="btn btn-warning btn-sm">
                <i class="voyager-list"></i> Volver al Listado
            </a>
        </div>
    </h1>
</div>
@stop

@section('content')
<div class="page-content browse container-fluid">
    @include('voyager::alerts')
    
    <!-- Información general -->
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="voyager-receipt"></i> Información de la Recepción</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="dl-horizontal">
                                <dt>Número de Remito:</dt>
                                <dd><strong>{{ $recepcion->nro_remito }}</strong></dd>
                                
                                <dt>Fecha de Recepción:</dt>
                                <dd>{{ $recepcion->fecha_recepcion->format('d/m/Y') }}</dd>
                                
                                <dt>Estado:</dt>
                                <dd>
                                    @if($recepcion->estado === 'BORRADOR')
                                        <span class="label label-warning">{{ $recepcion->descripcion_estado }}</span>
                                    @elseif($recepcion->estado === 'CONFIRMADA') 
                                        <span class="label label-success">{{ $recepcion->descripcion_estado }}</span>
                                    @else
                                        <span class="label label-danger">{{ $recepcion->descripcion_estado }}</span>
                                    @endif
                                </dd>
                                
                                <dt>Usuario Creador:</dt>
                                <dd>{{ $recepcion->usuarioCreador->name ?? 'Sistema' }}</dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <dl class="dl-horizontal">
                                <dt>Fecha de Creación:</dt>
                                <dd>{{ $recepcion->created_at->format('d/m/Y H:i') }}</dd>
                                
                                @if($recepcion->fecha_confirmacion)
                                    <dt>Fecha de Confirmación:</dt>
                                    <dd>{{ $recepcion->fecha_confirmacion->format('d/m/Y H:i') }}</dd>
                                    
                                    <dt>Confirmado por:</dt>
                                    <dd>{{ $recepcion->usuarioConfirmador->name ?? 'Sistema' }}</dd>
                                @endif

                                @if($recepcion->fecha_anulacion)
                                    <dt>Fecha de Anulación:</dt>
                                    <dd>{{ $recepcion->fecha_anulacion->format('d/m/Y H:i') }}</dd>
                                    
                                    <dt>Anulado por:</dt>
                                    <dd>{{ $recepcion->usuarioAnulador->name ?? 'Sistema' }}</dd>
                                @endif
                                
                                <dt>Total Items:</dt>
                                <dd><span class="badge">{{ $recepcion->total_items }}</span></dd>
                                
                                <dt>Cantidad Total:</dt>
                                <dd><strong>{{ number_format($recepcion->cantidad_total, 0) }}</strong> unidades</dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($recepcion->observaciones)
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <strong>Observaciones:</strong>
                                <p class="well well-sm">{{ $recepcion->observaciones }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="voyager-settings"></i> Acciones</h3>
                </div>
                <div class="panel-body text-center">
                    @if($recepcion->puedeSerEditada())
                        <a href="{{ route('recepciones.edit', $recepcion->id_recepcion) }}" 
                           class="btn btn-primary btn-block">
                            <i class="voyager-edit"></i> Editar Recepción
                        </a>
                        <br>
                    @endif
                    
                    @if($recepcion->puedeSerConfirmada())
                        <form action="{{ route('recepciones.confirmar', $recepcion->id_recepcion) }}" 
                              method="POST" style="margin-bottom: 10px;" 
                              onsubmit="return confirm('¿Está seguro de confirmar esta recepción? Esta acción aplicará todos los items al stock y no se podrá deshacer.')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="voyager-check"></i> Confirmar Recepción
                            </button>
                        </form>
                    @endif
                    
                    @if($recepcion->puedeSerAnulada())
                        <form action="{{ route('recepciones.anular', $recepcion->id_recepcion) }}" 
                              method="POST" style="margin-bottom: 10px;" 
                              onsubmit="return confirm('¿Está seguro de anular esta recepción? Esta acción revertirá todos los movimientos de stock aplicados.')">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="voyager-x"></i> Anular Recepción
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('recepciones.index') }}" class="btn btn-default btn-block">
                        <i class="voyager-list"></i> Volver al Listado
                    </a>
                    
                    <br>
                    
                    @if($recepcion->puedeSerEditada() && $recepcion->items->count() == 0)
                        <form action="{{ route('recepciones.destroy', $recepcion->id_recepcion) }}" 
                              method="POST" style="margin-top: 10px;" 
                              onsubmit="return confirm('¿Está seguro de eliminar esta recepción?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="voyager-trash"></i> Eliminar Recepción
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Items de la recepción -->
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
                    @if($recepcion->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Item</th>
                                        <th>Cantidades</th>
                                        <th>Lote</th>
                                        <th>Vencimiento</th>
                                        <th>Estado</th>
                                        @if($recepcion->estado === 'CONFIRMADA')
                                            <th>Movimiento</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recepcion->items as $item)
                                        <tr>
                                            <td>
                                                @if($item->tipo_item === 'MEDICAMENTO')
                                                    <span class="label label-info">
                                                        <i class="voyager-plus"></i> Medicamento
                                                    </span>
                                                @else
                                                    <span class="label label-warning">
                                                        <i class="voyager-dot"></i> Material
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $item->nombre_item }}</strong>
                                                
                                                @if($item->medicamento && ($item->medicamento->es_psicotropico || $item->medicamento->es_estupefaciente))
                                                    <br><small class="text-danger">
                                                        <i class="voyager-warning"></i>
                                                        @if($item->medicamento->es_psicotropico) Psicotrópico @endif
                                                        @if($item->medicamento->es_estupefaciente) Estupefaciente @endif
                                                    </small>
                                                @endif
                                                
                                                @if($item->item && $item->item->descripcion)
                                                    <br><small class="text-muted">{{ $item->item->descripcion }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong style="font-size: 1.2em; color: #28a745;">
                                                    +{{ number_format($item->cantidad, 2) }}
                                                </strong>
                                                @if($item->item)
                                                    <br><small class="text-muted">{{ $item->item->unidad_medida ?? 'unidades' }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->nro_lote)
                                                    <code>{{ $item->nro_lote }}</code>
                                                @else
                                                    <span class="text-muted">Sin lote</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->fecha_vencimiento)
                                                    {{ $item->fecha_vencimiento->format('d/m/Y') }}
                                                    @if($item->fecha_vencimiento->lt(now()->addDays(30)))
                                                        <br><small class="text-warning">
                                                            <i class="voyager-warning"></i> Próximo a vencer
                                                        </small>
                                                    @elseif($item->fecha_vencimiento->lt(now()))
                                                        <br><small class="text-danger">
                                                            <i class="voyager-x"></i> Vencido
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No especificado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($recepcion->estado === 'CONFIRMADA')
                                                    <span class="label label-success">Aplicado al stock</span>
                                                @elseif($recepcion->estado === 'ANULADA') 
                                                    <span class="label label-danger">Anulado</span>
                                                @else
                                                    <span class="label label-default">Pendiente</span>
                                                @endif
                                            </td>
                                            @if($recepcion->estado === 'CONFIRMADA')
                                                <td>
                                                    @if($item->movimiento_stock_id)
                                                        <a href="#" class="btn btn-xs btn-info" 
                                                           title="Ver movimiento de stock">
                                                            <i class="voyager-activity"></i> #{{ $item->movimiento_stock_id }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Sin movimiento</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Resumen por tipo -->
                        <div class="row">
                            @php
                                $resumen = $recepcion->items->groupBy('tipo_item');
                            @endphp
                            
                            @foreach($resumen as $tipo => $items)
                                <div class="col-md-6">
                                    <div class="well well-sm">
                                        <h4 style="margin-top: 0;">
                                            @if($tipo === 'MEDICAMENTO')
                                                <i class="voyager-plus text-info"></i> Medicamentos
                                            @else
                                                <i class="voyager-dot text-warning"></i> Materiales
                                            @endif
                                        </h4>
                                        <ul class="list-unstyled">
                                            <li><strong>Items:</strong> {{ $items->count() }}</li>
                                            <li><strong>Cantidad total:</strong> {{ number_format($items->sum('cantidad'), 0) }} unidades</li>
                                            @if($items->where('nro_lote', '!=', null)->count() > 0)
                                                <li><small class="text-muted">{{ $items->where('nro_lote', '!=', null)->count() }} items con lote</small></li>
                                            @endif
                                            @if($items->where('fecha_vencimiento', '!=', null)->count() > 0)
                                                <li><small class="text-muted">{{ $items->where('fecha_vencimiento', '!=', null)->count() }} items con vencimiento</small></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted" style="padding: 40px;">
                            <i class="voyager-info-circled" style="font-size: 48px;"></i>
                            <h3>No hay items en esta recepción</h3>
                            <p>Esta recepción no tiene items agregados.</p>
                            @if($recepcion->puedeSerEditada())
                                <a href="{{ route('recepciones.edit', $recepcion->id_recepcion) }}" 
                                   class="btn btn-primary">
                                    <i class="voyager-edit"></i> Agregar Items
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de estados -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="voyager-clock"></i> Historial de Estados</h3>
                </div>
                <div class="panel-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h4 class="timeline-title">Recepción Creada</h4>
                                <p class="timeline-description">
                                    Estado: <strong>BORRADOR</strong><br>
                                    Usuario: {{ $recepcion->usuarioCreador->name ?? 'Sistema' }}<br>
                                    Fecha: {{ $recepcion->created_at->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        </div>

                        @if($recepcion->fecha_confirmacion)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Recepción Confirmada</h4>
                                    <p class="timeline-description">
                                        Estado: <strong>CONFIRMADA</strong><br>
                                        Usuario: {{ $recepcion->usuarioConfirmador->name ?? 'Sistema' }}<br>
                                        Fecha: {{ $recepcion->fecha_confirmacion->format('d/m/Y H:i:s') }}<br>
                                        <small class="text-muted">Stock aplicado automáticamente</small>
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($recepcion->fecha_anulacion)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Recepción Anulada</h4>
                                    <p class="timeline-description">
                                        Estado: <strong>ANULADA</strong><br>
                                        Usuario: {{ $recepcion->usuarioAnulador->name ?? 'Sistema' }}<br>
                                        Fecha: {{ $recepcion->fecha_anulacion->format('d/m/Y H:i:s') }}<br>
                                        <small class="text-muted">Movimientos de stock revertidos</small>
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .dl-horizontal dt {
        width: 180px;
    }
    .dl-horizontal dd {
        margin-left: 200px;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -22px;
        top: 30px;
        bottom: -20px;
        width: 2px;
        background: #e7e7e7;
    }
    
    .timeline-item:last-child:before {
        display: none;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e7e7e7;
    }
    
    .timeline-title {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 16px;
        font-weight: 600;
    }
    
    .timeline-description {
        margin-bottom: 0;
        color: #666;
    }
    
    .bg-info { background-color: #5bc0de; }
    .bg-success { background-color: #5cb85c; }
    .bg-danger { background-color: #d9534f; }
</style>
@stop