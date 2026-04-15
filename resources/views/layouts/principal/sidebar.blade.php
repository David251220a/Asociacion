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

        @can('asociado.index')
            <li class="menu">
                <a href="{{route('asociado.index')}}" aria-expanded="false" class="dropdown-toggle"
                    @if(Str::startsWith(Route::currentRouteName(), 'asociado.index')) data-active="true" @endif
                >
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Asociados</span>
                    </div>
                </a>
            </li>
        @endcan
        
        @can('planilla.index')
            <li class="menu">
                <a href="{{route('planilla.index')}}" aria-expanded="false" class="dropdown-toggle"
                    @if(Str::startsWith(Route::currentRouteName(), 'planilla.index')) data-active="true" @endif
                >
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
        
        @can('factura.index')
            <li class="menu">
                <a href="{{route('factura.index')}}" aria-expanded="false" class="dropdown-toggle"
                    @if(Str::startsWith(Route::currentRouteName(), 'factura.index')) data-active="true" @endif
                >
                    <div class="">
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                            stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8">
                            </polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span>Factura</span>
                    </div>
                </a>
            </li>
        @endcan
        
        @can('factura.aporte')
            <li class="menu">
                <a href="{{route('factura.aporte')}}" aria-expanded="false" class="dropdown-toggle"
                    @if(Str::startsWith(Route::currentRouteName(), 'factura.aporte')) data-active="true" @endif
                >
                    <div class="">
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                            stroke-linejoin="round" class="feather feather-user-plus"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                        <span>Aporte</span>
                    </div>
                </a>
            </li>
        @endcan
        
        @can('entidad.index')
            <li class="menu">
                <a href="{{route('entidad.index')}}" aria-expanded="false" class="dropdown-toggle"
                    @if(Str::startsWith(Route::currentRouteName(), 'entidad.index')) data-active="true" @endif
                >
                    <div class="">
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                            stroke-linejoin="round" class="feather feather-home"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span>Entidad</span>
                    </div>
                </a>
            </li>
        @endcan
        
        @can('establecimiento.index')
            <li class="menu">
                <a href="{{route('establecimiento.index')}}" aria-expanded="false" class="dropdown-toggle"
                    @if(Str::startsWith(Route::currentRouteName(), 'establecimiento.index')) data-active="true" @endif
                >
                    <div class="">
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                            stroke-linejoin="round" class="feather feather-map-pin"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <span>Establecimiento</span>
                    </div>
                </a>
            </li>
        @endcan
        
        @can('usuario.index')
            <li class="menu">
                <a href="{{route('user.index')}}" aria-expanded="false" class="dropdown-toggle"
                    @if(Str::startsWith(Route::currentRouteName(), 'user.index')) data-active="true" @endif
                >
                    <div class="">
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                            stroke-linejoin="round" class="feather feather-user-check"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                        <span>Usuario</span>
                    </div>
                </a>
            </li>
        @endcan
        
        @can('rol.index')
            <li class="menu">
                <a href="{{route('role.index')}}" aria-expanded="false" class="dropdown-toggle"
                    @if(Str::startsWith(Route::currentRouteName(), 'role.index')) data-active="true" @endif
                >
                    <div class="">
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                            stroke-linejoin="round" class="feather feather-unlock"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                        </svg>
                        <span>Roles</span>
                    </div>
                </a>
            </li>
        @endcan
        
    </ul>

</nav>
