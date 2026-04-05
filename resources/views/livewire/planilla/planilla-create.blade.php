<div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-0">Crear Planilla</h3>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="form-row mb-2">
                        <div class="form-group col-md-3">
                            <label for="tipo_asociado">Tipo Asociado</label>
                            <select wire:model.defer="tipo_asociado_id" class="form-control">
                                <option value="3"> APORTANTES </option>
                                <option value="1"> JUBILADOS/AS </option>
                            </select>
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label for="mes">Mes</label>
                            <select wire:model.defer="mes" class="form-control">
                                @foreach ($meses as $key => $nombre)
                                    <option value="{{ $key }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="anio">Año</label>
                            <div class="input-group">
                                <select wire:model.defer="anio" class="form-control">
                                    @for ($i = 0; $i <= 2; $i++)
                                        @php $valor_anio = $anio - $i; @endphp
                                        <option value="{{ $valor_anio }}">{{ $valor_anio }}</option>
                                    @endfor
                                </select>

                                <div class="input-group-append">
                                    <button type="button" wire:click="generar" class="btn btn-primary">
                                        🔍 Buscar
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <div class="alert alert-info">
                                Cantidad: {{ count($data) }} |
                                Total: {{ number_format(collect($data)->sum('saldo'), 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="form-row">
                        <button
                            type="button"
                            wire:click="save"
                            :disabled="$wire.procesando"
                            class="btn btn-success"
                        >
                            <span wire:loading.remove wire:target="save">Grabar</span>
                            <span wire:loading wire:target="save">Procesando...</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>