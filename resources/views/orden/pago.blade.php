@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    @livewire('orden.orden-pagar', ['ordenPago' => $ordenPago], key($ordenPago->id))

@endsection


@section('js')
    <script src="{{asset('js/orden.js')}}"></script>
@endsection
