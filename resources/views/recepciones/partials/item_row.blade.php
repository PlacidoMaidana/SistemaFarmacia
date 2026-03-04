<tr data-item-id="{{ $item->id_item }}">
    <td>
        @if($item->tipo_item === 'MEDICAMENTO')
            <span class="label label-info">Medicamento</span>
        @else
            <span class="label label-warning">Material</span>
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
    </td>
    <td>
        <strong>{{ number_format($item->cantidad, 2) }}</strong>
        @if($item->item)
            <br><small class="text-muted">{{ $item->item->unidad_medida ?? 'unidades' }}</small>
        @endif
    </td>
    <td>
        {{ $item->nro_lote ?: '-' }}
    </td>
    <td>
        @if($item->fecha_vencimiento)
            {{ $item->fecha_vencimiento->format('d/m/Y') }}
            @if($item->fecha_vencimiento->lt(now()->addDays(30)))
                <br><small class="text-warning">
                    <i class="voyager-warning"></i> Próximo a vencer
                </small>
            @endif
        @else
            -
        @endif
    </td>
    <td>
        @if($item->recepcion->puedeSerEditada())
            <button type="button" class="btn btn-danger btn-xs btn-eliminar-item" 
                    data-url="{{ route('recepciones.items.destroy', [$item->recepcion->id_recepcion, $item->id_item]) }}"
                    title="Eliminar item">
                <i class="voyager-trash"></i>
            </button>
        @else
            <span class="text-muted">No editable</span>
        @endif
    </td>
</tr>