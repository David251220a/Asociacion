<div class="modal fade" id="crear" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="crear_miembro" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('miembros.store')}}" method="POST"
                onsubmit="
                    if (this.dataset.enviando === '1') return false;
                    this.dataset.enviando = '1';
                    document.getElementById('btnEnviar').disabled = true;
                    document.getElementById('btnEnviar').innerText = 'Enviando...';"
            >
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="movil_asignar">
                        Crear Miembro
                    </h5>
                </div>

                <div class="class modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">
                                <div class="form-group col-md-12">
                                    <label for="crear_nombre">Nombre</label>
                                    <input type="text" name="crear_nombre" class="form-control" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="crear_apellido">Apellido</label>
                                    <input type="text" name="crear_apellido" class="form-control" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="crear_tipo_miembro">Tipo Miembro</label>
                                    <select name="crear_tipo_miembro" id="crear_tipo_miembro" class="form-control">
                                        @foreach ($tipos as $key => $item)
                                            <option value="{{ $key }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" id="btnEnviar" class="btn btn-success btn-sm">
                        Crear
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
