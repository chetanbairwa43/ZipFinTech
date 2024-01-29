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
                            <img src="{{ url(config('app.logo')).'/'.$data['logo_1'] }}" alt="logo">
                        </div>

                        <h6 class="fw-light">Verify 2FA</h6>
                        
                        <form role="form" method="POST" action="/2fa">
                            {{ csrf_field() }}
                            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                            <input id="2fa" type="text" class="form-control" name="2fa" placeholder="Enter the code you received here." required autofocus>
                            @if ($errors->has('2fa'))
                            <span class="help-block">
                            <strong>{{ $errors->first('2fa') }}</strong>
                            </span>
                            @endif
                            </div>
                            <div class="form-group">
                            <button class="btn btn-primary" type="submit">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
