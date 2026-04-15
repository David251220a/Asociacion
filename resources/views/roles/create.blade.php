@extends('layouts.admin')

@section('styles')  
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div  class="col-lg-12 layout-spacing">

        <h2 class="mt-3">Crear Usuario</h2>
        @include('varios.mensaje')
        <div class="widget-content widget-content-area">
            <form action="{{route('role.store')}}" method="post" onsubmit="
                    if (this.dataset.enviando === '1') return false;
                    this.dataset.enviando = '1';
                    document.getElementById('btnEnviar').disabled = true;
                    document.getElementById('btnEnviar').innerText = 'Enviando...';"
            >
                @csrf
                <div class="row">
                    <div class="col-lg-3 col-md-4 col-sm-12 layout-spacing">
                        <label for="">Rol</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" required>
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
