@extends('layouts.master') 
@section('content')

<div class="content-wrapper">
    <!-- Content -->

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
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="row">

                                @php
                                    $date = date('d F Y', strtotime($data->created_at));
                                @endphp
                                <div class="row">
                                    <div class="col-12">
                                        <h2>
                                            Order #0{{ $data->id }} Details
                                            <small class="float-end">Date:{{ $date }}</small>
                                        </h2>
                                    </div>
                                </div>

                                <div class="row invoice-info mt-2">
                                    <div class="col-sm-4 invoice-col edit_shipping1">
                                        <h4 class="fw-bolder">Shipping Details</h4>
                                        <p class="mb-0"> Name : {{ $data->user->name ?? '' }}</p>
                                        <p class="mb-0">Phone : {{ $data->user->phone ?? '' }}</p>
                                        <p>Address : {{ $data->shipping_address2 ?? '' }}</p>
                                    </div>
                                </div>

                                <form action="{{ route('admin.orders.change-order-status', $data->id) }}" method="POST" enctype="multipart/form-data" id="basic-form">
                                    @csrf
                                    <div class="form-group">
                                        <label for="name" class="mt-2">Order Status</label>
                                        <select name="order_status" class="form-control order_status form-select @error('order_status') is-invalid @enderror">
                                            @foreach($orderStatus as $key => $value)
                                                <option value="{{ $key }}" {{ old('order_status') ? ((old('order_status') == $key) ? 'selected' : '' ) : (isset($data) && isset($data->status) ? ($data->status == $key ? 'selected' : '' ) : '') }} >{{$value}}</option>
                                            @endforeach
                                        </select>
                                        @error('order_status')
                                            <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="name" class="mt-2">Note</label>
                                        <textarea name="note" class="order-note @error('note') is-invalid @enderror w-100" rows="4">{{ old('note', '') }}</textarea>
                                        @error('note')
                                            <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <input class="btn btn-primary order_status_btn" type="submit" value="Save" disabled>
                                    </div>
                                </form>
                                
                                <div class="table-responsive">
                                    <table id="" class="table table-striped table-bordered text-nowrap w-100 no-action">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th class="wd-15p">Product Name</th>
                                                <th class="wd-15p">Cost</th>
                                                <th class="wd-15p">Item Quantity</th>
                                                <th class="wd-15p">Quantity</th>
                                                <th class="wd-15p">status</th>
                                                <th class="wd-15p">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $i = 1;
                                                $total = 0;
                                                $subtotal = 0;
                                            @endphp
                                            @foreach ($data->orderItem as $item)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $item->products->name ?? '-' }}</td>
                                                    <td>{{ $item->price ?? '-' }}</td>
                                                    <td>{{ $item->item_qty ?? '-' }}</td>
                                                    <td>{{ $item->qty ?? '-' }}</td>
                                                    <td>{{ $item->status == 'A' ? 'Accepted' : ($item->status == 'R' ? 'Rejected' : '-') }}</td>
                                                    <td>{{ ($item->price * $item->qty) ?? '-' }}</td>
                                                    @php $subtotal += ($item->price * $item->qty); @endphp
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="row float-end w-50">
                                        <div class="table-responsive">
                                            <input type="hidden" name="orderid" value="{{ $data->id }}">
                                            <table class="table order-detail">
                                                    <tr>
                                                        <th>SUBTOTAL :</th>
                                                        <td>₹ {{ $subtotal }}</td>
                                                    </tr>
                                                @isset($data->surcharge)
                                                <tr>
                                                    <th>SURCHARGE :</th>
                                                    <td>₹ {{ $data->surcharge ?? '' }}</td>
                                                </tr>
                                                @endisset
                                                @if(isset($data->tax) && ($data->tax > 0))
                                                    @php $taxDataTax = json_decode($data->tax_id_1,true);
                                                        $taxDataTax1 = json_decode($data->tax_id_2,true);
                                                    @endphp
                                                @if(!empty($taxDataTax))
                                                    <tr>
                                                        <th> {{$taxDataTax['type']}} :</th>
                                                        <td>₹  {{ $taxDataTax['amount'] }}</td>
                                                    </tr>
                                                @endif
                                                @if(!empty($taxDataTax1))
                                                    <tr>
                                                        <th> {{$taxDataTax1['type']}} :</th>
                                                        <td>₹  {{ $taxDataTax1['amount'] }}</td>
                                                    </tr>
                                                @endif
                                                @endif
                                                @isset($data->coupon)
                                                    <tr>
                                                        <th>COUPON : {{ $data->coupon->coupon_code }}</th>
                                                        <td>(-) ₹ {{ $data->coupon->discounted_price }}</td>
                                                    </tr>
                                                @endisset
                                                @isset($data->delivery_charges)
                                                <tr>
                                                    <th>DELIVERY CHARGE :</th>
                                                    <td>₹ {{ $data->delivery_charges ?? '' }}</td>
                                                </tr>
                                                @endisset
                                                @isset($data->packing_fee)
                                                <tr>
                                                    <th>PACKING FEE :</th>
                                                    <td>₹ {{ $data->packing_fee ?? '' }}</td>
                                                </tr>
                                                @endisset
                                                @isset($data->tip_amount)
                                                <tr>
                                                    <th>TIP AMOUNT :</th>
                                                    <td>₹ {{ $data->tip_amount ?? '' }}</td>
                                                </tr>
                                                @endisset
                                                <tr>
                                                    <th>PAYMENT MODE :</th>
                                                    <td>{{ isset($data->order_type) ? ($data->order_type == 'O' ? 'Online' : ($data->order_type == 'C' ? 'COD' : '')) : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>GRAND TOTAL :</th>
                                                    <td class="border-bottom">₹ {{ $data->grand_total }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        $(document).on('change', '.order_status', function(){
            $('.order_status_btn').removeAttr('disabled',"disabled");
        });
    });
</script>
@endsection