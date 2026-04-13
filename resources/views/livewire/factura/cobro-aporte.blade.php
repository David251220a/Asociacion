<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h3 class="mb-0">Cobro de Aporte Individual</h3>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-row mb-2">
                    <div class="form-group col-md-3">
                        <label>Documento</label>
                        <input type="text" wire:model.defer="documento" wire:blur="buscar" class="form-control">
                    </div>

                    <div class="form-group col-md-3">
                        <label>RUC</label>
                        <input type="text" wire:model.defer="ruc" class="form-control">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Nombre y Apellido</label>
                        <input type="text" value="{{ $asociado?->persona?->nombre . ' ' . $asociado?->persona?->apellido }}" class="form-control" readonly>
                    </div>
                    
                </div>
            </div>

            @if(count($aportesPendientes) > 0)
                <div class="col-lg-12 mt-4">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Sel.</th>
                                    <th>Periodo</th>
                                    <th>Mes</th>
                                    <th>Año</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aportesPendientes as $item)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                value="{{ $item['id'] }}"
                                                wire:model="seleccionados">
                                        </td>
                                        <td>{{ $item['numero_periodo'] }}</td>
                                        <td>{{ $item['mes_nombre'] }}</td>
                                        <td>{{ $item['anio'] }}</td>
                                        <td class="text-right">
                                            {{ number_format($item['monto'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-2">
                        Total seleccionado: {{ number_format($totalSeleccionado, 0, ',', '.') }}
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-row mb-2">
                        <div class="col-md-12">
                            <label><strong>Formas de Cobro</strong></label>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Forma de Cobro</th>
                                    <th style="width: 30%;">Banco</th>
                                    <th style="width: 20%;">Monto</th>
                                    <th style="width: 15%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cobros as $index => $item)
                                    <tr>
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
                                    <th colspan="2" class="text-right">Total abonado</th>
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

                <div class="col-lg-12">
                    <div class="form-row mb-3">

                        <div class="form-group col-md-4 d-flex align-items-end">
                            <button 
                                type="button" 
                                wire:click="grabar" 
                                wire:loading.attr="disabled"
                                wire:target="grabar"
                                class="btn btn-success"
                            >
                                <span wire:loading.remove wire:target="grabar">Grabar</span>
                                <span wire:loading wire:target="grabar">Procesando...</span>
                            </button>
                        </div>
                    </div>
                </div>

            @endif

        </div>
    </div>
</div>