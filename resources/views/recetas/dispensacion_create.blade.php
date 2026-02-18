@extends('voyager::master')

@section('page_title', 'Nueva Dispensación - Receta #' . $receta->id_receta)

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-plus"></i> Nueva Dispensación - Receta #{{ $receta->id_receta }}
        </h1>
        <div class="breadcrumb">
            <a href="{{ route('voyager.dashboard') }}">Dashboard</a>
            <span class="divider">/</span>
            <a href="{{ route('voyager.recetas.index') }}">Recetas</a>
            <span class="divider">/</span>
            <a href="{{ route('recetas.dispensaciones.index', $receta->id_receta) }}">Dispensaciones</a>
            <span class="divider">/</span>
            <span class="active">Nueva</span>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">Agregar Dispensación a Receta #{{ $receta->id_receta }}</h3>
                    </div>

                    <div class="panel-body">
                        <form action="{{ route('recetas.dispensaciones.store', $receta->id_receta) }}" method="POST">
                            @csrf

                            <!-- MEDICAMENTO: Modal con DataTable -->
                            <div class="form-group">
                                <label>Medicamento <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" id="medicamento_nombre" class="form-control" readonly 
                                           placeholder="Seleccione un medicamento">
                                    <input type="hidden" name="id_medicamento" id="id_medicamento" required>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalMedicamentos">
                                            <i class="voyager-search"></i> Buscar
                                        </button>
                                    </span>
                                </div>
                                @error('id_medicamento') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>Cantidad *</label>
                                <input type="number" name="cantidad" step="0.01" min="0.01" class="form-control" required>
                                @error('cantidad') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>Unidad de medida</label>
                                <input type="text" name="unidad_medida" class="form-control" placeholder="Ej: comprimidos, ml, ampollas">
                            </div>

                            <div class="form-group">
                                <label>Fecha de dispensación *</label>
                                <input type="date" name="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                @error('fecha') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="text-right">
                                <a href="{{ route('recetas.dispensaciones.index', $receta->id_receta) }}" class="btn btn-default">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Dispensación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Medicamentos -->
    <div class="modal fade" id="modalMedicamentos" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Seleccionar Medicamento</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <table id="tablaMedicamentos" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Concentración</th>
                                <th>Tipo</th>
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

    var tablaMedicamentos = null;

    // Inicializar DataTable cuando se abre el modal
    $('#modalMedicamentos').on('shown.bs.modal', function () {
        if (!tablaMedicamentos) {
            tablaMedicamentos = $('#tablaMedicamentos').DataTable({
                processing: true,
                ajax: {
                    url: "{{ route('api.medicamentos') }}",
                    dataSrc: function(json) {
                        return json;
                    }
                },
                columns: [
                    { data: 'nombre' },
                    { data: 'concentracion' },
                    { 
                        data: 'es_psicotropico',
                        render: function(data) {
                            return data == 1 ? '<span class="label label-warning">Psicotrópico</span>' : '<span class="label label-info">Común</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function(data) {
                            return `<button class="btn btn-sm btn-success seleccionar-medicamento" data-id="${data.id_medicamento}" data-nombre="${data.nombre} ${data.concentracion ? '(' + data.concentracion + ')' : ''}">Seleccionar</button>`;
                        }
                    }
                ]
            });
        }
    });

    // Seleccionar Medicamento
    $('#tablaMedicamentos').on('click', '.seleccionar-medicamento', function() {
        $('#id_medicamento').val($(this).data('id'));
        $('#medicamento_nombre').val($(this).data('nombre'));
        $('#modalMedicamentos').modal('hide');
    });

});
</script>
@endsection