<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - Buku Tamu</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body { background-color: #f8f9fc; }
        #wrapper { display: flex; width: 100%; align-items: stretch; }
        #sidebar { min-width: 250px; max-width: 250px; background: #2c3e50; color: #fff; min-height: 100vh; transition: all 0.3s; }
        #sidebar .sidebar-header { padding: 20px; background: #1a252f; text-align: center; }
        #sidebar ul.components { padding: 20px 0; border-bottom: 1px solid #47748b; }
        #sidebar ul li a { padding: 15px 25px; font-size: 1.1em; display: block; color: #abb2b9; text-decoration: none; }
        #sidebar ul li a:hover { color: #fff; background: #34495e; }
        #sidebar ul li.active > a { color: #fff; background: #3498db; }
        #content { width: 100%; padding: 20px; }
        .card { border: none; border-radius: 10px; }
    </style>
</head>
<body>
    <div id="app">
        <div id="wrapper">
            @auth
            <nav id="sidebar">
                <div class="sidebar-header">
                    <h4>BUKU TAMU</h4>
                </div>
                <ul class="list-unstyled components">
                    <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a>
                    </li>
                    <li class="{{ request()->is('admin/kunjungan*') ? 'active' : '' }}">
                        <a href="{{ route('admin.kunjungan') }}"><i class="fas fa-book me-2"></i> Data Kunjungan</a>
                    </li>
                    <li class="{{ request()->is('admin/survey*') ? 'active' : '' }}">
                        <a href="{{ route('admin.survey') }}"><i class="fas fa-poll me-2"></i> Data Survey</a>
                    </li>
                    <li class="{{ request()->is('admin/pengunjung*') ? 'active' : '' }}">
                        <a href="{{ route('admin.pengunjung') }}"><i class="fas fa-user-friends me-2"></i> Data Pengunjung</a>
                    </li>

                    <li class="{{ request()->is('admin/master*') ? 'active' : '' }}">
                        <a href="{{ route('admin.keperluan') }}"><i class="fas fa-database me-2"></i> Master Keperluan</a>
                    </li>

                    <li class="{{ request()->is('admin/laporan*') ? 'active' : '' }}">
                        <a href="{{ route('admin.laporan') }}"><i class="fas fa-file-export me-2"></i> Laporan</a>
                    </li>
                    @can('admin-only')
                    <hr>
                    <li class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                        <a href="{{ route('admin.users') }}"><i class="fas fa-users-cog me-2"></i> Manajemen User</a>
                    </li>
                    @endcan

                    <li>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>    
            @endauth
            
            <div id="content">
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 rounded">
                    <div class="container-fluid">
                        <span class="navbar-text fw-bold text-primary">
                            @auth
                                Selamat Datang, {{ Auth::user()->name }} ({{ Auth::user()->role?->nama_role }})
                            @else
                                Buku Tamu Digital Politeknik Negeri Banjarmasin
                            @endauth
                        </span>

                        <div class="ms-auto">
                            @guest
                                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm fw-bold">
                                    <i class="fas fa-lock me-1"></i> Login Admin
                                </a>
                            @endguest
                        </div>
                    </div>
                </nav>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</body>
</html>