<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Contratos Web') }} - Painel</title>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- FontAwesome para Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Header / Navbar -->
        <nav class="app-header navbar navbar-expand bg-dark navbar-dark shadow-sm" data-bs-theme="dark">
            <div class="container-fluid">
                <!-- Toggle Sidebar Button -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="fa-solid fa-bars"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-inline-block">
                        <a href="{{ route('dashboard') }}" class="nav-link">Início</a>
                    </li>
                </ul>

                <!-- Right Side Elements -->
                <ul class="navbar-nav ms-auto">
                    @php
                        $navbarAlerts = \App\Models\Alert::where('user_id', auth()->id())->unread()->orderBy('created_at', 'desc')->take(5)->get();
                        $navbarAlertsCount = \App\Models\Alert::where('user_id', auth()->id())->unread()->count();
                    @endphp

                    <!-- Notifications Dropdown Menu -->
                    <li class="nav-item dropdown me-3 align-self-center">
                        <a class="nav-link position-relative py-1" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                            <i class="fa-solid fa-bell fs-5"></i>
                            @if($navbarAlertsCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25em 0.5em; margin-top: 5px; margin-left: -5px;">
                                    {{ $navbarAlertsCount }}
                                </span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">
                            <span class="dropdown-item dropdown-header text-uppercase fs-7 text-secondary py-2">
                                {{ $navbarAlertsCount }} Alertas Não Lidos
                            </span>
                            <div class="dropdown-divider mb-0"></div>
                            
                            @if($navbarAlerts->isEmpty())
                                <div class="dropdown-item text-center text-muted py-3">
                                    <i class="fa-solid fa-circle-check text-success mb-2 fs-4"></i>
                                    <p class="mb-0 fs-7">Nenhuma pendência encontrada</p>
                                </div>
                            @else
                                @foreach($navbarAlerts as $navAlert)
                                    @php
                                        $alertIcons = [
                                            'new_request' => 'fa-envelope text-primary',
                                            'request_deadline' => 'fa-clock text-warning',
                                            'obligation_deadline' => 'fa-circle-exclamation text-danger',
                                            'request_response' => 'fa-reply text-success',
                                        ];
                                        $alertIcon = $alertIcons[$navAlert->type] ?? 'fa-bell text-secondary';
                                    @endphp
                                    <a href="{{ route('alerts.go', $navAlert) }}" class="dropdown-item d-flex align-items-center py-2 border-bottom">
                                        <i class="fa-solid {{ $alertIcon }} me-3 fs-5"></i>
                                        <div style="white-space: normal;">
                                            <p class="mb-0 fw-bold fs-7" style="line-height: 1.2;">{{ $navAlert->title }}</p>
                                            <p class="mb-0 text-muted fs-8" style="line-height: 1.2;">{{ $navAlert->message }}</p>
                                        </div>
                                    </a>
                                @endforeach
                                <div class="dropdown-divider mt-0"></div>
                                <a href="{{ route('dashboard') }}" class="dropdown-item dropdown-footer text-center text-primary py-2 fw-bold fs-7">
                                    Ver todos os alertas no Dashboard
                                </a>
                            @endif
                        </div>
                    </li>
                    @if(auth()->check() && (auth()->user()->isSuperAdmin() || (auth()->user()->isGestor() && auth()->user()->companies()->count() > 1)))
                        @php
                            if (auth()->user()->isSuperAdmin()) {
                                $switchCompanies = \App\Models\Company::where('active', true)->orderBy('name', 'asc')->get();
                            } else {
                                $switchCompanies = auth()->user()->companies()->where('companies.active', true)->orderBy('name', 'asc')->get();
                            }
                            $currentCompany = auth()->user()->company;
                        @endphp
                        <li class="nav-item dropdown me-2">
                            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-arrows-spin me-2 text-warning"></i>
                                <span class="d-none d-md-inline text-truncate text-white" style="max-width: 200px;">
                                    {{ $currentCompany ? $currentCompany->name : 'Todas as Empresas' }}
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li class="dropdown-header text-uppercase fs-7 text-secondary">Alternar Empresa</li>
                                @if(auth()->user()->isSuperAdmin())
                                    <li>
                                        <form action="{{ route('companies.switch') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="company_id" value="">
                                            <button type="submit" class="dropdown-item @if(!$currentCompany) active @endif">
                                                <i class="fa-solid fa-globe me-2 text-primary"></i> Todas as Empresas
                                            </button>
                                        </form>
                                    </li>
                                @endif
                                @foreach($switchCompanies as $swCompany)
                                    <li>
                                        <form action="{{ route('companies.switch') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="company_id" value="{{ $swCompany->id }}">
                                            <button type="submit" class="dropdown-item @if($currentCompany && $currentCompany->id === $swCompany->id) active @endif">
                                                <i class="fa-solid fa-building me-2 @if($currentCompany && $currentCompany->id === $swCompany->id) text-white @else text-secondary @endif"></i> {{ $swCompany->name }}
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @elseif(auth()->check() && auth()->user()->isGestor() && auth()->user()->company)
                        <!-- Se o gestor só tiver uma empresa, apenas exibe o nome dela para identificação -->
                        <li class="nav-item d-none d-md-inline-block align-self-center px-3">
                            <span class="badge bg-secondary">
                                <i class="fa-solid fa-building me-1"></i> {{ auth()->user()->company->name }}
                            </span>
                        </li>
                    @endif

                    <!-- User Dropdown Menu -->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            @if(auth()->user()->profile_photo_path)
                                <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" class="rounded-circle me-1" alt="User Image" style="width: 24px; height: 24px; object-fit: cover;">
                            @else
                                <i class="fa-solid fa-user-circle fa-lg me-1"></i>
                            @endif
                            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">
                            <!-- User image & Name -->
                            <li class="user-header text-bg-primary p-3 text-center">
                                @if(auth()->user()->profile_photo_path)
                                    <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" class="rounded-circle shadow mb-2" alt="User Image" style="width: 90px; height: 90px; object-fit: cover;">
                                @else
                                    <i class="fa-solid fa-user fa-3x mb-2 text-white"></i>
                                @endif
                                <p>
                                    {{ auth()->user()->name }}
                                    <small class="d-block text-white-50">
                                        @if(auth()->user()->isSuperAdmin())
                                            Administrador Global
                                        @elseif(auth()->user()->isGestor())
                                            Gestor ({{ auth()->user()->company->name ?? 'Empresa' }})
                                        @else
                                            Fornecedor ({{ auth()->user()->provider->name ?? 'Fornecedor' }})
                                        @endif
                                    </small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer d-flex justify-content-between p-2">
                                <a href="{{ route('profile.index') }}" class="btn btn-default btn-flat border">Perfil</a>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-flat">Sair</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
            <div class="sidebar-brand text-center p-3 border-bottom border-secondary">
                <a href="{{ route('dashboard') }}" class="brand-link text-decoration-none text-white fs-4">
                    <i class="fa-solid fa-file-signature me-2 text-primary"></i>
                    <span class="brand-text">Contratos<strong>Web</strong></span>
                </a>
            </div>
            
            <div class="sidebar-wrapper">
                <nav class="mt-3">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                        
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-gauge-high"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        @if(auth()->user()->isSuperAdmin())
                            <div class="nav-header text-uppercase text-secondary fs-7 px-3 mt-3 mb-1">Administrador Global</div>
                            
                            <li class="nav-item">
                                <a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-building"></i>
                                    <p>Empresas Contratantes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('document-types.index') }}" class="nav-link {{ request()->routeIs('document-types.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-folder-open"></i>
                                    <p>Tipos de Documentos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-users"></i>
                                    <p>Gerenciar Usuários</p>
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->isGestor())
                            <div class="nav-header text-uppercase text-secondary fs-7 px-3 mt-3 mb-1">Gestão de Contratos</div>
                            
                            <li class="nav-item">
                                <a href="{{ route('providers.index') }}" class="nav-link {{ request()->routeIs('providers.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-handshake"></i>
                                    <p>Fornecedores</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('contracts.index') }}" class="nav-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-file-contract"></i>
                                    <p>Contratos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('ged.index') }}" class="nav-link {{ request()->routeIs('ged.index') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-check-double"></i>
                                    <p>Aprovações de GED</p>
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->isFornecedor())
                            <div class="nav-header text-uppercase text-secondary fs-7 px-3 mt-3 mb-1">Portal do Fornecedor</div>
                            
                            <li class="nav-item">
                                <a href="{{ route('contracts.index') }}" class="nav-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-folder-open"></i>
                                    <p>Meus Contratos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('ged.index') }}" class="nav-link {{ request()->routeIs('ged.index') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-upload"></i>
                                    <p>Enviar Documentos</p>
                                </a>
                            </li>
                        @endif

                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="app-main p-4">
            <!-- Content Header -->
            <div class="app-content-header mb-4">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <h3 class="mb-0">@yield('page-title', 'Painel')</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Painel</a></li>
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Body -->
            <div class="app-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts Flash Toastr -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @endif

            @if(session('error'))
                toastr.error("{{ session('error') }}");
            @endif

            @if(session('info'))
                toastr.info("{{ session('info') }}");
            @endif

            @if(session('warning'))
                toastr.warning("{{ session('warning') }}");
            @endif
        });
    </script>
    
    @stack('scripts')
</body>
</html>
