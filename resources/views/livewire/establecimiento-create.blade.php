<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="form-row mb-2">
            <div class="form-group col-md-4">
                <label for="departamento_id">Departamento</label>
                <select name="departamento_id" id="departamento_id" wire:model="departamento_id" class="form-control">
                    @foreach ($departamento as $item)
                        <option value="{{$item->id}}">{{$item->descripcion}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4">
                <label for="distrito_id">Distrito</label>
                <select name="distrito_id" id="distrito_id" wire:model="distrito_id" class="form-control">
                    @foreach ($distrito as $item)
                        <option value="{{$item->id}}">{{$item->descripcion}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4">
                <label for="ciudad_id">Ciudad</label>
                <select name="ciudad_id" id="ciudad_id" wire:model="ciudad_id" class="form-control">
                    @foreach ($ciudad as $item)
                        <option value="{{$item->id}}">{{$item->descripcion}}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>
</div>