@extends('voyager::master')

@section('page_title', 'Nueva Receta')

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            <form id="form-receta" action="{{ route('recetas.dispensaciones.store') }}" method="POST">
                @csrf

                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="voyager-document"></i> Nueva Receta</h3>
                    </div>

                    <div class="panel-body">

                        {{-- MÉDICO --}}
                        <div class="form-group">
                            <label>Médico <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="medico_nombre" class="form-control" readonly placeholder="Seleccione un médico">
                                <input type="hidden" id="id_medico" name="id_medico" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" onclick="abrirModalMedicos()">
                                        <i class="voyager-search"></i> Buscar
                                    </button>
                                </span>
                            </div>
                        </div>

                        {{-- INTERNO --}}
                        <div class="form-group">
                            <label>Interno <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="interno_nombre" class="form-control" readonly placeholder="Seleccione un interno">
                                <input type="hidden" id="id_interno" name="id_interno" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" onclick="abrirModalInternos()">
                                        <i class="voyager-search"></i> Buscar
                                    </button>
                                </span>
                            </div>
                        </div>

                        {{-- Otros campos existentes --}}
                        <div class="form-group">
                            <label>Fecha de Emisión</label>
                            <input type="date" name="fecha_emision" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Número de Receta</label>
                            <input type="text" name="numero_receta" class="form-control" placeholder="REC-2026-XXXX">
                        </div>

                        <div class="form-group">
                            <label>Tipo de Receta</label>
                            <select name="tipo_receta" class="form-control" required>
                                <option value="simple">Simple</option>
                                <option value="psicotropica">Psicotrópica</option>
                                <option value="estupefaciente">Estupefaciente</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="4"></textarea>
                        </div>

                    </div>

                    <div class="panel-footer text-right">
                        <button type="submit" class="btn btn-primary">Guardar Receta</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== MODALES ==================== --}}

{{-- Modal Médicos --}}
<div class="modal fade" id="modalMedicos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Seleccionar Médico</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table id="tablaMedicos" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Especialidad</th>
                            <th>Matrícula</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Internos --}}
<div class="modal fade" id="modalInternos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Seleccionar Interno</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table id="tablaInternos" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>LPU</th>
                            <th>Pabellón</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script>
$(document).ready(function() {

    // ====================== MÉDICOS ======================
    var tablaMedicos = $('#tablaMedicos').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('api.medicos') }}",
        columns: [
            { data: 'nombre_y_apellido' },
            { data: 'especialidad' },
            { data: 'matricula' },
            { 
                data: null,
                render: function(data) {
                    return `<button class="btn btn-sm btn-success seleccionar-medico" data-id="${data.id_medico}" data-nombre="${data.nombre_y_apellido}">Seleccionar</button>`;
                }
            }
        ]
    });

    // ====================== INTERNOS ======================
    var tablaInternos = $('#tablaInternos').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('api.internos') }}",
        columns: [
            { data: 'nombre_y_apellido' },
            { data: 'lpu' },
            { data: 'pabellon_nombre' },
            { 
                data: null,
                render: function(data) {
                    return `<button class="btn btn-sm btn-success seleccionar-interno" data-id="${data.id_interno}" data-nombre="${data.nombre_y_apellido}">Seleccionar</button>`;
                }
            }
        ]
    });

    // Seleccionar Médico
    $('#tablaMedicos').on('click', '.seleccionar-medico', function() {
        $('#id_medico').val($(this).data('id'));
        $('#medico_nombre').val($(this).data('nombre'));
        $('#modalMedicos').modal('hide');
    });

    // Seleccionar Interno
    $('#tablaInternos').on('click', '.seleccionar-interno', function() {
        $('#id_interno').val($(this).data('id'));
        $('#interno_nombre').val($(this).data('nombre'));
        $('#modalInternos').modal('hide');
    });

});

// Abrir modales
function abrirModalMedicos() { $('#modalMedicos').modal('show'); }
function abrirModalInternos() { $('#modalInternos').modal('show'); }
</script>
@endsection