@if ((int) $item->estado_planilla === 1  && !$item->orden_pago_id)
    <div class="modal fade" id="anular_planilla_{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document" >
            <div class="modal-content">
                <form method="POST" action="{{ route('miembros.planillas.anular', $item->id ) }}"
                    onsubmit="
                    if (this.dataset.enviando === '1') return false;
                    this.dataset.enviando = '1';
                    document.getElementById('btnEnviar').disabled = true;
                    document.getElementById('btnEnviar').innerText = 'Enviando...';"
                >
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Anular planilla
                        </h5>

                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Cerrar"
                        >
                            <span aria-hidden="true">
                                &times;
                            </span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Está por anular la planilla N.º

                            <strong>
                                {{ str_pad(
                                    $item->numero,
                                    5,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}
                                /
                                {{ $item->anio }}
                            </strong>.

                            Esta operación permitirá regenerar posteriormente
                            la planilla del mismo periodo.
                        </div>

                        <div class="form-group">
                            <label
                                for="motivo_anulacion_{{ $item->id }}"
                            >
                                Motivo de la anulación
                            </label>

                            <textarea
                                name="motivo_anulacion"
                                id="motivo_anulacion_{{ $item->id }}"
                                class="form-control"
                                rows="4"
                                maxlength="500"
                                required
                                placeholder="Ingrese el motivo de la anulación"
                            ></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal"
                        >
                            Cancelar
                        </button>

                        <button
                            id="btnEnviar"
                            type="submit"
                            class="btn btn-danger"
                        >
                            <i class="fa fa-ban"></i>
                            Confirmar anulación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
