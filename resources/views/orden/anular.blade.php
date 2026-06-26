<div class="modal fade" id="anular_{{$item->id}}" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="anular_orden" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('orden.anular', $item)}}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="anular_orden">
                        Anular Orden de Pago
                    </h5>
                </div>

                <div class="class modal-body">
                    <p>Esta seguro de anular esta Orden de Pago: {{ str_pad($item->numero, 7, '0', STR_PAD_LEFT) }}/{{ $item->anio }}</p>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">
                                <div class="form-group col-md-12">
                                    <label for="motivo_anulacion">Motivo anulacion</label>
                                    <input type="text" name="motivo_anulacion" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        Anular
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
