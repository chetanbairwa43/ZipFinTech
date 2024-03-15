@extends('layouts.app')

@section('content')
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
            <div class="row w-100 mx-0">
                <div class="col-lg-4 mx-auto">
                    <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                        @php
                            $data = App\Models\Setting::pluck('value','key');
                        @endphp
                        <div class="brand-logo">
                            <img src="{{ url(config('app.logo')).'/'.$data['logo_1'] }}" alt="logo" style="width: 73px !important;">
                            
                          <!-- <h3><u> ZipFinTech </u> </h3> -->
                        </div>
                        <!-- <h6 class="fw-light">Sign in to continue.</h6> -->
                        <form class="pt-3" id="basic-form" method="POST" action="{{ route('delete.user') }}">
                            @csrf
                            
                            <div class="form-group">
                                <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" placeholder="Email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" placeholder="Password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary btn-lg font-weight-medium auth-form-btn">
                                    {{ __('DELETE USER') }}
                                </button>
                            </div>
                            {{-- <div class="my-2 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    
                                    <label class="form-check-label text-muted" for="remember">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        {{ __('Keep me signed in') }}
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a class="auth-link text-black" href="{{ route('password.request') }}">
                                        {{ __('Forgot Password?') }}
                                    </a>
                                @endif
                            </div> --}}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
