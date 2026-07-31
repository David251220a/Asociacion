@php
    $tieneOrigen = (int) $item->origen_id > 0;
    $ordenPagada = (int) $item->estado_pago === 1;
@endphp

<div class="modal fade" id="anular_dos_{{ $item->id }}" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="titulo_anular_dos_{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <form
                action="{{ route('orden.anular', $item) }}"
                method="POST"
                class="form-anular-orden"
                onsubmit="
                    if (!this.checkValidity()) {
                        return false;
                    }

                    if (this.dataset.enviando === '1') {
                        return false;
                    }
                    this.dataset.enviando = '1';
                    document.getElementById('btn_enviar_{{ $item->id }}').disabled = true;
                    document.getElementById('btn_enviar_{{ $item->id }}').innerText = 'Anulando...';
                "
            >
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="titulo_anular_dos_{{ $item->id }}">
                            Anular Orden de Pago
                        </h5>
                        <small class="text-muted">
                            Orden N.º
                            {{ str_pad($item->numero, 7, '0', STR_PAD_LEFT) }}/{{ $item->anio }}
                        </small>
                    </div>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    @if ($ordenPagada)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Esta orden ya fue pagada.</strong>
                            Al confirmar la anulación, también se anularán
                            los pagos registrados y se revertirá el movimiento
                            correspondiente en tesorería.
                        </div>
                    @endif

                    @if ($tieneOrigen)

                        <p class="font-weight-bold mb-3">
                            Seleccione qué desea hacer con la solicitud vinculada:
                        </p>

                        <div class="form-group">

                            <label class="opcion-anulacion" for="anulacion_completa_{{ $item->id }}">
                                <input type="radio" id="anulacion_completa_{{ $item->id }}" name="tipo_anulacion" value="completa" required>

                                <span>
                                    <strong>
                                        Anular completamente la solicitud
                                    </strong>

                                    <small>
                                        Se anulará la orden de pago y la solicitud
                                        de ayuda social. No se generará una nueva
                                        orden.
                                    </small>
                                </span>
                            </label>

                            <label class="opcion-anulacion" for="anulacion_reemitir_{{ $item->id }}">
                                <input type="radio" id="anulacion_reemitir_{{ $item->id }}" name="tipo_anulacion" value="reemitir" required>
                                <span>
                                    <strong>
                                        Anular y generar una nueva orden
                                    </strong>

                                    <small>
                                        La solicitud permanecerá aprobada y se
                                        generará una nueva orden pendiente de pago.
                                    </small>
                                </span>
                            </label>

                        </div>

                    @else

                        <input type="hidden" name="tipo_anulacion" value="solo_orden">
                        <div class="alert alert-light border">
                            Esta orden no se encuentra vinculada a ninguna
                            solicitud. Solamente se anularán la orden y sus
                            pagos correspondientes.
                        </div>

                    @endif

                    <div class="form-group mb-0">
                        <label for="motivo_anulacion_{{ $item->id }}">
                            Motivo de la anulación
                            <span class="text-danger">*</span>
                        </label>

                        <textarea
                            id="motivo_anulacion_{{ $item->id }}"
                            name="motivo_anulacion"
                            class="form-control"
                            rows="4"
                            maxlength="1000"
                            placeholder="Describa el motivo de la anulación"
                            required
                        ></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-danger btn-sm btn-confirmar-anulacion" id="btn_enviar_{{$item->id}}">
                        <i class="fas fa-ban mr-1"></i>
                        Confirmar anulación
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
