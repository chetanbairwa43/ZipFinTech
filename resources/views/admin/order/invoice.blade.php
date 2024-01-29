@extends('layouts.master') 
@section('content')

<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <!-- <div class="card-header border-bottom">
                       Invoice
                    </div> -->
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="row">
                                <div class="row">
                                    <div class="col-12">
                                        <h2>
                                            #INV-{{ $data->id }}
                                            <small class="float-end">Date:{{ date('d F Y', strtotime($data->created_at)) }}</small>
                                        </h2>
                                    </div>
                                </div>

                                <div class="row invoice-info mt-2">
                                    <div class="col-sm-4 invoice-col edit_shipping1">
                                        <h4 class="fw-bolder">Invoice To: </h4>
                                        <h6 class="mb-0">{{ $data->user->name ?? '' }}</h6>
                                        <p class="mb-0">Address : {{ isset($data->orderAddress) ? (!empty($data->orderAddress->location) ? $data->orderAddress->location : '' ) : '' }}</p>
                                        <p>Phone : {{ $data->user->phone ?? '' }}</p>
                                    </div>
                                </div>
                                
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
                                                    <td>{{ $item->products->name }}</td>
                                                    <td>{{ $item->price }}</td>
                                                    <td>{{ $item->item_qty }}</td>
                                                    <td>{{ $item->qty }}</td>
                                                    <td>{{ $item->status == 'A' ? 'Accepted' : ($item->status == 'R' ? 'Rejected' : '-') }}</td>
                                                    <td>{{ ($item->price * $item->qty) ?? '-' }}</td>
                                                    @php $subtotal += ($item->price * $item->qty); @endphp
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="row float-end w-50">
                                        <div class="table-responsive">
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
                                                       @if(!empty($taxDataTax1))
                                                            <tr>
                                                                @foreach($taxDataTax as $key => $value)
                                                                <th> {{$key}} :</th>
                                                                <td>₹  {{ $value }}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endif
                                                        @if(!empty($taxDataTax1))
                                                            <tr>
                                                                @foreach($taxDataTax1 as $key => $value)
                                                                <th> {{$key}} :</th>
                                                                <td>₹  {{ $value }}</td>
                                                                @endforeach
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
                            <a class="btn btn-primary" href="#">
                            <span class="mdi mdi-file-download-outline"></span>
                                {{ 'Download Invoice' }}
                            </a>
                            <!-- <button type="button" class="btn btn-info mb-1" onclick="javascript:window.print();">
                                <i class="si si-printer"></i>
                                {{ 'Print Invoice' }}
                            </button> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
