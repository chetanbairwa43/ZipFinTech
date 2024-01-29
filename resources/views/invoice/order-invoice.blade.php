<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        .row {
            --bs-gutter-x: 30px;
            --bs-gutter-y: 0;
            display: flex;
            flex-wrap: wrap;
            margin-top: calc(-1 * var(--bs-gutter-y));
            margin-right: calc(-.5 * var(--bs-gutter-x));
            margin-left: calc(-.5 * var(--bs-gutter-x));
        }
        .mb-2 {
            margin-bottom: 0.5rem !important;
        }
        .mb-0 {
            margin-bottom: 0 !important;
        }
        .mt-2 {
            margin-top: 0.5rem !important;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table.table-bordered {
            border-top: 1px solid #dee2e6;
        }
        .table {
            margin-bottom: 0;
        }
        .text-nowrap {
            white-space: nowrap !important;
        }
        .w-100 {
            width: 100% !important;
        }
        .w-50 {
            width: 50% !important;
        }
        .float-end {
            float: right !important;
        }
        .table {
            --bs-table-bg: transparent;
            --bs-table-accent-bg: transparent;
            --bs-table-striped-color: #212529;
            --bs-table-striped-bg: rgba(0, 0, 0, 0.05);
            --bs-table-active-color: #212529;
            --bs-table-active-bg: rgba(0, 0, 0, 0.1);
            --bs-table-hover-color: #212529;
            --bs-table-hover-bg: #eaeaf1;
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            vertical-align: top;
            border-color: #dee2e6;
        }
        table {
            caption-side: bottom;
            border-collapse: collapse;
        }
        .table > thead {
            vertical-align: bottom;
        }
        thead, tbody, tfoot, tr, td, th {
            border-color: inherit;
            border-style: solid;
            border-width: 0;
        }
        .table > :not(:first-child), .jsgrid .jsgrid-table > :not(:first-child) {
            border-top: none;
        }
        .table-bordered > :not(caption) > * {
            border-width: 1px 0;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            --bs-table-accent-bg: var(--bs-table-striped-bg);
            color: var(--bs-table-striped-color);
        }
        table.no-action td {
            padding-top: 12px;
            padding-bottom: 12px;
        }
        .table td {
            white-space: unset;
        }
        .table td {
            padding: 0.45rem 0.5rem;
        }
        .table td {
            font-size: 0.812rem;
        }
        .table th, .table td {
            vertical-align: middle;
            white-space: nowrap;
            padding: 0.5rem 0.5rem;
        }
        .table-bordered > :not(caption) > * > * {
            border-width: 0 1px;
        }
        .table > :not(:last-child) > :last-child > *, .jsgrid .jsgrid-table > :not(:last-child) > :last-child > * {
            border-bottom-color: #dee2e6;
        }
        .table thead th {
            border-top: 0;
            border-bottom-width: 1px;
            font-weight: 600;
            font-size: .875rem;
        }
        .table th, .table td {
            vertical-align: middle;
            white-space: nowrap;
            padding: 0.5rem 0.5rem;
        }
        .table-bordered > :not(caption) > * > * {
            border-width: 0 1px;
        }
        th {
            text-align: inherit;
            text-align: -webkit-match-parent;
        }
        .order-detail th {
            font-size: 0.875rem;
        }
        .table td {
            font-size: 0.812rem;
        }
        h4 {
            font-size: 1.125rem;
        }
        h6 {
            font-size: .9375rem;
        }
        .fw-bolder {
            font-weight: bolder !important;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <img src="{{ public_path($logo)}}">
                        </div>
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
                                                        <td>Rs {{ $item->price }}</td>
                                                        <td>{{ $item->item_qty }}</td>
                                                        <td>{{ $item->qty }}</td>
                                                        <td>{{ $item->status == 'A' ? 'Accepted' : ($item->status == 'R' ? 'Rejected' : '-') }}</td>
                                                        <td>Rs {{ ($item->price * $item->qty) ?? '-' }}</td>
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
                                                        <td>Rs. {{ $subtotal }}</td>
                                                    </tr>
                                                    @isset($data->surcharge)
                                                    <tr>
                                                        <th>SURCHARGE :</th>
                                                        <td>Rs. {{ $data->surcharge ?? '' }}</td>
                                                    </tr>
                                                    @endisset
                                                    @if(isset($data->tax) && ($data->tax > 0))
                                                            @php $taxDataTax = json_decode($data->tax_id_1,true);
                                                                $taxDataTax1 = json_decode($data->tax_id_2,true);
                                                            @endphp
                                                       @if(!empty($taxDataTax))
                                                            <tr>
                                                                <th> {{$taxDataTax['type']}} :</th>
                                                                <td>Rs. {{ $taxDataTax['amount'] }}</td>
                                                            </tr>
                                                        @endif
                                                        @if(!empty($taxDataTax1))
                                                            <tr>
                                                                <th> {{$taxDataTax1['type']}} :</th>
                                                                <td>Rs. {{ $taxDataTax1['amount'] }}</td>
                                                            </tr>
                                                        @endif
                                                        @endif
                                                    @isset($data->coupon)
                                                        <tr>
                                                            <th>COUPON : {{ $data->coupon->coupon_code }}</th>
                                                            <td>(-) Rs. {{ $data->coupon->discounted_price }}</td>
                                                        </tr>
                                                    @endisset
                                                    @isset($data->delivery_charges)
                                                    <tr>
                                                        <th>DELIVERY CHARGE :</th>
                                                        <td>Rs. {{ $data->delivery_charges ?? '' }}</td>
                                                    </tr>
                                                    @endisset
                                                    @isset($data->packing_fee)
                                                    <tr>
                                                        <th>PACKING FEE :</th>
                                                        <td>Rs. {{ $data->packing_fee ?? '' }}</td>
                                                    </tr>
                                                    @endisset
                                                    @isset($data->tip_amount)
                                                    <tr>
                                                        <th>TIP AMOUNT :</th>
                                                        <td>Rs. {{ $data->tip_amount ?? '' }}</td>
                                                    </tr>
                                                    @endisset
                                                    <tr>
                                                        <th>PAYMENT MODE :</th>
                                                        <td>{{ isset($data->order_type) ? ($data->order_type == 'O' ? 'Online' : ($data->order_type == 'C' ? 'COD' : '')) : '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>GRAND TOTAL :</th>
                                                        <td class="border-bottom">Rs {{ $data->grand_total }}</td>
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
</body>
</html>