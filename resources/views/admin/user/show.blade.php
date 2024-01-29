@extends('layouts.master') 
@section('content')

<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-bottom">
                       User Details
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="row">

                                <h5 class="fw-bolder">Basic Information</h5>
                                <div class="col-md-4 ">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">First Name</h6>
                                        <p class="mb-0">{{ $data->fname ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4 ">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Last Name</h6>
                                        <p class="mb-0">{{ $data->lname ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">DOB</h6>
                                        <p class="mb-0">{{ $data->dob ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Phone</h6>
                                        <p class="mb-0">{{ $data->phone ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Wallet Balance</h6>
                                        <p class="mb-0">{{ number_format((float)$data->wallet_balance, 2, '.', '') }}</p>
                                    </div>
                                </div>

                                {{-- <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">User Role</h6>

                                        @if((isset($data)) && (isset($data->roles)))
                                            <p class="mb-0">
                                                @foreach($data->roles as $key => $value)
                                                    {{ $value->roles->name ?? '' }}
                                                @endforeach
                                            </p>
                                        @endif
                                    </div>
                                </div> --}}

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Email</h6>
                                        <p class="mb-0">{{ $data->email ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Account Number</h6>
                                        <p class="mb-0">{{ $virtual->accountNumber ?? '-'}}</p>
                                    </div>
                                </div>

                                @if(isset($virtual))
                                    @php
                                        $accountInformation = json_decode($virtual->accountInformation, true);
                                        $accountName = $accountInformation['accountName'];
                                        $bankName = $accountInformation['bankName'];
                                    @endphp
                                @endif

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Account Holder Name</h6>
                                        <p class="mb-0">{{ $accountName ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Account Type</h6>
                                        <p class="mb-0">{{ $virtual->accountType ?? '-'}}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Bank Name</h6>
                                        <p class="mb-0">{{ $bankName ?? '-'}}</p>
                                    </div>
                                </div>  
                            
                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Business</h6>
                                        <p class="mb-0">{{ $virtual->business ?? '-'}}</p>
                                    </div>
                                </div>  

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Business Id</h6>
                                        <p class="mb-0">{{ $virtual->business_id ?? '-'}}</p>
                                    </div>
                                </div>  


                                {{-- <div class="col-md-4">
                                    <div class="p-3 listViewclr">  
                                        <h6 class="fw-bolder">Location</h6>
                                        <p class="mb-0">{{ $data->location ?? '-' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">  
                                        <h6 class="fw-bolder">Latitude</h6>
                                        <p class="mb-0">{{ $data->latitude ?? '-' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">  
                                        <h6 class="fw-bolder">Longitude</h6>
                                        <p class="mb-0">{{ $data->longitude ?? '-' }}</p>
                                    </div>
                                </div> --}}

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">  
                                        <h6 class="fw-bolder">Status</h6>
                                        <p class="mb-0">{{ isset($data) && ($data->status == 1) ? 'Active' : 'In-Active'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Profile Image</h6>

                                        @if(!empty($data->profile_image))
                                            <div class="even mt-3">
                                                <div class="parc">
                                                    <span class="pip" data-title="{{$data->profile_image}}">
                                                        <img src="{{ url(config('app.profile_image')).'/'.$data->profile_image ?? '' }}" alt="" width="150" height="100">
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <p class="mb-0"> No Image Found </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Selfie Image</h6>

                                        @if(!empty($data->user_image))
                                            <div class="even mt-3">
                                                <div class="parc">
                                                    <span class="pip" data-title="{{$data->user_image}}">
                                                        <img src="{{url('uploads/profile-images/'.$data->user_image) }}" alt="" width="150" height="100">
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <p class="mb-0"> No Image Found </p>
                                        @endif
                                    </div>
                                </div>
                                @php
                                     $base64String = 'data:image/png;base64,'.$fincraUser->avatar;
                                @endphp

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">BVN Image</h6>

                                        @if(!empty($base64String))
                                            <div class="even mt-3">
                                                <div class="parc">
                                                    <span class="pip" data-title="{{$base64String}}">
                                                        <img src="{{$base64String }}" alt="" width="150" height="100">
                                                        {{-- <img src="{{url('storage/public/'.$fincraUser->avatar) }}" alt="" width="150" height="100"> --}}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <p class="mb-0"> No Image Found </p>
                                        @endif
                                    </div>
                                </div>
                            </div> 
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Street Name</h6>
                                        <p class="mb-0">{{ $address->street_name ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">House Number</h6>
                                        <p class="mb-0">{{ $address->house_number ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Postal Code</h6>
                                        <p class="mb-0">{{ $address->postal_code ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">City</h6>
                                        <p class="mb-0">{{ $address->city ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">State</h6>
                                        <p class="mb-0">{{ $address->state ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Country</h6>
                                        <p class="mb-0">{{ $address->country ?? '-'}}</p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- @if(isset($data) && isset($data->driver))
                                <div class="row mt-4">

                                    <h5 class="fw-bolder">Driver Information</h5>

                                    <div class="col-md-4 ">
                                        <div class="p-3 listViewclr">
                                            <h6 class="fw-bolder">Date Of Birth</h6>
                                            <p class="mb-0">{{ date('jS M Y', strtotime($data->driver->dob)) ?? '-'}}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Aadhar Number</h6>
                                            <p class="mb-0">{{ $data->driver->aadhar_no ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Pan Card Number</h6>
                                            <p class="mb-0">{{ $data->driver->pan_no ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Vehicle Number</h6>
                                            <p class="mb-0">{{ $data->driver->vehicle_no ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Driving Licence Number</h6>
                                            <p class="mb-0">{{ $data->driver->licence_no ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Driver</h6>
                                            <p class="mb-0">{{ $data->is_driver_online ? 'Online' : 'Offline' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Bank Statement/ Cheque</h6>
                                                </div>

                                                @if(!empty($data->driver->bank_statement))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.driver_document')).'/'.$data->driver->bank_statement }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->driver->bank_statement))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->driver->bank_statement}}">
                                                            <img src="{{ url(config('app.driver_document')).'/'.$data->driver->bank_statement ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Licence Front Side</h6>
                                                </div>

                                                @if(!empty($data->driver->licence_front_image))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.driver_document')).'/'.$data->driver->licence_front_image }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->driver->licence_front_image))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->driver->licence_front_image}}">
                                                            <img src="{{ url(config('app.driver_document')).'/'.$data->driver->licence_front_image ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Licence Back Side</h6>
                                                </div>

                                                @if(!empty($data->driver->licence_back_image))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.driver_document')).'/'.$data->driver->licence_back_image }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->driver->licence_back_image))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->driver->licence_back_image}}">
                                                            <img src="{{ url(config('app.driver_document')).'/'.$data->driver->licence_back_image ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Aadhar Card Front Side</h6>
                                                </div>

                                                @if(!empty($data->driver->aadhar_front_image))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.driver_document')).'/'.$data->driver->aadhar_front_image }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->driver->aadhar_front_image))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->driver->aadhar_front_image}}">
                                                            <img src="{{ url(config('app.driver_document')).'/'.$data->driver->aadhar_front_image ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Aadhar Card Back Side</h6>
                                                </div>

                                                @if(!empty($data->driver->aadhar_back_image))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.driver_document')).'/'.$data->driver->aadhar_back_image }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->driver->aadhar_back_image))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->driver->aadhar_back_image}}">
                                                            <img src="{{ url(config('app.driver_document')).'/'.$data->driver->aadhar_back_image ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Pan Card</h6>
                                                </div>

                                                @if(!empty($data->driver->pan_card_image))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.driver_document')).'/'.$data->driver->pan_card_image }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->driver->pan_card_image))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->driver->pan_card_image}}">
                                                            <img src="{{ url(config('app.driver_document')).'/'.$data->driver->pan_card_image ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Status</h6>
                                            <p class="mb-0">{{ $data->as_driver_verified ? 'Approved' : 'Un-Approved' }}</p>
                                        </div>
                                    </div>

                                </div>
                            @endif

                            @if(isset($data) && isset($data->vendor))
                                <div class="row mt-4">

                                    <h5 class="fw-bolder">Vendor Information</h5>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Store Name</h6>
                                            <p class="mb-0">{{ $data->vendor->store_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Store Address</h6>
                                            <p class="mb-0">{{ !empty($data->vendor->address) ? $data->vendor->address : '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Store Location</h6>
                                            <p class="mb-0">{{ $data->vendor->location ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Aadhar Number</h6>
                                            <p class="mb-0">{{ $data->vendor->aadhar_no ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Pan Card Number</h6>
                                            <p class="mb-0">{{ $data->vendor->pan_no ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Self Delivery</h6>
                                            <p class="mb-0">{{ $data->self_delivery ? 'Online' : 'Offline' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Delivery Range</h6>
                                            <p class="mb-0">{{ $data->delivery_range ?? '-' }} Km</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Admin Commission</h6>
                                            <p class="mb-0">₹ {{ $data->admin_commission ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Store</h6>
                                            <p class="mb-0">{{ $data->is_vendor_online ? 'Open' : 'Close' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Bank Statement/ Cheque</h6>
                                                </div>

                                                @if(!empty($data->vendor->bank_statement))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.vendor_document')).'/'.$data->vendor->bank_statement }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->vendor->bank_statement))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->vendor->bank_statement}}">
                                                            <img src="{{ url(config('app.vendor_document')).'/'.$data->vendor->bank_statement ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Pan Card</h6>
                                                </div>

                                                @if(!empty($data->vendor->pan_card_image))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.vendor_document')).'/'.$data->vendor->pan_card_image }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->vendor->pan_card_image))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->vendor->pan_card_image}}">
                                                            <img src="{{ url(config('app.vendor_document')).'/'.$data->vendor->pan_card_image ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Aadhar Card Front Side</h6>
                                                </div>

                                                @if(!empty($data->vendor->aadhar_front_image))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.vendor_document')).'/'.$data->vendor->aadhar_front_image }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->vendor->aadhar_front_image))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->vendor->aadhar_front_image}}">
                                                            <img src="{{ url(config('app.vendor_document')).'/'.$data->vendor->aadhar_front_image ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Aadhar Card Back Side</h6>
                                                </div>

                                                @if(!empty($data->vendor->aadhar_back_image))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.vendor_document')).'/'.$data->vendor->aadhar_back_image }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->vendor->aadhar_back_image))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->vendor->aadhar_back_image}}">
                                                            <img src="{{ url(config('app.vendor_document')).'/'.$data->vendor->aadhar_back_image ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h6 class="fw-bolder">Store Image</h6>
                                                </div>

                                                @if(!empty($data->vendor->store_image))
                                                    <div class="col-md-2">
                                                        <a href="{{ url(config('app.vendor_document')).'/'.$data->vendor->store_image }}" download><i class="mdi mdi-download"></i></a>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!empty($data->vendor->store_image))
                                                <div class="even mt-3">
                                                    <div class="parc">
                                                        <span class="pip" data-title="{{$data->vendor->store_image}}">
                                                            <img src="{{ url(config('app.vendor_document')).'/'.$data->vendor->store_image ?? "" }}" alt="" width="260" height="150">
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="mb-0"> No Image Found </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Status</h6>
                                            <p class="mb-0">{{ $data->as_vendor_verified ? 'Approved' : 'Un-Approved' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Store Timing</h6>
                                            @if(isset($data) && count($data->vendor_availability)>0)
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Day</th>
                                                            <th>Open Time</th>
                                                            <th>Close Time</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($data->vendor_availability as $value)
                                                        <tr>
                                                            <td>{{$week_arr[$value->week_day]}}</td>
                                                            <td>{{date("h:i A", strtotime($value->start_time))}}</td>
                                                            <td>{{date("h:i A", strtotime($value->end_time))}}</td>
                                                            <td><p class="mb-0 {{$value->status == 1 ? 'text-success' : 'text-danger'}}">{{$value->status == 1 ? 'Open' : 'Close'}}</p></td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <p class="mb-0">No Data Found</p>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            @endif

                            @if(isset($data) && isset($data->bank_account))
                                <div class="row mt-4">

                                    <h5 class="fw-bolder">Bank Account Information</h5>

                                    <div class="col-md-6 ">
                                        <div class="p-3 listViewclr">
                                            <h6 class="fw-bolder">Bank</h6>
                                            <p class="mb-0">{{ $data->bank_account->bank->name ?? '-'}}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Account Holder Name</h6>
                                            <p class="mb-0">{{ $data->bank_account->account_name ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">Bank Account Number</h6>
                                            <p class="mb-0">{{ $data->bank_account->account_no ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 listViewclr">  
                                            <h6 class="fw-bolder">IFSC Code</h6>
                                            <p class="mb-0">{{ $data->bank_account->ifsc_code ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div> --}}
                            {{-- @endif --}}

                            <a class="btn btn-danger btn_back" href="{{ url()->previous() }}">
                                {{ 'Back to list' }}
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
