<div class="modal fade" id="editar_{{$item->id}}" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="editar_miembro" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('miembros.update')}}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editar_miembro">
                        Editar Miembro
                    </h5>
                </div>

                <div class="class modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">
                                <div class="form-group col-md-12">
                                    <label for="editar_nombre">Nombre</label>
                                    <input type="text" name="editar_nombre" class="form-control" value="{{$item->nombre}}" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="editar_apellido">Apellido</label>
                                    <input type="text" name="editar_apellido" class="form-control" value="{{$item->apellido}}" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="editar_tipo_miembro">Tipo Miembro</label>
                                    <select name="editar_tipo_miembro" id="editar_tipo_miembro" class="form-control">
                                        @foreach ($tipos as $key => $des)
                                            <option {{ $item->tipo == $key ? 'selected' : '' }} value="{{ $key }}">{{ $des }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" value="{{$item->id}}" name="miembro_id">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        Editar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
