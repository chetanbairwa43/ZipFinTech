@extends('layouts.app')
@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-8 mt-5">
        <div class="card-group">
            <div class="card p-4">
                <div class="card-body">
                    @if(session()->has('message'))
                        <p class="alert alert-info">
                            {{ session()->get('message') }}
                        </p>
                    @endif
                    <form method="POST" action="{{ route('verify.store') }}">
                        {{ csrf_field() }}
                        <h1>Two Factor Verification</h1>
                        <p class="text-muted">
                            You have received an email which contains two factor login code.
                            If you haven't received it, press <a href="{{ route('verify.resend') }}" class="text-success">here</a>.
                        </p>

                        <div class="input-group mb-3">
                            
                            <input name="two_factor_code" type="text" class="form-control{{ $errors->has('two_factor_code') ? ' is-invalid' : '' }}" required autofocus placeholder="Two Factor Code">
                            @if($errors->has('two_factor_code'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('two_factor_code') }}
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary px-4">
                                    Verify
                                </button>
                            </div>
                            <div class="col-6 text-right">
                                <a class="btn btn-danger px-4" href="#" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                                    Logout
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="logoutform" action="{{ route('logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>
@endsection