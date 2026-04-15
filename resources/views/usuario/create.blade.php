@extends('layouts.admin')

@section('styles')  
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div class="layout-px-spacing">

        <h2 class="mt-3">Crear Usuario</h2>
        @include('varios.mensaje')
        <div class="widget-content widget-content-area">
            <form action="{{route('user.store')}}" method="post" onsubmit="
                    if (this.dataset.enviando === '1') return false;
                    this.dataset.enviando = '1';
                    document.getElementById('btnEnviar').disabled = true;
                    document.getElementById('btnEnviar').innerText = 'Enviando...';"
            >
                @csrf
                <div class="row">
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Username</label>
                        <input type="text" class="form-control" name="username" id="username" value="{{old('username')}}" required>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Documento</label>
                        <input type="text" class="form-control" name="documento" id="documento" value="{{old('documento')}}" required>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Nombre</label>
                        <input type="text" class="form-control" name="name" value="{{old('name')}}" required>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Apellido</label>
                        <input type="text" class="form-control" name="lastname" value="{{old('lastname')}}" required>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Email</label>
                        <input type="text" class="form-control" name="email" value="{{old('email')}}" required>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Celular</label>
                        <input type="text" class="form-control" name="celular" value="{{old('celular')}}" required>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Contraseña</label>
                        <input type="password" class="form-control" name="password" id="password" value="{{old('password')}}" required>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Repetir Contraseña</label>
                        <input type="password" class="form-control" name="password_rep" id="password_rep" value="{{old('password')}}" required>
                        <span id="msj" style="display: none"><p style="color: red">Las contraseñas no coinciden!!</p></span>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Grupo</label>
                        <select name="rol" id="rol" class="form-control">
                            <option value=""></option>
                            @foreach ($role as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <button id="btnEnviar" type="submit" class="btn btn-success">
                        Grabar
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');

            form.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // 🔥 evita el submit
                    return false;
                }
            });
        });
    </script>
@endsection
