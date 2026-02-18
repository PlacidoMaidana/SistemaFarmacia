@extends('voyager::master')

@php
    $isEdit = (isset($dataTypeContent) && !is_null($dataTypeContent->getKey()));
@endphp
@section('page_title', $dataType->getTranslatedAttribute('display_name_singular') . ($isEdit ? ' - Editar' : ' - Crear'))

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            @php
                // Determine edit vs create and prepare form action
                $isEdit = $isEdit ?? (isset($dataTypeContent) && !is_null($dataTypeContent->getKey()));
                $data = $data ?? ($dataTypeContent ?? (object)[]);
                $formAction = $formAction ?? ($isEdit ? route('voyager.' . $dataType->slug . '.update', $dataTypeContent->getKey()) : route('voyager.' . $dataType->slug . '.store'));
            @endphp

            <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf

                @if ($isEdit)
                    {{ method_field('PUT') }}
                @endif

                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-plus"></i> 
                            {{ $dataType->getTranslatedAttribute('display_name_singular') }}
                            {{ $isEdit ? ' - Editar' : ' - Crear' }}
                        </h3>
                    </div>

                    <div class="panel-body">

                        <!-- CAMPO MÉDICO -->
                        <div class="form-group">
                            <label>Médico <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="medico_nombre" class="form-control" readonly 
                                       placeholder="Seleccione un médico"
                                       value="{{ old('medico_nombre', $data->medico ? $data->medico->nombre_y_apellido : '') }}">
                                <input type="hidden" name="id_medico" id="id_medico" 
                                       value="{{ old('id_medico', $data->id_medico ?? '') }}" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalMedicos">
                                        <i class="voyager-search"></i> Buscar
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- CAMPO INTERNO -->
                        <div class="form-group">
                            <label>Interno <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="interno_nombre" class="form-control" readonly 
                                       placeholder="Seleccione un interno"
                                       value="{{ old('interno_nombre', $data->interno ? $data->interno->nombre_y_apellido : '') }}">

                                <input type="hidden" name="id_interno" id="id_interno" 
                                       value="{{ old('id_interno', $data->id_interno ?? '') }}" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalInternos">
                                        <i class="voyager-search"></i> Buscar
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- CAMPOS AUTOMÁTICOS DEL BREAD (los que no modificamos) -->
                        <!-- CAMPO USUARIO (autocompletar con usuario autenticado) -->
                        <div class="form-group">
                            <label>Usuario (Farmacéutico)</label>
                            <input type="text" class="form-control" readonly
                                   value="{{ old('usuario_nombre', optional(Auth::user())->name ?? '') }}">
                            <input type="hidden" name="id_usuario" id="id_usuario"
                                   value="{{ old('id_usuario', $data->id_usuario ?? (Auth::id() ?? '')) }}">
                        </div>

                        <!-- CAMPO IMAGEN: subir foto de la receta en papel -->
                        <div class="form-group">
                            <label>Imagen de la receta (opcional)</label>
                            <div id="imagen-preview" style="margin-bottom:10px;">
                                @if(!empty($data->imagen))
                                    <div class="imagen-container">
                                        <img src="{{ asset('storage/' . $data->imagen) }}" alt="Imagen receta" style="max-width:100%; max-height:300px; border:1px solid #ddd; padding:5px; border-radius:4px;">
                                    </div>
                                @endif
                            </div>
                            <input type="file" name="imagen" id="imagen-input" accept="image/*" class="form-control">
                            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo recomendado: 2MB</small>
                        </div>

                        <!-- FECHA EMISION con icono calendario -->
                        <div class="form-group">
                            <label>Fecha Emisión <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="fecha_emision" id="fecha_emision" class="form-control" placeholder="YYYY-MM-DD"
                                       value="{{ old('fecha_emision', $data->fecha_emision ?? '') }}" autocomplete="off">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" id="btn_fecha_emision">
                                        <i class="voyager-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- TIPO RECETA como select (valores controlados - ENUM) -->
                        <div class="form-group">
                            <label>Tipo Receta <span class="text-danger">*</span></label>
                            <select name="tipo_receta" id="tipo_receta" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <option value="electronica" {{ old('tipo_receta', $data->tipo_receta ?? '') == 'electronica' ? 'selected' : '' }}>Electrónica</option>
                                <option value="papel" {{ old('tipo_receta', $data->tipo_receta ?? '') == 'papel' ? 'selected' : '' }}>Manual/Papel</option>
                                <option value="cronica" {{ old('tipo_receta', $data->tipo_receta ?? '') == 'cronica' ? 'selected' : '' }}>Crónica</option>
                                <option value="aguda" {{ old('tipo_receta', $data->tipo_receta ?? '') == 'aguda' ? 'selected' : '' }}>Aguda</option>
                                <option value="archivada" {{ old('tipo_receta', $data->tipo_receta ?? '') == 'archivada' ? 'selected' : '' }}>Archivada</option>
                                <option value="anulada" {{ old('tipo_receta', $data->tipo_receta ?? '') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                                <option value="pendiente" {{ old('tipo_receta', $data->tipo_receta ?? '') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="repetible" {{ old('tipo_receta', $data->tipo_receta ?? '') == 'repetible' ? 'selected' : '' }}>Repetible</option>
                                <option value="unica" {{ old('tipo_receta', $data->tipo_receta ?? '') == 'unica' ? 'selected' : '' }}>Única</option>
                            </select>
                        </div>

                        @php
                            $dataTypeRows = (isset($id) || (!empty($data) && isset($data->id_receta))) ? $dataType->editRows : $dataType->addRows;
                        @endphp

                        @foreach ($dataTypeRows as $row)
                            @if (!in_array($row->field, ['id_medico', 'medico_nombre', 'id_interno', 'interno_nombre', 'id_usuario', 'fecha_emision', 'tipo_receta', 'medico', 'interno', 'usuario', 'dispensaciones', 'id_receta']) && $row->type !== 'relationship')
                                {!! app('voyager')->formField($row, $dataType, $data) !!}
                            @endif
                        @endforeach

                    </div>

                    <div class="panel-footer text-right">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Modal Médicos (versión simple para prueba) -->
<div class="modal fade" id="modalMedicos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Seleccionar Médico</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Especialidad</th>
                            <th>Matrícula</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Models\Medico::all() as $medico)
                            <tr>
                                <td>{{ $medico->nombre_y_apellido }}</td>
                                <td>{{ $medico->Especialidad ?? 'N/A' }}</td>
                                <td>{{ $medico->matricula }}</td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm seleccionar-medico"
                                            data-id="{{ $medico->id_medico }}"
                                            data-nombre="{{ $medico->nombre_y_apellido }}">
                                        Seleccionar
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

<!-- Modal Internos (similar) -->
<div class="modal fade" id="modalInternos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Seleccionar Interno</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>LPU</th>
                            <th>Pabellón</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Models\Interno::all() as $interno)
                            <tr>
                                <td>{{ $interno->nombre_y_apellido }}</td>
                                <td>{{ $interno->lpu ?? 'N/A' }}</td>
                                <td>{{ $interno->pabellon ? $interno->pabellon->pabellon : 'N/A' }}</td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm seleccionar-interno"
                                            data-id="{{ $interno->id_interno }}"
                                            data-nombre="{{ $interno->nombre_y_apellido }}">
                                        Seleccionar
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

@endsection

@section('javascript')
<script>
$(document).ready(function() {
    // Seleccionar médico desde modal
    $('.seleccionar-medico').click(function() {
        $('#id_medico').val($(this).data('id'));
        $('#medico_nombre').val($(this).data('nombre'));
        $('#modalMedicos').modal('hide');
    });

    // Seleccionar interno desde modal
    $('.seleccionar-interno').click(function() {
        $('#id_interno').val($(this).data('id'));
        $('#interno_nombre').val($(this).data('nombre'));
        $('#modalInternos').modal('hide');
    });

    // Inicializar datepicker para Fecha Emision y mostrar con botón
    if ($('#fecha_emision').length) {
        $('#fecha_emision').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });
        $('#btn_fecha_emision').click(function() {
            $('#fecha_emision').data('DateTimePicker').show();
        });
    }

    // Vista previa de imagen al seleccionar archivo
    $('#imagen-input').on('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.match('image.*')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Crear o actualizar la imagen de vista previa
                let preview = $('#imagen-preview');
                preview.html(
                    '<div class="imagen-container">' +
                    '<img src="' + e.target.result + '" alt="Vista previa" ' +
                    'style="max-width:100%; max-height:300px; border:1px solid #ddd; padding:5px; border-radius:4px;">' +
                    '</div>'
                );
                
                // Mostrar mensaje de confirmación
                if (typeof toastr !== 'undefined') {
                    toastr.success('Imagen cargada. Recuerde guardar para aplicar los cambios.');
                }
            };
            reader.readAsDataURL(file);
        } else if (file) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Por favor seleccione un archivo de imagen válido.');
            } else {
                alert('Por favor seleccione un archivo de imagen válido.');
            }
        }
    });

    // Validación cliente antes de submit
        $('form').on('submit', function(e) {
        var missing = [];
        if (!$('#id_interno').val()) missing.push('Interno');
        if (!$('#id_medico').val()) missing.push('Médico');
        if (!$('#fecha_emision').val()) missing.push('Fecha Emisión');
        if (!$('#tipo_receta').val()) missing.push('Tipo de Receta');
        if (!missing.length) return true;
        e.preventDefault();
        var msg = 'Complete los siguientes campos obligatorios: ' + missing.join(', ');
        if (typeof toastr !== 'undefined') {
            toastr.error(msg);
        } else {
            alert(msg);
        }
        $('html, body').animate({ scrollTop: $('.page-content').offset().top }, 200);
        return false;
    });
});
</script>
@endsection