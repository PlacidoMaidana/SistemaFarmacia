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
                                <strong>Interno:</strong> {{ $origen->interno->nombre_completo ?? 'N/A' }}
                                <br>
                                @if($tipoOrigen == 'receta')
                                    <strong>Receta:</strong> #{{ $origen->id_receta }} - {{ \Carbon\Carbon::parse($origen->fecha)->format('d/m/Y') }}
                                    @if($origen->medico)
                                        <br><strong>Médico:</strong> {{ $origen->medico->nombre_completo }}
                                    @endif
                                @elseif($tipoOrigen == 'tratamiento')
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

                            {{-- Selector de Medicamento --}}
                            <div class="form-group" id="div_medicamento" style="display: none;">
                                <label for="id_medicamento" class="control-label">
                                    <span class="text-danger">*</span> Medicamento
                                </label>
                                <select class="form-control select2" id="id_medicamento" name="id_medicamento">
                                    <option value="">Seleccione un medicamento...</option>
                                    @foreach($medicamentos as $medicamento)
                                        <option value="{{ $medicamento->id_medicamento }}" {{ old('id_medicamento') == $medicamento->id_medicamento ? 'selected' : '' }}>
                                            {{ $medicamento->nombre }}
                                            @if($medicamento->nombre_comercial)
                                                ({{ $medicamento->nombre_comercial }})
                                            @endif
                                            @if($medicamento->concentracion)
                                                - {{ $medicamento->concentracion }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Selector de Material --}}
                            <div class="form-group" id="div_material" style="display: none;">
                                <label for="id_material" class="control-label">
                                    <span class="text-danger">*</span> Material de Enfermería
                                </label>
                                <select class="form-control select2" id="id_material" name="id_material">
                                    <option value="">Seleccione un material...</option>
                                    @foreach($materiales as $material)
                                        <option value="{{ $material->id_material }}" {{ old('id_material') == $material->id_material ? 'selected' : '' }}>
                                            {{ $material->nombre }}
                                            @if($material->descripcion)
                                                - {{ $material->descripcion }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
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
                                        <label for="lote" class="control-label">
                                            Lote
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="lote" 
                                               name="lote" 
                                               maxlength="50" 
                                               value="{{ old('lote') }}">
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

@section('javascript')
<script>
    $(document).ready(function() {
        // Inicializar select2
        $('.select2').select2();

        // Manejar cambio de tipo de item
        $('#tipo_item').on('change', function() {
            var tipo = $(this).val();
            
            // Ocultar y limpiar ambos
            $('#div_medicamento').hide();
            $('#div_material').hide();
            $('#id_medicamento').val('').prop('required', false);
            $('#id_material').val('').prop('required', false);
            
            // Mostrar el seleccionado
            if (tipo === 'medicamento') {
                $('#div_medicamento').show();
                $('#id_medicamento').prop('required', true);
            } else if (tipo === 'material') {
                $('#div_material').show();
                $('#id_material').prop('required', true);
            }
        });

        // Si hay valor previo (old), activarlo
        @if(old('tipo_item'))
            $('#tipo_item').trigger('change');
        @endif
    });
</script>
@endsection
