<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h3 class="mb-0">Cobro de Planilla</h3>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-row mb-2">
                    <div class="form-group col-md-3">
                        <label>Tipo Asociado</label>
                        <input type="text" value="{{ $planilla->tipoAsociado->descripcion }}" class="form-control" readonly>
                    </div>

                    <div class="form-group col-md-3">
                        <label>Mes</label>
                        <input type="text" wire:model.defer="mes" class="form-control" readonly>
                    </div>

                    <div class="form-group col-md-3">
                        <label>Año</label>
                        <input type="text" value="{{ $planilla->anio }}" class="form-control" readonly>
                    </div>

                    <div class="form-group col-md-3">
                        <label>Monto Generado Planilla</label>
                        <input type="text" value="{{ number_format($planilla->total, 0, ',', '.') }}" class="form-control text-right" readonly>
                    </div>

                    <div class="form-group col-md-3">
                        <label>Archivo Excel</label>
                        <input type="file" wire:model="archivo" class="form-control" accept=".xlsx,.xls" @if($verificado) disabled @endif>
                        <div wire:loading wire:target="archivo" class="text-primary mt-1">
                            Cargando archivo...
                        </div>

                        @if($archivo)
                            <small class="text-success d-block mt-1">
                                Archivo seleccionado: {{ $archivo->getClientOriginalName() }}
                            </small>
                        @endif

                        @error('archivo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-row mb-3">
                    <div class="form-group col-md-2">
                        <button type="button" wire:click="verificar" wire:key="archivo-{{ $verificado ? 'bloqueado' : 'libre' }}" class="btn btn-primary"  @if($verificado) disabled @endif>
                            <span wire:loading.remove wire:target="verificar">Verificar</span>
                            <span wire:loading wire:target="verificar">Verificando...</span>
                        </button>
                    </div>
                </div>
            </div>

            @if(count($erroresDocumentos) > 0)
                <div class="col-lg-12">
                    <div class="alert alert-danger">
                        <strong>Se encontraron documentos con problemas. No se puede grabar.</strong>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Fila</th>
                                    <th>Documento</th>
                                    <th>Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($erroresDocumentos as $error)
                                    <tr>
                                        <td>{{ $error['fila'] }}</td>
                                        <td>{{ $error['documento'] }}</td>
                                        <td>{{ $error['mensaje'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($verificado && count($erroresDocumentos) == 0)
                <div class="col-lg-12">
                    <div class="form-row mb-3">
                        <div class="form-group col-md-2">
                            <label>Cantidad Personas</label>
                            <input type="text" class="form-control text-right" value="{{ $cantidad_excel }}" readonly>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Monto Excel</label>
                            <input type="text" class="form-control text-right" value="{{ number_format($monto_excel, 0, ',', '.') }}" readonly>
                        </div>
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
                                wire:click="cancelar" 
                                class="btn btn-danger mr-2"
                            >
                                Cancelar
                            </button>

                            <button 
                                type="button" 
                                wire:click="grabar" 
                                class="btn btn-success"
                            >
                                Grabar
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>