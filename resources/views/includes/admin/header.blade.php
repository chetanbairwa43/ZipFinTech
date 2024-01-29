<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-conteAnt-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        @php
            $data = App\Models\Setting::pluck('value','key');
        @endphp
        <div>
            <a class="navbar-brand brand-logo" href="{{ route('admin.dashboard') }}">
               {{-- <img src="{{ url(config('app.logo')).'/'.$data['logo_1'] }}" alt="logo" /> --}}
              ZipFinTech
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('admin.dashboard') }}">
                {{-- <img src="{{ url(config('app.logo')).'/'.$data['logo_2'] }}" alt="logo" /> --}}
            </a>
        </div>
    </div>

    <div class="navbar-menu-wrapper align-items-top"> 
        <div class="my-2 ml-auto d-flex justify-content-end align-items-center">
            <div class="user-name">
                <h5 class="user-name-backend mb-0 ml-2">{{ isset(auth()->user()->name) ? auth()->user()->name : 'Guest' }}</h5>
            </div>
            <div class="user-profile">
                <img class="user-profile-backend rounded-circle" src="{{ isset(auth()->user()->profile_image) ? url(config('app.profile_image')).'/'.auth()->user()->profile_image : url('admin/assets/images/users/user.jpg') }}" alt="">
                <ul>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users.show', auth()->user()->id) }}">My Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
				            <span class="menu-title">Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>