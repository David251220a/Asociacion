@php
    $asociacionActivo = request()->routeIs('solicitud.*');
    $ayudaSocialActiva = request()->routeIs(['solicitud.index_ayuda_social','solicitud.show_ayuda_social',]);
    $solicitud_aprobacionActiva = request()->routeIs(['solicitud.index','solicitud.show',]);
    $personaActiva = request()->routeIs(['persona.*']);
    $asociadoActiva = request()->routeIs(['asociado.*', 'ficha_medica.create', 'familiar.*']);
    $planillaActiva = request()->routeIs(['planilla.*']);
    $facturaActiva = request()->routeIs(['factura.*']);
    $reciboActiva = request()->routeIs(['recibo.index']);
    $ordenActiva = request()->routeIs(['orden.*']);
    $pagoindividualActiva = request()->routeIs(['recibo.aporte']);
    $pagoDonacionlActiva = request()->routeIs(['recibo.varios']);
    $entidadActiva = request()->routeIs(['entidad.*']);
    $resumenActiva = request()->routeIs(['resumen.index']);
    $resumenCalcularActiva = request()->routeIs(['resumen.recalcular']);
    $establecimientoActiva = request()->routeIs(['establecimiento.*']);
    $miembroActiva = request()->routeIs(['miembros.*']);
    $userActiva =request()->routeIs('user.*') && !request()->routeIs('user.cambiar_contrase');
    $rolActiva = request()->routeIs(['role.*']);
    $contrasActiva = request()->routeIs(['user.cambiar_contrase']);
    $consultas = request()->routeIs(['factura.index', 'recibo.index', 'recibo.show', 'resumen.index']);
    $cobros = request()->routeIs(['recibo.aporte', 'recibo.varios']);
    $parametro_general = request()->routeIs(['entidad.*', 'miembros.*', 'role.*', 'user.*', 'establecimiento.*', 'entidad_soli.*']) && !request()->routeIs('user.cambiar_contrase');
    $soliActivarActivar = request()->routeIs(['entidad_soli.*']);
@endphp

<nav id="sidebar">
    <div class="shadow-bottom"></div>
    <ul class="list-unstyled menu-categories" id="accordionExample">

        <li class="menu">
            <a href="{{route('home')}}" aria-expanded="false" class="dropdown-toggle" @if(Str::startsWith(Route::currentRouteName(), 'home')) data-active="true" @endif>
                <div class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span>Home</span>
                </div>
            </a>
        </li>

        @if(Auth::user()->persona?->asociado)
            <li class="menu">
                <a href="{{ route('solicitudes') }}" aria-expanded="false"
                class="dropdown-toggle"
                @if(Str::startsWith(Route::currentRouteName(), 'solicitudes')) data-active="true" @endif>
                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-file-text"
                        >
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span>Solicitudes</span>
                    </div>
                </a>
            </li>
        @endif

        @if(Auth::user()->persona?->asociado)
            <li class="menu">
                <a href="{{ route('aporte') }}" aria-expanded="false"
                class="dropdown-toggle"
                @if(Str::startsWith(Route::currentRouteName(), 'aporte')) data-active="true" @endif>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-dollar-sign">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <span>Aporte</span>
                    </div>
                </a>
            </li>
        @endif

        @can('persona.index')
            <li class="menu">
                <a href="{{route('persona.index')}}" aria-expanded="false" class="dropdown-toggle" @if($personaActiva) data-active="true" @endif>
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-user">
                            <path d="M20 21a8 8 0 0 0-16 0"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Personas</span>
                    </div>
                </a>
            </li>
        @endcan

        @can('asociado.index')
            <li class="menu">
                <a href="{{route('asociado.index')}}" aria-expanded="false" class="dropdown-toggle" @if($asociadoActiva) data-active="true" @endif>
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Asociados</span>
                    </div>
                </a>
            </li>
        @endcan

        @can('solicitudes')
            <li class="menu">
                <a href="#anulacion" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-file-text"
                        >
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span>Solicitudes</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ $asociacionActivo ? 'show' : '' }}" id="anulacion" data-parent="#accordionExample">
                    @can('solicitud.index')
                        <li class="{{ $solicitud_aprobacionActiva ? 'active' : '' }}">
                            <a href="{{route('solicitud.index')}}" >
                                <span>Asociación</span>
                            </a>
                        </li>
                    @endcan
                    @can('solicitud.index_ayuda_social')
                        <li class="{{ $ayudaSocialActiva ? 'active' : '' }}">
                            <a href="{{route('solicitud.index_ayuda_social')}}" >
                                <span>Ayuda Social</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can('planilla.index')
            <li class="menu">
                <a href="{{route('planilla.index')}}" aria-expanded="false" class="dropdown-toggle" @if($planillaActiva) data-active="true" @endif>
                    <div class="">
                        <svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-clipboard"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                        <span>Planilla</span>
                    </div>
                </a>
            </li>
        @endcan

        @can('cobros')
            <li class="menu">
                <a href="#cobros" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-credit-card"
                        >
                            <rect
                                x="1"
                                y="4"
                                width="22"
                                height="16"
                                rx="2"
                                ry="2"
                            ></rect>

                            <line
                                x1="1"
                                y1="10"
                                x2="23"
                                y2="10"
                            ></line>
                        </svg>
                        <span>Cobros</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ $cobros ? 'show' : '' }}" id="cobros" data-parent="#accordionExample">
                    @can('recibo.aporte')
                        <li class="{{ $pagoindividualActiva ? 'active' : '' }}">
                            <a href="{{route('recibo.aporte')}}" >
                                <span>Aporte</span>
                            </a>
                        </li>
                    @endcan
                    @can('recibo.varios')
                        <li class="{{ $pagoDonacionlActiva ? 'active' : '' }}">
                            <a href="{{route('recibo.varios')}}" >
                                <span>Donaciones</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can('consultas')
            <li class="menu">
                <a href="#consultas" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-search"
                        >
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>Consultas</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ $consultas ? 'show' : '' }}" id="consultas" data-parent="#accordionExample">
                    @can('factura.index')
                        <li class="{{ $facturaActiva ? 'active' : '' }}">
                            <a href="{{route('factura.index')}}" >
                                <span>Factura</span>
                            </a>
                        </li>
                    @endcan
                    @can('recibo.index')
                        <li class="{{ $reciboActiva ? 'active' : '' }}">
                            <a href="{{route('recibo.index')}}" >
                                <span>Recibo</span>
                            </a>
                        </li>
                    @endcan
                    @can('resumen.index')
                        <li class="{{ $resumenActiva ? 'active' : '' }}">
                            <a href="{{route('resumen.index')}}" >
                                <span>Resumen</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can('orden.index')
            <li class="menu">
                <a href="{{route('orden.index')}}" aria-expanded="false" class="dropdown-toggle" @if($ordenActiva) data-active="true" @endif>
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-credit-card">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                        <span>Orden de Pago</span>
                    </div>
                </a>
            </li>
        @endcan

        @can('resumen.recalcular')
            <li class="menu">
                <a href="{{route('resumen.recalcular')}}" aria-expanded="false" class="dropdown-toggle" @if($resumenCalcularActiva) data-active="true" @endif>
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-bar-chart-2">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        <span>Recalcular</span>
                    </div>
                </a>
            </li>
        @endcan

        @can('parametro_general')
            <li class="menu">
                <a href="#parametro_general" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-settings"
                        >
                            <circle cx="12" cy="12" r="3"></circle>

                            <path
                                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06
                                a2 2 0 1 1-2.83 2.83l-.06-.06
                                a1.65 1.65 0 0 0-1.82-.33
                                1.65 1.65 0 0 0-1 1.51V21
                                a2 2 0 1 1-4 0v-.09
                                a1.65 1.65 0 0 0-1.08-1.51
                                1.65 1.65 0 0 0-1.82.33l-.06.06
                                a2 2 0 1 1-2.83-2.83l.06-.06
                                A1.65 1.65 0 0 0 4.6 15
                                1.65 1.65 0 0 0 3.09 14H3
                                a2 2 0 1 1 0-4h.09
                                A1.65 1.65 0 0 0 4.6 9
                                a1.65 1.65 0 0 0-.33-1.82l-.06-.06
                                a2 2 0 1 1 2.83-2.83l.06.06
                                A1.65 1.65 0 0 0 9 4.6h.08
                                A1.65 1.65 0 0 0 10 3.09V3
                                a2 2 0 1 1 4 0v.09
                                a1.65 1.65 0 0 0 1 1.51
                                1.65 1.65 0 0 0 1.82-.33l.06-.06
                                a2 2 0 1 1 2.83 2.83l-.06.06
                                a1.65 1.65 0 0 0-.33 1.82V9
                                c.12.6.6 1.08 1.2 1.2H21
                                a2 2 0 1 1 0 4h-.09
                                a1.65 1.65 0 0 0-1.51 1z"
                            ></path>
                        </svg>
                        <span>Param General</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ $parametro_general ? 'show' : '' }}" id="parametro_general" data-parent="#accordionExample">
                    @can('miembros.index')
                        <li class="{{ $miembroActiva ? 'active' : '' }}">
                            <a href="{{route('miembros.index')}}" >
                                <span>Miembros</span>
                            </a>
                        </li>
                    @endcan
                    @can('entidad.index')
                        <li class="{{ $entidadActiva ? 'active' : '' }}">
                            <a href="{{route('entidad.index')}}" >
                                <span>Entidad</span>
                            </a>
                        </li>
                    @endcan
                    @can('establecimiento.index')
                        <li class="{{ $establecimientoActiva ? 'active' : '' }}">
                            <a href="{{route('establecimiento.index')}}" >
                                <span>Establecimiento</span>
                            </a>
                        </li>
                    @endcan
                    @can('entidad_soli.solicitud')
                        <li class="{{ $soliActivarActivar ? 'active' : '' }}">
                            <a href="{{route('entidad_soli.solicitud')}}" >
                                <span>Solicitudes</span>
                            </a>
                        </li>
                    @endcan
                    @can('usuario.index')
                        <li class="{{ $userActiva ? 'active' : '' }}">
                            <a href="{{route('user.index')}}" >
                                <span>Usuario</span>
                            </a>
                        </li>
                    @endcan
                    @can('rol.index')
                        <li class="{{ $rolActiva ? 'active' : '' }}">
                            <a href="{{route('role.index')}}" >
                                <span>Roles</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        <li class="menu">
            <a href="{{route('user.cambiar_contrase')}}" aria-expanded="false" class="dropdown-toggle" @if($contrasActiva) data-active="true" @endif>
                <div class="">
                    <svg
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-unlock"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                    </svg>
                    <span>Contraseña</span>
                </div>
            </a>
        </li>

    </ul>

</nav>
