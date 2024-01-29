@extends('layouts.master')
@section('content')

<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        Transaction details
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="row">
                                <div class="col-md-4 ">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">User Name</h6>
                                        <p class="mb-0">{{ $data->user->fname ?? '-'}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Amount</h6>
                                        <p class="mb-0">{{isset($data) ? ($data->amount) :'0' }}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Date</h6>
                                        <p class="mb-0">{{ \Carbon\Carbon::parse(isset($data) ?( $data->created_at ) : '')->format('d F,Y')}}
                                        </p>
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Type</h6>
                                        <p class="mb-0">{{isset($data) ? ($data->transaction_type == 'dr') ? 'Debit':'Credit' :''}}
                                        </p>
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Transaction about</h6>
                                        <p class="mb-0">{{ isset($data) ?($data->transaction_about):''}}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">

                                        <h6 class="fw-bolder">Transaction ID</h6>
                                        <p class="mb-0">{{isset($data) ?( $data->t_id) : ''}}</p>

                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Description</h6>
                                        <p class="mb-0">{{isset($data) ? ($data->description) : '-'}}</p>
                                    </div>
                                </div>
                            </div>

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
