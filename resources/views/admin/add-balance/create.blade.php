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
                        {{ $data->id ? 'Add Balance' : 'Add Balance' }}
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.customer-update-balance', ['id' => $data->id]) }}" method="POST" enctype="multipart/form-data" id="basic-form">
                            @csrf
                            <input type="hidden" name="id" id="id" value="{{ isset($data) ? $data->id : '' }}">
                            
                            <div class="form-group">
                                <label for="name" class="mt-2"> Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Amount" value="{{$data->amount }}" required>
                                @error('amount')
                                    <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="mt-2"> Transaction Type <span class="text-danger">*</span></label>
                                    <select name="transaction_type" class="form-control form-select @error('transaction_type') is-invalid @enderror" required>
                                        <option value="" {{ old('transaction_type') ? ((old('transaction_type') == '') ? 'selected' : '' ) : ( (isset($data) && $data->transaction_type == '') ? 'selected' : '' ) }} >Select Transaction Type</option>
                                        <option value="dr" {{ old('transaction_type') ? ((old('transaction_type') == dr) ? 'selected' : '' ) : ( (isset($data) && $data->transaction_type == 'dr') ? 'selected' : '' ) }} >DEBIT</option>
                                        <option value="cr" {{ old('transaction_type') ? ((old('transaction_type') == cr) ? 'selected' : '' ) : ( (isset($data) && $data->transaction_type == 'cr') ? 'selected' : '' ) }} >CREDIT</option>
                                    </select>
                                    @error('transaction_type')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Transaction About <span class="text-danger">*</span></label>
                                    <input type="text" name="transaction_about" class="form-control @error('transaction_about') is-invalid @enderror" placeholder="Transaction About" value="{{$data->transaction_about }}" required>
                                    @error('transaction_about')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- <div class="form-group">
                                <label for="name" class="mt-2"> Transaction About <span class="text-danger">*</span></label>
                                <textarea name="transaction_about" class="ckeditor @error('transaction_about') is-invalid @enderror" id="ckeditor" required="required">{{ empty(old('transaction_about')) ? (isset($data) ? $data->transaction_about : '') : old('transaction_about') }}</textarea>
                                @error('transaction_about')
                                    <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div> -->

                            
                            
                            <div class="mt-3">
                                <input class="btn btn-primary" type="submit" value="{{isset($data->id) ? 'Save' : 'Save' }}">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
