<div  class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h3 class="mb-0">Orden de Pago: {{ str_pad($data->numero, 7, '0', STR_PAD_LEFT) }}/{{ $data->anio }}</h3>
                </div>
            </div>

            @include('varios.mensaje')

            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="form-row mb-2">
                        <div class="form-group col-md-3">
                            <label for="documento">Documento</label>
                            <input type="text" wire:blur="buscarPersona" class="form-control" value="{{$data->persona->documento}}" readonly style="color: black; font-weight:bold">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="ruc">RUC</label>
                            <input type="text" class="form-control" value="{{ $data->persona->ruc }}" readonly style="color: black; font-weight:bold">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="beneficiario">Beneficiario</label>
                            <input type="text" class="form-control" value="{{ $data->persona->nombre . ' ' . $data->persona->apellido }}" readonly style="color: black; font-weight:bold">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="tipo_egreso_id">Tipo Egreso</label>
                            <input type="text" class="form-control" value="{{ $data->tipo_egreso->descripcion }}" readonly style="color: black; font-weight:bold">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="concepto">Concepto</label>
                            <input type="text" class="form-control" value="{{$data->concepto}}" readonly style="color: black; font-weight:bold">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="observacion">Observacion</label>
                            <input type="text" class="form-control" value="{{$data->descripcion}}" readonly style="color: black; font-weight:bold">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="total">Total</label>
                            <input type="text" class="form-control text-right" value="{{number_format($data->total, 0, ',', '.')}}" readonly style="color: black; font-weight:bold">
                        </div>
                    </div>

                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="form-row mb-2">
                        <div class="col-md-12">
                            <label><strong>Forma de Pago</strong></label>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Fecha</th>
                                    <th style="width: 25%;">Forma de Pago</th>
                                    <th style="width: 20%;">Banco</th>
                                    <th style="width: 15%;">N° Comprobante</th>
                                    <th style="width: 15%;">Monto</th>
                                    <th style="width: 10%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cobros as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="date"
                                                wire:model="cobros.{{ $index }}.fecha_pago"
                                                class="form-control">

                                            @error("cobros.$index.fecha_pago")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </td>
                                        <td>
                                            <select wire:change="cambioFormaCobro($event.target.value, {{ $index }})" class="form-control">
                                                <option value="">Seleccionar</option>
                                                @foreach($formasCobro as $forma)
                                                    <option value="{{ $forma->id }}" @selected($cobros[$index]['forma_cobro_id'] == $forma->id) >
                                                        {{ $forma->descripcion }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("cobros.$index.forma_cobro_id")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </td>

                                        <td>
                                            @if(!empty($cobros[$index]['banco_ver']))
                                                <select wire:model="cobros.{{ $index }}.banco_id" class="form-control">
                                                    <option value="">Seleccionar</option>
                                                    @foreach($bancos as $banco)
                                                        <option value="{{ $banco->id }}">{{ $banco->descripcion }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" class="form-control" value="No requiere banco" readonly>
                                            @endif
                                        </td>

                                        <td>
                                            <input type="text"
                                                wire:model.defer="cobros.{{ $index }}.numero_comprobante"
                                                class="form-control"
                                                placeholder="N° comprobante">

                                            @error("cobros.$index.numero_comprobante")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </td>

                                        <td>
                                            <input type="text"
                                                wire:model.lazy="cobros.{{ $index }}.monto"
                                                id="cobros.{{ $index }}.monto"
                                                wire:change="recalcularTotal"
                                                class="form-control text-right"
                                                onkeyup="punto_decimal(this)">
                                            @error("cobros.$index.monto")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </td>

                                        <td class="text-center">
                                            <button type="button"
                                                    wire:click="quitarCobro({{ $index }})"
                                                    class="btn btn-danger btn-sm"
                                                    @if(count($cobros) == 1) disabled @endif>
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total abonado</th>
                                    <th>
                                        <input type="text"
                                            class="form-control text-right"
                                            value="{{ number_format($total_abonado, 0, ',', '.') }}"
                                            readonly>
                                    </th>
                                    <th>
                                        <button type="button" wire:click="agregarCobro" class="btn btn-primary btn-sm">
                                            Agregar
                                        </button>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @error('total_abonado')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="form-row mt-3">
                        <button type="button" wire:click="pagar" class="btn btn-success mr-2">
                            Pagar
                        </button>

                        <a href="{{route('orden.index')}}" class="btn btn-danger">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
