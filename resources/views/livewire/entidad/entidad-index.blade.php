<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">

            @include('varios.mensaje')

            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 filtered-list-search mx-auto">
                    <div class="alert alert-icon-left alert-light-success mb-4" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12" y2="17"></line></svg>
                        Entidad
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="form-row mb-2">
                        <div class="form-group col-md-6">
                            <label for="razon">Razon Social</label>
                            <input wire:model.defer="razon" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="nombre">Nombre Fantasia</label>
                            <input wire:model.defer="nombre" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="ruc">Ruc</label>
                            <input wire:model.defer="ruc" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="ruc_sin_digito">Ruc sin digito</label>
                            <input wire:model.defer="ruc_sin_digito" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="digito">Digito Verificador</label>
                            <input wire:model.defer="digito" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="tipo_regimen">Tipo Regimen</label>
                            <select wire:model.defer="tipo_regimen" class="form-control">
                                <option value=""></option>
                                <option value="1">Régimen</option>
                                <option value="2">Importador</option>
                                <option value="3">Exportador</option>
                                <option value="4">Maquila</option>
                                <option value="5">Ley N° 60/90</option>
                                <option value="6">Régimen del Pequeño Productor</option>
                                <option value="7">Régimen del Mediano Productor</option>
                                <option value="8">Régimen Contable</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="email">Email</label>
                            <input wire:model.defer="email" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-8">
                            <label for="direccion">Direccion</label>
                            <input wire:model.defer="direccion" type="text" class="form-control">
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label for="tipo_transaccion_id">Tipo Transaccion</label>
                            <select wire:model.defer="tipo_transaccion_id" class="form-control">
                                @foreach ($transaccion as $item)
                                    <option value="{{$item->id}}">{{$item->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="departamento_id">Departamento</label>
                            <select wire:model.defer="departamento_id" class="form-control">
                                @foreach ($depar as $item)
                                    <option value="{{$item->id}}">{{$item->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="districto_id">Distrito</label>
                            <select wire:model.defer="districto_id" class="form-control">
                                @foreach ($distrito as $item)
                                    <option value="{{$item->id}}">{{$item->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="ciudad_id">Ciudad</label>
                            <select wire:model.defer="ciudad_id" class="form-control">
                                @foreach ($ciudad as $item)
                                    <option value="{{$item->id}}">{{$item->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="telefono">Telefono</label>
                            <input wire:model.defer="telefono" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="numero_casa">Numero Casa</label>
                            <input wire:model.defer="numero_casa" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="codigo_set_id">Codigo Set ID</label>
                            <input wire:model.defer="codigo_set_id" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="codigo_cliente_set">Codigo Cliente Set</label>
                            <input wire:model.defer="codigo_cliente_set" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="num_timbrado">Timbrado</label>
                            <input wire:model.defer="num_timbrado" type="text" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="fecha_timbrado">Fecha Timbrado</label>
                            <input wire:model.defer="fecha_timbrado" type="date" class="form-control">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="ambiente">Ambiente</label>
                            <select wire:model.defer="ambiente" class="form-control">
                                <option value="0">TEST</option>  
                                <option value="1">PRODUCCION</option>   
                            </select>
                        </div>

                    </div>

                    <div class="form-row">
                        <button
                            type="button"
                            wire:click="grabar"
                            :disabled="$wire.procesando"
                            class="btn btn-success"
                        >
                            <span wire:loading.remove wire:target="grabar">Grabar</span>
                            <span wire:loading wire:target="grabar">Procesando...</span>
                        </button>
                    </div>

                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h4>Firma Digital</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped table-checkable table-highlight-head mb-4">
                            <thead>
                                <tr>
                                    <th class="">ID</th>
                                    <th class="">Firma</th>
                                    <th class=""></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($entidad as $item)
                                    <tr>
                                        <td class="">
                                            {{$item->id}}
                                        </td>
                                        <td>
                                            {{$item->pass_firma}}
                                        </td>
                                        <td>
                                            @can('entidad.firma')
                                                <a class="ml-2" href="{{route('entidad.firma')}}" data-toggle="tooltip" data-placement="top" title="Editar">
                                                    <svg xmlns="#" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                                                    stroke-linejoin="round" class="feather feather-edit-3"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                                </a>
                                            @endcan
                                            
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h4>Obligaciones - 
                        @can('entidad.obligaciones')
                            <a href="{{route('entidad.obligaciones')}}" class="btn btn-info btn-sm">Agregar</a>
                        @endcan
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped table-checkable table-highlight-head mb-4">
                            <thead>
                                <tr>
                                    <th class="">Codigo</th>
                                    <th class="">Descripcion</th>
                                    <th class=""></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($obligaciones as $item)
                                    <tr>
                                        <td class="">
                                            {{$item->codigo}}
                                        </td>
                                        <td>
                                            {{$item->descripcion}}
                                        </td>
                                        <td>
                                            @can('entidad.obligacion_editar')
                                                <a class="ml-2" href="{{route('entidad.obligacion_editar', $item)}}" data-toggle="tooltip" data-placement="top" title="Editar">
                                                    <svg xmlns="#" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                                                    stroke-linejoin="round" class="feather feather-edit-3"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                                </a>
                                            @endcan
                                            
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
