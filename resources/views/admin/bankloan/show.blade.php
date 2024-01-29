@extends('layouts.master')
@section('content')

<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-bottom">
                       Loan Details
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="row">
                                <div class="col-md-4 ">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">First Name</h6>
                                        <p class="mb-0">{{ $value->user->fname ?? ''}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4 ">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Last Name</h6>
                                        <p class="mb-0">{{ $value->user->lname ?? ''}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Loan Purpose</h6>
                                        <p class="mb-0">{{ $value->loan_purpose ?? ''}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Residential Status</h6>
                                        <p class="mb-0">{{ $value->residential_status ?? ''}}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Employed Status</h6>
                                        <p class="mb-0">{!! $value->employed_status ?? '' !!}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Monthly Income</h6>
                                        <p class="mb-0">{!! $value->monthly_income ?? '' !!}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Duration</h6>
                                        <p class="mb-0">{!! $value->duration_of_loan ?? '' !!}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Desired Amount</h6>
                                        <p class="mb-0">{!! $value->desired_amount ?? '' !!}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 listViewclr">
                                        <h6 class="fw-bolder">Is your income likely to change in the next 6 - 12 months</h6>
                                        <p class="mb-0">{{($value->increament == 0) ? 'NO':'Yes' ??'-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="p-3 listViewclr">
                                        <form action="{{url('admin/loan-status-update')}}" method="GET">
                                            <input type="hidden" value="{{$value->id}}" name="id">

                                            <div class="row">
                                                <div class="form-group col-md-3">
                                                    <label for="loan_amount">Loan Amount</label>
                                                    <input type="text" name="loan_amount"class="form-control @error('title') is-invalid @enderror" placeholder="Loan Amount" value="{{ old('loan_amount', isset($value) ? $value->loan_amount : '') }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="loan_duration">Loan Duration</label>
                                                    <input type="text" name="loan_duration"class="form-control @error('title') is-invalid @enderror" placeholder="Loan Duration" value="{{ old('loan_duration', isset($value) ? $value->loan_duration : '') }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="flat_fee">Interest or Flat Fee(In percentage %)</label>
                                                    <input type="text" name="flat_fee"class="form-control @error('title') is-invalid @enderror" placeholder="Interest or Flat Fee" value="{{ old('Flat Fee', isset($value) ? $value->flat_fee : '') }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="fee_type">Fee Type</label>
                                                    <input type="text" name="fee_type"class="form-control @error('title') is-invalid @enderror" placeholder="Fee Type" value="{{ old('fee_type', isset($value) ? $value->fee_type : '') }}">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-3">
                                                    <label for="repayment_method">Repayment Method</label>
                                                    <input type="text" name="repayment_method"class="form-control @error('title') is-invalid @enderror" placeholder="Repayment Method" value="{{ old('repayment_method', isset($value) ? $value->repayment_method : '') }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="repayment_date">Repayment Date</label>
                                                    <input type="date" name="repayment_date"class="form-control @error('title') is-invalid @enderror" placeholder="Repayment Date" value="{{ old('repayment_date', isset($value) ? $value->repayment_date : '') }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="warning_date"> Warning Date</label>
                                                    <input type="date" name="warning_date"class="form-control @error('title') is-invalid @enderror" placeholder="Warning Date" value="{{ old('warning_date', isset($value) ? $value->warning_date : '') }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="collection_method">Collection Method</label>
                                                    <input type="text" name="collection_method"class="form-control @error('title') is-invalid @enderror" placeholder="Collection Method" value="{{ old('collection_method', isset($value) ? $value->collection_method : '') }}">
                                                </div>
                                            </div>
                                            <div class="row">
                                                    <div class="form-group col-md-3">
                                                        <label for="repayment_schedule">Repayment Schedule</label>
                                                        <input type="text" name="repayment_schedule"class="form-control @error('title') is-invalid @enderror" placeholder="Repayment Schedule" value="{{ old('repayment_schedule', isset($value) ? $value->repayment_schedule : '') }}">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="loan_amount">Loan Approve Status</label>
                                                        <select name="is_approved" class="form-select text-muted mr-1 myInput" >
                                                            <option class="text-dark" value="rejected"{{isset($value) && $value->is_approved=="rejected"?'selected':''}}>&nbsp;&nbsp;Rejected
                                                             </option>
                                                             <option class="text-dark" value="pending" {{isset($value) && $value->is_approved=="pending"?'selected':''}}>&nbsp;&nbsp;Pending
                                                            </option>
                                                             <option class="text-dark" value="approved" {{isset($value) && $value->is_approved=="approved"?'selected':''}}>&nbsp;&nbsp;Approved
                                                            </option>
                                                         </select>
                                                    </div>
                                            </div>

                                            <div class="mt-3">
                                                <input class="btn btn-primary" type="submit" value="{{ isset($value) && isset($value->id) ? 'Update' : 'Save' }}">
                                            </div>
                                    </form>
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
