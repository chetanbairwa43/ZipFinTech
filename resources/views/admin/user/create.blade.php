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
                        Create User
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" id="basic-form">
                            @csrf
                            <input type="hidden" name="id" id="id" value="{{ isset($data) ? $data->id : '' }}">
                            <input type="hidden" name="virtual_account_id" id="virtual_account_id" value="{{ (isset($data) && isset($data->virtualAccounts)) ? $data->virtualAccounts->id : '' }}">
                            <input type="hidden" name="vendor_id" id="vendor_id" value="{{ (isset($data) && isset($data->vendor)) ? $data->vendor->id : '' }}">
                            <input type="hidden" name="bank_account_id" id="bank_account_id" value="{{ (isset($data) && isset($data->bank_account)) ? $data->bank_account->id : '' }}">

                            <h5 class="fw-bolder">{{ 'Basic Information' }}</h5>
                            <div class="row">

                                {{-- <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Name" value="{{ old('name', isset($data) ? $data->name : '') }}" >
                                    @error('name')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div> --}}

                                <div class="form-group col-md-6">
                                    <label for="fname" class="mt-2"> First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="fname" class="form-control @error('fname') is-invalid @enderror" placeholder="First Name" value="{{ old('fname', isset($data) ? $data->fname : '') }}" >
                                    @error('fname')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="lname" class="mt-2"> Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="lname" class="form-control @error('lname') is-invalid @enderror" placeholder="Last Name" value="{{ old('lname', isset($data) ? $data->lname : '') }}" >
                                    @error('lname')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> DOB <span class="text-danger">*</span></label>
                                    <input type="dob" name="dob" class="form-control @error('dob') is-invalid @enderror" placeholder="DOB" value="{{ old('dob', isset($data) ? $data->dob : '') }}" disabled>
                                    @error('dob')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>


                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control disabled @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email', isset($data) ? $data->email : '') }}" disabled>
                                    @error('email')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="phone" class="mt-2"> Phone <span class="text-danger">*</span></label>
                                    <input type="phone" name="phone" class="form-control disable @error('phone') is-invalid @enderror" placeholder="Phone" value="{{ old('phone', isset($data) ? $data->phone : '') }}" disabled>
                                    @error('phone')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="bvn" class="mt-2">BVN Number <span class="text-danger">*</span></label>
                                    <input type="bvn" name="bvn" id="bvnInput" class="form-control @error('bvn') is-invalid @enderror" placeholder="BVN Number" value="{{ old('bvn', isset($data) ? $data->bvn : '') }}" oninput="disableInput()" disabled>
                                    @error('bvn')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                               
                                {{-- disabled --}}
                             
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="accountType" class="mt-2">Account Type <span class="text-danger">*</span></label>
                                    <input type="text" name="accountType" id="accountType" class="form-control @error('accountType') is-invalid @enderror" placeholder="Account Type" value="{{ old('accountType', isset($virtualAccount) ? $virtualAccount['accountType'] : '') }}">
                                    @error('accountType')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="business" class="mt-2">Business <span class="text-danger">*</span></label>
                                    <input type="text" name="business" id="business" class="form-control @error('business') is-invalid @enderror" placeholder="Business" value="{{ old('business', isset($virtualAccount) ? $virtualAccount['business'] : '') }}" disabled>
                                    @error('business')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="business_id" class="mt-2">Business ID <span class="text-danger">*</span></label>
                                    <input type="text" name="business_id" id="business_id" class="form-control @error('business_id') is-invalid @enderror" placeholder="Business ID" value="{{ old('business_id', isset($virtualAccount) ? $virtualAccount['business_id'] : '') }}" disabled>
                                    @error('business_id')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="accountNumber" class="mt-2">Account Number <span class="text-danger">*</span></label>
                                    <input type="number" name="accountNumber" id="accountNumber" class="form-control @error('accountNumber') is-invalid @enderror" placeholder="Account Number" value="{{ old('accountNumber', isset($virtualAccount) ? $virtualAccount['accountNumber'] : '') }}" disabled>
                                    @error('accountNumber')
                                    <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            @if(isset($virtualAccount))
                                @php
                                    $accountInformation = json_decode($virtualAccount['accountInformation'], true);
                                @endphp
                            @endif

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="accountName" class="mt-2">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="accountName" id="accountName" class="form-control @error('accountName') is-invalid @enderror" placeholder="Account Holder Name" value="{{ old('accountName', isset($accountInformation) ? $accountInformation['accountName'] : '') }}" disabled>
                                    @error('accountName')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="bankName" class="mt-2">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bankName" id="bankName" class="form-control @error('bankName') is-invalid @enderror" placeholder="Bank Name" value="{{ old('bankName', isset($accountInformation) ? $accountInformation['bankName'] : '') }}" disabled>
                                    @error('bankName')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                           
                            {{-- <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Location</label>
                                    <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" placeholder="Location" value="{{ old('location', isset($data) ? $data->location : '') }}">
                                    @error('location')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Latitude </label>
                                    <input type="text" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" placeholder="Latitude" value="{{ old('latitude', isset($data) ? $data->latitude : '') }}" readonly>
                                    @error('latitude')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div> --}}

                            <div class="row">
                                @isset($data->created_origin)

                                <div class="form-group col-md-6">
                                    <label for="created_origin" class="mt-2"> Created Origin </label>
                                    <input type="text" name="created_origin" id="created_origin" class="form-control @error('created_origin') is-invalid @enderror"  value="{{ old('created_origin', isset($data) ? $data->created_origin : '') }}" readonly>
                                    @error('longitude')
                                    <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </div>
                               
                                @endisset
                               

                                <div class="form-group col-md-6">
                                    <label class="mt-2">Role <span class="text-danger">*</span></label>
                                    <select name="role[]" class="form-control role select2 form-select @error('role') is-invalid @enderror" multiple required>
                                        @foreach($roles as $key => $value)
                                            <option value="{{ $key }}" {{ (in_array($key, old('role', [])) || isset($data) && $data->roles->contains($key)) ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach

                                    </select>
                                    @error('role')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    @if(!empty($data->profile_image))
                                        <div class="mt-3">
                                            <span class="pip" data-title="{{$data->profile_image}}">
                                                <img src="{{ url(config('app.profile_image')).'/'.$data->profile_image ?? '' }}" alt="" width="150" height="100">
                                            </span>
                                        </div>
                                    @endif
                                    <label for="name" class="mt-2"> Profile Image <span class="text-danger info">(Only jpeg, png, jpg files allowed)</span></label>
                                    <input type="file" name="profileImage" class="form-control @error('profileImage') is-invalid @enderror" accept="image/jpeg,image/png">
                                    <input type="hidden" class="form-control" name="profileImageOld" value="{{ isset($data) ? $data->profile_image : ''}}">
                                    @error('profileImage')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                                  <div class="form-group col-md-6 {{ isset($data) ? 'mb-0 mt-auto' : '' }}">
                                        <div class="form-check">
                                           <label class="form-check-label">
                                            <input class="form-check-input" disabled type="checkbox" name="is_africa_verifed" {{ old('is_africa_verifed') ? 'checked' : (isset($data) ? ($data->is_africa_verifed ? 'checked' : '' ) : '' ) }} value="1" >
                                             {{ __('Africa Verifed') }}
                                            </label>
                                         </div>
                                  </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6 password-field @error('password') show @enderror">
                                    <label for="name" class="mt-2"> Password  <span class="text-danger">{{ isset($data) && isset($data->id) ? '' : '*' }}</span> <i class="mdi mdi-information-outline" data-toggle="tooltip" data-placement="right" title="Password must contain atleast one Lower case letter, atleast one Upper case letter, atleast one Number and atleast one Special character."></i></label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" minlength="8" {{ isset($data) ? '' : 'required' }}>
                                    @error('password')
                                    <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </div>
                            <div class="mt-3">
                                <input class="btn btn-primary" type="submit" value="{{ isset($data) && isset($data->id) ? 'Update' : 'Save' }}">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
{{-- <div class="modal fade" id="add_fund_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.add-fund', $data->id ?? '') }}" method="POST" enctype="multipart/form-data" id="add-fund-basic-form">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name" class="mt-2"> Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Enter Amount" value="{{ old('amount', isset($data) ? $data->amount : '') }}" required>
                        @error('amount')
                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div> --}}

<!-- Modal -->
{{-- <div class="modal fade" id="revoke_fund_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.revoke-fund', $data->id ?? '') }}" method="POST" enctype="multipart/form-data" id="revoke-fund-basic-form">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name" class="mt-2"> Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Enter Amount" value="{{ old('amount', isset($data) ? $data->amount : '') }}" required>
                        @error('amount')
                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Revoke</button>
                </div>
            </form>
        </div>
    </div>
</div> --}}

@endsection

@section('scripts')
<script async
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_GEOCODE_API_KEY') }}&libraries=places&callback=initMap">
</script>
<script>
function initMap() {
    window.addEventListener('load', initialize);
    function initialize() {
        var input = document.getElementById('location');
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();

            document.getElementById("latitude").value = place.geometry['location'].lat();
            document.getElementById("longitude").value = place.geometry['location'].lng();
        });

        var input_1 = document.getElementById('store_location');
        var autocomplete_1 = new google.maps.places.Autocomplete(input_1);
        autocomplete_1.addListener('place_changed', function () {
            var place_1 = autocomplete_1.getPlace();

            document.getElementById("store_latitude").value = place_1.geometry['location'].lat();
            document.getElementById("store_longitude").value = place_1.geometry['location'].lng();
        });
    }
}
function roleFunction(roles) {
    var is_password = 0;
    var is_driver = 0;
    var is_vendor = 0;
    var is_staff = 0;

    if(($.inArray('1', roles) !== -1) || ($.inArray('5', roles) !== -1)) {
        is_password = 1;
    }
    if($.inArray('3', roles) !== -1) {
        is_driver = 1;
    }
    if($.inArray('4', roles) !== -1) {
        is_vendor = 1;
    }
    if($.inArray('5', roles) !== -1) {
        var is_staff = 1;
    }
    // if(is_password) {
    //     $('.password-field').removeClass("hide");
    // }
    // else {
    //     $('.password-field').addClass("hide");
    // }
    if(is_driver) {
        $('.driverInfoSection').removeClass("hide");
        console.log('driver');
        if($('#id').val() == "") {
            $('.driverInfoSection .is_required').attr('required',"required");
        }
    }
    else {
        $('.driverInfoSection').addClass("hide");
        console.log('driver-1');
        $('.driverInfoSection .is_required').removeAttr('required');
    }
    if(is_staff) {
        $('.staff_section').removeClass("hide");
    }
    else {
        $('.staff_section').addClass("hide");
    }
    if(is_vendor) {
        $('.vendorInfoSection').removeClass("hide");
        if($('#id').val() == "") {
            $('.vendorInfoSection .is_required').attr('required',"required");
        }
    }
    else {
        $('.vendorInfoSection').addClass("hide");
        $('.vendorInfoSection .is_required').removeAttr('required');
    }
    if((is_driver) || (is_vendor)) {
        $('.accountDetailSection').removeClass("hide");
    }
    else {
        $('.accountDetailSection').addClass("hide");
    }
}

$(document).ready(function(){

    $(document).on('change', '.role', function(){
        var roles = $(this).val();

        roleFunction(roles);
    });

    $(document).on('click', '.selectAll', function(){
        $(".permissions > option").prop("selected", true);
        $(".permissions").trigger("change");
    });

    $(document).on('click', '.deselectAll', function(){
        $(".permissions > option").prop("selected", false);
        $(".permissions").trigger("change");
    });

    var roles = $('.role').val();
    roleFunction(roles);

    $('#add-fund-basic-form').validate();
    $('#revoke-fund-basic-form').validate();

});
</script>
<script>
  var checkbox = document.getElementById("myCheckbox");
  checkbox.disabled = true;
   checkbox.accent-color="#000";
</script>

<script>
    function disableInput() {
        var inputField = document.getElementById('bvnInput');
        if (inputField.value.trim() !== '') {
            inputField.disabled = true;
        }
    }
</script>
@endsection



