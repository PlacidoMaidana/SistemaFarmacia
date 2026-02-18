@extends('voyager::master')

@section('page_title', 'Dispensaciones - Receta #' . $receta->id_receta)

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-list"></i> Dispensaciones de la Receta #{{ $receta->id_receta }}
        </h1>
        <div class="breadcrumb">
            <a href="{{ route('voyager.dashboard') }}">Dashboard</a>
            <span class="divider">/</span>
            <a href="{{ route('voyager.recetas.index') }}">Recetas</a>
            <span class="divider">/</span>
            <span class="active">Dispensaciones</span>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            Dispensaciones de la Receta #{{ $receta->id_receta }}
                            <small>
                                Interno: {{ $receta->interno->nombre_y_apellido ?? 'N/A' }}  
                                - Médico: {{ $receta->medico->nombre_y_apellido ?? 'N/A' }}
                            </small>
                        </h3>
                        <a href="{{ route('recetas.dispensaciones.create', $receta->id_receta) }}" 
                           class="btn btn-success btn-sm pull-right">
                            <i class="voyager-plus"></i> Nueva Dispensación
                        </a>
                    </div>

                    <div class="panel-body">
                        @if($receta->dispensaciones->isEmpty())
                            <p class="text-center text-muted">Aún no hay dispensaciones registradas.</p>
                        @else
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <th>Fecha / Hora</th>
                                        <th>Usuario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receta->dispensaciones as $disp)
                                        <tr>
                                            <td>{{ $disp->medicamento->nombre ?? '—' }}</td>
                                            <td>{{ $disp->cantidad }}</td>
                                            <td>{{ $disp->unidad_medida ?? '—' }}</td>
                                            <td>{{ $disp->fecha }} {{ $disp->hora }}</td>
                                            <td>{{ $disp->usuario->name ?? '—' }}</td>
                                            <td>
                                                <form action="{{ route('recetas.dispensaciones.destroy', $disp->id_dispensacion) }}" 
                                                      method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('¿Eliminar esta dispensación?')">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection