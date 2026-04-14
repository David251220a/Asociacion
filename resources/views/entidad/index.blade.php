@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    @livewire('entidad.entidad-index')

@endsection


@section('js')
    <script src="{{asset('js/factura.js')}}"></script>
@endsection