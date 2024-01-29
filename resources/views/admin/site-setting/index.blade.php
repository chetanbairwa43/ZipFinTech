@extends('layouts.master')
@section('content')
<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        @if(Session::has('success'))
            @section('scripts')
                <script>swal("Good job!", "{{ Session::get('success') }}", "success");</script>
            @endsection
        @endif

        @if(Session::has('error'))
            @section('scripts')
                <script>swal("Oops...", "{{ Session::get('error') }}", "error");</script>
            @endsection
        @endif
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-header border-bottom">
                        Site Setting
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.site-setting.store') }}" method="POST" enctype="multipart/form-data" id="basic-form">
                            @csrf

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <label for="name" class="mt-2"> Logo 1 <span class="text-danger info">(Only jpeg, png, jpg files allowed)</span></label>
                                            <input type="file" name="logo_1" class="form-control @error('logo_1') is-invalid @enderror" accept="image/jpeg,image/png">
                                            <input type="hidden" class="form-control" name="logo_1_old" value="{{ isset($data) && isset($data['logo_1']) ? $data['logo_1'] : ''}}">
                                            @error('logo_1')
                                                <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="col-md-5 mt-auto">
                                        @if(!empty($data['logo_1']))
                                            <div class="mt-3">
                                                <span class="pip" data-title="{{ $data['logo_1'] }}">
                                                    <img src="{{ url(config('app.logo')).'/'.$data['logo_1'] ?? '' }}" alt="" width="150" height="50px">
                                                </span>
                                            </div>
                                        @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <label for="name" class="mt-2"> Logo 2 <span class="text-danger info">(Only jpeg, png, jpg files allowed)</span></label>
                                            <input type="file" name="logo_2" class="form-control @error('logo_2') is-invalid @enderror" accept="image/jpeg,image/png">
                                            <input type="hidden" class="form-control" name="logo_2_old" value="{{ isset($data) && isset($data['logo_2']) ? $data['logo_2'] : ''}}">
                                            @error('logo_2')
                                                <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="col-md-5 mt-auto">
                                        @if(!empty($data['logo_2']))
                                            <div class="mt-3">
                                                <span class="pip" data-title="{{ $data['logo_2'] }}">
                                                    <img src="{{ url(config('app.logo')).'/'.$data['logo_2'] ?? '' }}" alt="" width="150" height="50px">
                                                </span>
                                            </div>
                                        @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2">Admin Email <span class="text-danger">*</span></label>
                                    <input type="email" name="admin_mail" class="form-control @error('admin_mail') is-invalid @enderror" placeholder="Admin Email" value="{{ old('admin_mail', isset($data) && isset($data['admin_mail']) ? $data['admin_mail'] : '') }}" required>
                                    @error('admin_mail')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Referal Amount <span class="text-danger">*</span></label>
                                    <input type="text" name="referal_amount" class="form-control @error('referal_amount') is-invalid @enderror" placeholder="Referal Amount" value="{{ old('referal_amount', isset($data) && isset($data['referal_amount']) ? $data['referal_amount'] : '' ) }}" required>
                                    @error('referal_amount')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Surge Charge <span class="text-danger">*</span></label>
                                    <input type="text" name="surcharge" class="form-control @error('surcharge') is-invalid @enderror" placeholder="Surge Charge" value="{{ old('surcharge', isset($data) && isset($data['surcharge']) ? $data['surcharge'] : '' ) }}" required>
                                    @error('surcharge')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Packing Charge <span class="text-danger">*</span></label>
                                    <input type="text" name="packing_charge" class="form-control @error('packing_charge') is-invalid @enderror" placeholder="Packing Charge" value="{{ old('packing_charge', isset($data) && isset($data['packing_charge']) ? $data['packing_charge'] : '' ) }}" required>
                                    @error('packing_charge')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Delivery charge for 1km <span class="text-danger">*</span></label>
                                    <input type="text" name="delivery_charge_1km" class="form-control @error('delivery_charge_1km') is-invalid @enderror" placeholder="Delivery charge for 1km" value="{{ old('delivery_charge_1km', isset($data) && isset($data['delivery_charge_1km']) ? $data['delivery_charge_1km'] : '' ) }}" required>
                                    @error('delivery_charge_1km')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Delivery charge for 2km <span class="text-danger">*</span></label>
                                    <input type="text" name="delivery_charge_2km" class="form-control @error('delivery_charge_2km') is-invalid @enderror" placeholder="Delivery charge for 2km" value="{{ old('delivery_charge_2km', isset($data) && isset($data['delivery_charge_2km']) ? $data['delivery_charge_2km'] : '' ) }}" required>
                                    @error('delivery_charge_2km')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Delivery charge for 3km <span class="text-danger">*</span></label>
                                    <input type="text" name="delivery_charge_3km" class="form-control @error('delivery_charge_3km') is-invalid @enderror" placeholder="Delivery charge for 3km" value="{{ old('delivery_charge_3km', isset($data) && isset($data['delivery_charge_3km']) ? $data['delivery_charge_3km'] : '' ) }}" required>
                                    @error('delivery_charge_3km')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Delivery charge for 4km <span class="text-danger">*</span></label>
                                    <input type="text" name="delivery_charge_4km" class="form-control @error('delivery_charge_4km') is-invalid @enderror" placeholder="Delivery charge for 4km" value="{{ old('delivery_charge_4km', isset($data) && isset($data['delivery_charge_4km']) ? $data['delivery_charge_4km'] : '' ) }}" required>
                                    @error('delivery_charge_4km')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Delivery charge for 5km <span class="text-danger">*</span></label>
                                    <input type="text" name="delivery_charge_5km" class="form-control @error('delivery_charge_5km') is-invalid @enderror" placeholder="Delivery charge for 5km" value="{{ old('delivery_charge_5km', isset($data) && isset($data['delivery_charge_5km']) ? $data['delivery_charge_5km'] : '' ) }}" required>
                                    @error('delivery_charge_5km')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Delivery charge after 5km (per km charge) <span class="text-danger">*</span></label>
                                    <input type="text" name="delivery_charge_per_km" class="form-control @error('delivery_charge_per_km') is-invalid @enderror" placeholder="Delivery charge after 5km (per km charge)" value="{{ old('delivery_charge_per_km', isset($data) && isset($data['delivery_charge_per_km']) ? $data['delivery_charge_per_km'] : '' ) }}" required>
                                    @error('delivery_charge_per_km')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2">Min Order Amount for Free Delivery <span class="text-danger">*</span></label>
                                    <input type="number" name="min_order_value" class="form-control @error('min_order_value') is-invalid @enderror" placeholder="Minimum Order Amount for Free Delivery" value="{{ old('min_order_value', isset($data) && isset($data['min_order_value']) ? $data['min_order_value'] : '' ) }}" required min=0 >
                                    @error('min_order_value')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 mt-auto">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <label class="form-check-label text-muted">
                                                    <input class="form-check-input" type="checkbox" name="tip_is_tax_free" {{ old('tip_is_tax_free') ? 'checked' : (isset($data) && isset($data['tip_is_tax_free']) ? ($data['tip_is_tax_free'] ? 'checked' : '' ) : '' ) }} value="1">
                                                    {{ __('Tax Free Tip') }}
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <label class="form-check-label text-muted">
                                                    <input class="form-check-input" type="checkbox" name="cod" {{ old('cod') ? 'checked' : (isset($data) && isset($data['cod']) ? ($data['cod'] ? 'checked' : '' ) : '' ) }} value="1">
                                                    {{ __('Cash On Delivery (COD)') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <input class="btn btn-primary" type="submit" value="Save">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection