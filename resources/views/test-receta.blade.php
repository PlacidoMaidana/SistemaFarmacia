<h1>Test Relaciones Receta</h1>

<table border="1" cellpadding="10">
    <tr>
        <td><strong>ID Receta</strong></td>
        <td>{{ $receta->id_receta }}</td>
    </tr>
    <tr>
        <td><strong>ID Interno (FK)</strong></td>
        <td>{{ $receta->id_interno }}</td>
    </tr>
    <tr>
        <td><strong>Interno via accessor</strong></td>
        <td>{{ $receta->internoNombre }}</td>
    </tr>
    <tr>
        <td><strong>Interno->apellido</strong></td>
        <td>{{ $receta->interno ? $receta->interno->apellido : 'NULL' }}</td>
    </tr>
    <tr>
        <td><strong>Interno->nombre</strong></td>
        <td>{{ $receta->interno ? $receta->interno->nombre : 'NULL' }}</td>
    </tr>
    <tr>
        <td><strong>ID Medico (FK)</strong></td>
        <td>{{ $receta->id_medico }}</td>
    </tr>
    <tr>
        <td><strong>Medico via accessor</strong></td>
        <td>{{ $receta->medicoNombre }}</td>
    </tr>
    <tr>
        <td><strong>Medico->NombreYApellido</strong></td>
        <td>{{ $receta->medico ? $receta->medico->NombreYApellido : 'NULL' }}</td>
    </tr>
    <tr>
        <td><strong>ID Usuario (FK)</strong></td>
        <td>{{ $receta->id_usuario }}</td>
    </tr>
    <tr>
        <td><strong>Usuario via accessor</strong></td>
        <td>{{ $receta->usuarioNombre }}</td>
    </tr>
    <tr>
        <td><strong>Usuario->name</strong></td>
        <td>{{ $receta->usuario ? $receta->usuario->name : 'NULL' }}</td>
    </tr>
</table>

<h2>Debug Raw</h2>
<pre>{{ json_encode($receta->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
