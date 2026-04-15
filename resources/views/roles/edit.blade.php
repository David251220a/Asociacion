@extends('layouts.admin')

@section('styles')
@endsection

@section('content')

    <div class="layout-px-spacing">

        <h2 class="mt-3">Editar Rol: {{ $data->name }}</h2>
        <div class="widget-content widget-content-area">
            <form action="{{ route('role.update', $data) }}" method="POST" onsubmit="return checkSubmit();">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-4">
                        <label for="">Rol</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ $data->name }}">
                    </div>
                </div>
                <h4 class="">Permisos</h4>
                <div class="row">
                    @foreach ($permissions as $item)
                        <div class="col-lg-3 col-md-3 col-sm-12 mb-4">
                            <label>
                                <input type="checkbox" name="permissions[]" id="permissions" class=""
                                value="{{ $item['name'] }}" {{ $data->hasPermissionTo($item['name']) ? 'checked' : null }}>
                                {{$item->descripcion}}
                            </label>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-success">Editar</button>
            </form>
        </div>
    </div>

@endsection

@section('js')
@endsection
