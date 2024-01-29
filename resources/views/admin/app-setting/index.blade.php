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
                        App Setting
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.app-setting.store') }}" method="POST" enctype="multipart/form-data" id="basic-form">
                            @csrf
                            <h5 class="fw-bolder">Customer Support Details</h5>
                            <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">Mobile Number <span class="text-danger">*</span></label>
                                        <input type="number" name="mobile_number" class="form-control mobile_number valid" placeholder="Mobile Number" value="{{ old('mobile_number', isset($data) && isset($data['mobile_number']) ? $data['mobile_number'] : '' ) }}" min="0" minlength="10" maxlength="10" required="" aria-invalid="false">
                                                                            </div>

                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">Landline Number </label>
                                        <input type="number" name="landline_number" class="form-control landline_number valid" placeholder="Landline Number" value="{{ old('landline_number', isset($data) && isset($data['landline_number']) ? $data['landline_number'] : '' ) }}" min="0" aria-invalid="false">
                                                                            </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">Support Email <span class="text-danger">*</span></label>
                                        <input type="email" name="support_email" class="form-control support_email " placeholder="Support Email" value="{{ old('support_email', isset($data) && isset($data['support_email']) ? $data['support_email'] : '' ) }}" required="">
                                                                            </div>

                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">Whatsapp Number </label>
                                        <input type="number" name="whatsapp_number" class="form-control whatsapp_number " placeholder="WhatsApp Number" value="{{ old('whatsapp_number', isset($data) && isset($data['whatsapp_number']) ? $data['whatsapp_number'] : '' ) }}" min="0" minlength="10" maxlength="10">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">Payout Fee </label>
                                        <input type="number" name="payout_fee" class="form-control payout_fee " placeholder="Payout Fee" value="{{ old('payout_fee', isset($data) && isset($data['payout_fee']) ? $data['payout_fee'] : '' ) }}" min="0">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">CashOut Fee </label>
                                        <input type="number" name="cashout_fee" class="form-control cashout_fee " placeholder="CashOut Fee" value="{{ old('cashout_fee', isset($data) && isset($data['cashout_fee']) ? $data['cashout_fee'] : '' ) }}" min="0">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">CashIn Fee </label>
                                        <input type="number" name="cashin_fee" class="form-control cashin_fee " placeholder="CashIn Fee" value="{{ old('cashin_fee', isset($data) && isset($data['cashin_fee']) ? $data['cashin_fee'] : '' ) }}" min="0">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">Services Fee </label>
                                        <input type="number" name="service_fee" class="form-control service_fee " placeholder="Services Fee" value="{{ old('service_fee', isset($data) && isset($data['service_fee']) ? $data['service_fee'] : '' ) }}" min="0">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">BridgeCard Fee </label>
                                        <input type="number" name="bridgeCard_fee" class="form-control bridgeCard_fee " placeholder="BridgeCard Fee" value="{{ old('bridgeCard_fee', isset($data) && isset($data['bridgeCard_fee']) ? $data['bridgeCard_fee'] : '' ) }}" min="0">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">BridgeCard FX rate Fee </label>
                                        <input type="number" name="bridgeCard_fxrate_fee" class="form-control bridgeCard_fxrate_fee " placeholder="BridgeCard FX rate Fee" value="{{ old('bridgeCard_fxrate_fee', isset($data) && isset($data['bridgeCard_fxrate_fee']) ? $data['bridgeCard_fxrate_fee'] : '' ) }}" min="0">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="name" class="mt-2">Card Creation Fee (in $) </label>
                                        <input type="number" name="cardCreation_fee" class="form-control cardCreation_fee " placeholder="Card Creation Fee" value="{{ old('cardCreation_fee', isset($data) && isset($data['cardCreation_fee']) ? $data['cardCreation_fee'] : '' ) }}" min="0">
                                    </div>
                                </div>
                            <hr>
                            <h5 class="fw-bolder">App Settings</h5>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> App Version <span class="text-danger">*</span></label>
                                    <input type="text" name="app_version" class="form-control @error('app_version') is-invalid @enderror" placeholder="App Version" value="{{ old('app_version', isset($data) && isset($data['app_version']) ? $data['app_version'] : '' ) }}" required>
                                    @error('app_version')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="mt-2"> Maintenance Mode <span class="text-danger">*</span></label>
                                    <select name="maintenance_mode" class="form-control is_required form-select @error('maintenance_mode') is-invalid @enderror" required>
                                        <option value="false" {{ old('maintenance_mode') ? ((old('maintenance_mode') == 'false') ? 'selected' : '' ) : (isset($data) && isset($data['maintenance_mode']) ? ($data['maintenance_mode'] == 'false' ? 'selected' : '' ) : '' ) }} >False</option>
                                        <option value="true" {{ old('maintenance_mode') ? ((old('maintenance_mode') == 'true') ? 'selected' : '' ) : (isset($data) && isset($data['maintenance_mode']) ? ($data['maintenance_mode'] == 'true' ? 'selected' : '' ) : '' ) }} >True</option>
                                    </select>
                                    @error('maintenance_mode')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="mt-2"> Force Update Mode <span class="text-danger">*</span></label>
                                    <select name="force_update" class="form-control is_required form-select @error('force_update') is-invalid @enderror" required>
                                        <option value="false" {{ old('force_update') ? ((old('force_update') == 'false') ? 'selected' : '' ) : (isset($data) && isset($data['force_update']) ? ($data['force_update'] == 'false' ? 'selected' : '' ) : '' ) }} >False</option>
                                        <option value="true" {{ old('force_update') ? ((old('force_update') == 'true') ? 'selected' : '' ) : (isset($data) && isset($data['force_update']) ? ($data['force_update'] == 'true' ? 'selected' : '' ) : '' ) }} >True</option>
                                    </select>
                                    @error('force_update')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="mt-2"> Operating System <span class="text-danger">*</span></label>
                                    <select name="Operating_System" class="form-control is_required form-select @error('force_update') is-invalid @enderror" required>
                                        <option value="iOS" {{ old('force_update') ? ((old('force_update') == 'iOS') ? 'selected' : '' ) : (isset($data) && isset($data['force_update']) ? ($data['force_update'] == 'false' ? 'selected' : '' ) : '' ) }} >iOS</option>
                                        <option value="Android" {{ old('force_update') ? ((old('force_update') == 'Android') ? 'selected' : '' ) : (isset($data) && isset($data['force_update']) ? ($data['force_update'] == 'true' ? 'selected' : '' ) : '' ) }} >Android</option>
                                    </select>
                                    @error('force_update')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <hr>
                            @php
                            function maskApiKey($apiKey) {
                                $apiKeyLength = strlen($apiKey);
                                $maskedPart = str_repeat('*', $apiKeyLength - 5);
                                $lastFiveChars = substr($apiKey, -5);
                                return $maskedPart . $lastFiveChars;
                            }
                            @endphp
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="name" class="mt-2">BusinessID</label>
                                     <input type="text" name="business_id" class="form-control business_id" id="fullInput" id="lastFourDigits" placeholder="BusinessID" value="{{ old('business_id', isset($data) && isset($data['business_id']) ? maskApiKey($data['business_id']) : '' ) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="name" class="mt-2">Public Key</label>
                                     <input type="text" name="public_key" class="form-control public_key" id="fullInput" id="lastFourDigits" placeholder="Public key" value="{{ old('public_key', isset($data) && isset($data['public_key']) ? maskApiKey($data['public_key']) : '' ) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="name" class="mt-2">Secret Key</label>
                                     <input type="text" name="secret_key" class="form-control secret_key" id="fullInput" id="lastFourDigits" placeholder="Secret key" value="{{ old('secret_key', isset($data) && isset($data['secret_key']) ? maskApiKey($data['secret_key']) : '' ) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="name" class="mt-2">Api Key</label>
                                     <input type="text" name="api_Key" class="form-control api_Key" id="fullInput" id="lastFourDigits" placeholder="Api Key" value="{{ old('api_Key', isset($data) && isset($data['api_Key']) ? maskApiKey($data['api_Key']) : '' ) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="name" class="mt-2">Test Token</label>
                                     <input type="text" name="test_token" class="form-control" id="fullInput" id="lastFourDigits" placeholder="Test Token" value="{{ old('test_token', isset($data) && isset($data['test_token']) ? maskApiKey($data['test_token']) : '' ) }}">
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
@section('scripts')

<script>
$(document).ready(function() {
    $('#fullInput').on('input', function() {
        // Get the full input value
        var fullValue = $(this).val();

        // Extract the last four digits
        var lastFourDigits = fullValue.slice(-4);

        // Display the last four digits in the <span> element
        $('#lastFourDigits').text(lastFourDigits);
    });
});
</script>
@endsection
