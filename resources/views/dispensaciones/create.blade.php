@extends('voyager::master')

@section('page_title', 'Nueva Dispensación')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-plus"></i> Nueva Dispensación
        </h1>
    </div>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('dispensaciones.store') }}" method="POST" class="form-edit-add">
                        @csrf

                        <input type="hidden" name="tipo_origen" value="{{ $tipoOrigen }}">
                        <input type="hidden" name="id_origen" value="{{ $idOrigen }}">
                        <input type="hidden" name="id_interno" value="{{ $origen->id_interno }}">

                        <div class="panel-body">

                            {{-- Información del Origen --}}
                            <div class="alert alert-info">
                                <strong>Interno:</strong> {{ $origen->interno->nombre_y_apellido ?? 'N/A' }}
                                <br>
                                @if($tipoOrigen == 'receta')
                                    <strong>Receta:</strong> #{{ $origen->id_receta }} - {{ \Carbon\Carbon::parse($origen->fecha_emision)->format('d/m/Y') }}
                                    @if($origen->medico)
                                        <br><strong>Médico:</strong> {{ $origen->medico->nombre_y_apellido }}
                                    @endif
                                @elseif($tipoOrigen == 'tratamiento_cronicos')
                                    <strong>Tratamiento:</strong> #{{ $origen->id_tratamiento }}
                                    @if($origen->medicamento)
                                        <br><strong>Medicamento del Tratamiento:</strong> {{ $origen->medicamento->nombre }}
                                    @endif
                                @endif
                            </div>

                            {{-- Selector de Tipo de Item --}}
                            <div class="form-group">
                                <label for="tipo_item" class="control-label">
                                    <span class="text-danger">*</span> Tipo de Item
                                </label>
                                <select class="form-control" id="tipo_item" name="tipo_item" required>
                                    <option value="">Seleccione...</option>
                                    <option value="medicamento" {{ old('tipo_item') == 'medicamento' ? 'selected' : '' }}>Medicamento</option>
                                    <option value="material" {{ old('tipo_item') == 'material' ? 'selected' : '' }}>Material de Enfermería</option>
                                </select>
                            </div>

                            {{-- Selector de Medicamento con Modal --}}
                            <div class="form-group" id="div_medicamento" style="display: none;">
                                <label for="medicamento_nombre" class="control-label">
                                    <span class="text-danger">*</span> Medicamento
                                </label>
                                <div class="input-group">
                                    <input type="text" id="medicamento_nombre" class="form-control" readonly 
                                           placeholder="Seleccione un medicamento"
                                           value="{{ old('medicamento_nombre') }}">
                                    <input type="hidden" name="id_medicamento" id="id_medicamento" 
                                           value="{{ old('id_medicamento') }}">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalMedicamentos">
                                            <i class="voyager-search"></i> Buscar
                                        </button>
                                    </span>
                                </div>
                            </div>

                            {{-- Selector de Material con Modal --}}
                            <div class="form-group" id="div_material" style="display: none;">
                                <label for="material_nombre" class="control-label">
                                    <span class="text-danger">*</span> Material de Enfermería
                                </label>
                                <div class="input-group">
                                    <input type="text" id="material_nombre" class="form-control" readonly 
                                           placeholder="Seleccione un material"
                                           value="{{ old('material_nombre') }}">
                                    <input type="hidden" name="id_material" id="id_material" 
                                           value="{{ old('id_material') }}">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalMateriales">
                                            <i class="voyager-search"></i> Buscar
                                        </button>
                                    </span>
                                </div>
                            </div>

                            {{-- Cantidad y Unidad --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cantidad" class="control-label">
                                            <span class="text-danger">*</span> Cantidad
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="cantidad" 
                                               name="cantidad" 
                                               step="0.01" 
                                               min="0.01" 
                                               value="{{ old('cantidad') }}" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unidad_medida" class="control-label">
                                            Unidad de Medida
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="unidad_medida" 
                                               name="unidad_medida" 
                                               maxlength="30" 
                                               value="{{ old('unidad_medida') }}" 
                                               placeholder="Ej: comprimidos, ml, unidades">
                                    </div>
                                </div>
                            </div>

                            {{-- Fecha --}}
                            <div class="form-group">
                                <label for="fecha" class="control-label">
                                    <span class="text-danger">*</span> Fecha de Dispensación
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha" 
                                       name="fecha" 
                                       value="{{ old('fecha', date('Y-m-d')) }}" 
                                       required>
                            </div>

                            {{-- Lote y Vencimiento --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nro_lote" class="control-label">
                                            Lote
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="nro_lote" 
                                               name="nro_lote" 
                                               maxlength="50" 
                                               value="{{ old('nro_lote') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_vencimiento" class="control-label">
                                            Fecha de Vencimiento
                                        </label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="fecha_vencimiento" 
                                               name="fecha_vencimiento" 
                                               value="{{ old('fecha_vencimiento') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Observaciones --}}
                            <div class="form-group">
                                <label for="observaciones" class="control-label">
                                    Observaciones
                                </label>
                                <textarea class="form-control" 
                                          id="observaciones" 
                                          name="observaciones" 
                                          rows="3">{{ old('observaciones') }}</textarea>
                            </div>

                        </div>{{-- panel-body --}}

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">
                                <i class="voyager-check"></i> Guardar Dispensación
                            </button>
                            <a href="{{ route('dispensaciones.por-origen', ['tipo_origen' => $tipoOrigen, 'id_origen' => $idOrigen]) }}" 
                               class="btn btn-default">
                                <i class="voyager-x"></i> Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@stop

<!-- Modal Medicamentos -->
<div class="modal fade" id="modalMedicamentos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Seleccionar Medicamento</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table id="tablaMedicamentos" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Nombre Comercial</th>
                            <th>Concentración</th>
                            <th>Stock</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medicamentos as $medicamento)
                            <tr>
                                <td>{{ $medicamento->nombre }}</td>
                                <td>{{ $medicamento->nombre_comercial ?? '-' }}</td>
                                <td>{{ $medicamento->concentracion ?? '-' }}</td>
                                <td>{{ $medicamento->stock_actual ?? 0 }}</td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm seleccionar-medicamento"
                                            data-id="{{ $medicamento->id_medicamento }}"
                                            data-nombre="{{ $medicamento->nombre }}{{ $medicamento->nombre_comercial ? ' (' . $medicamento->nombre_comercial . ')' : '' }}{{ $medicamento->concentracion ? ' - ' . $medicamento->concentracion : '' }}">
                                        <i class="voyager-check"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Materiales -->
<div class="modal fade" id="modalMateriales" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Seleccionar Material de Enfermería</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table id="tablaMateriales" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Código</th>
                            <th>Stock</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materiales as $material)
                            <tr>
                                <td>{{ $material->nombre }}</td>
                                <td>{{ $material->descripcion ?? '-' }}</td>
                                <td>{{ $material->codigo ?? '-' }}</td>
                                <td>{{ $material->stock_actual ?? 0 }}</td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm seleccionar-material"
                                            data-id="{{ $material->id_material }}"
                                            data-nombre="{{ $material->nombre }}{{ $material->descripcion ? ' - ' . $material->descripcion : '' }}">
                                        <i class="voyager-check"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('javascript')
<script>
    $(document).ready(function() {
        // Inicializar DataTables para búsqueda en modales
        $('#tablaMedicamentos').DataTable({
            "language": {
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ registros",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ medicamentos",
                "infoEmpty": "Mostrando 0 a 0 de 0 medicamentos",
                "infoFiltered": "(filtrado de _MAX_ total)",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "emptyTable": "No hay medicamentos disponibles"
            },
            "pageLength": 10,
            "order": [[0, "asc"]]
        });

        $('#tablaMateriales').DataTable({
            "language": {
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ registros",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ materiales",
                "infoEmpty": "Mostrando 0 a 0 de 0 materiales",
                "infoFiltered": "(filtrado de _MAX_ total)",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "emptyTable": "No hay materiales disponibles"
            },
            "pageLength": 10,
            "order": [[0, "asc"]]
        });

        // Seleccionar medicamento desde modal
        $('.seleccionar-medicamento').click(function() {
            $('#id_medicamento').val($(this).data('id'));
            $('#medicamento_nombre').val($(this).data('nombre'));
            $('#modalMedicamentos').modal('hide');
        });

        // Seleccionar material desde modal
        $('.seleccionar-material').click(function() {
            $('#id_material').val($(this).data('id'));
            $('#material_nombre').val($(this).data('nombre'));
            $('#modalMateriales').modal('hide');
        });

        // Manejar cambio de tipo de item
        $('#tipo_item').on('change', function() {
            var tipo = $(this).val();
            
            // Ocultar y limpiar ambos
            $('#div_medicamento').hide();
            $('#div_material').hide();
            $('#id_medicamento').val('');
            $('#medicamento_nombre').val('');
            $('#id_material').val('');
            $('#material_nombre').val('');
            
            // Mostrar el seleccionado
            if (tipo === 'medicamento') {
                $('#div_medicamento').show();
            } else if (tipo === 'material') {
                $('#div_material').show();
            }
        });

        // Si hay valor previo (old), activarlo
        @if(old('tipo_item'))
            $('#tipo_item').trigger('change');
        @endif
    });
</script>
@endsection
