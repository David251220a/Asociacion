<div  class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h3 class="mb-0">Crear Orden de Pago</h3>
                </div>
            </div>

            @include('varios.mensaje')

            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="form-row mb-2">
                        <div class="form-group col-md-3">
                            <label for="documento">Documento</label>
                            <input wire:model.defer="documento" type="text" wire:blur="buscarPersona" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="ruc">RUC</label>
                            <input type="text" class="form-control" value="{{ $persona?->ruc }}" readonly style="color: black; font-weight:bold">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="beneficiario">Beneficiario</label>
                            <input type="text" class="form-control" value="{{ $persona?->nombre . ' ' . $persona?->apellido }}" readonly style="color: black; font-weight:bold">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="tipo_egreso_id">Tipo Egreso</label>
                            <select wire:model.defer="tipo_egreso_id" class="form-control">
                                @foreach ($tipo_egresos as $item)
                                    <option value="{{$item->id}}">{{$item->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="concepto">Concepto</label>
                            <input type="text" class="form-control" wire:model.defer="concepto">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="observacion">Observacion</label>
                            <input type="text" class="form-control" wire:model.defer="observacion">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="total">Total</label>
                            <input type="text" class="form-control text-right" wire:model.defer="total" onkeyup="punto_decimal(this)">
                        </div>
                    </div>

                    <div class="form-row mt-3">
                        <button type="button" wire:click="guardarPendiente(0)" class="btn btn-warning mr-2">
                            Guardar Pendiente
                        </button>

                        <button type="button" wire:click="guardarPendiente(1)" class="btn btn-success">
                            Guardar y Registrar Pago
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
