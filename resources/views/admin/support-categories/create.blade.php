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
                        {{ isset($data) && isset($data->id) ? 'Edit Category' : 'Create Category' }}
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.support-categories.store') }}" method="POST" enctype="multipart/form-data" id="basic-form">
                            @csrf
                            <input type="hidden" name="id" id="id" value="{{ isset($data) ? $data->id : '' }}">
                            <input type="hidden" name="slug" id="slug" value="{{ isset($data) ? $data->slug : '' }}">
                            
                            <div class="form-group">
                                <label for="name" class="mt-2"> Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="Title" value="{{ old('title', isset($data) ? $data->title : '') }}" required>
                                @error('title')
                                    <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            {{-- <div class="row">
                                <div class="form-group {{ isset($data) ? 'col-md-6' : '' }} ">
                                    <label for="name" class="mt-2"> Image <span class="text-danger">*</span> <span class="text-danger info">(Only jpeg, png, jpg files allowed)</span></label>
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png" {{ isset($data) && isset($data->id) ? '' : 'required' }}>
                                    <input type="hidden" class="form-control" name="imageOld" value="{{ isset($data) ? $data->image : ''}}">
                                    @error('image')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                @if(!empty($data->image))
                                <div class="form-group col-md-6">
                                    <div class="mt-3">
                                        <span class="pip" data-title="{{$data->image}}">
                                            <img src="{{ url(config('app.category_image')).'/'.$data->image ?? '' }}" alt="" width="150" height="100">
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div> --}}

                            {{-- <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="mt-2"> Admin Commission Type</label>
                                    <select name="admin_commission_type" class="form-control form-select @error('admin_commission_type') is-invalid @enderror admin_commission_type">
                                        <option value="" {{ old('admin_commission_type') ? ((old('admin_commission_type') == '') ? 'selected' : '' ) : (isset($data) && isset($data['admin_commission_type']) ? ($data['admin_commission_type'] == '' ? 'selected' : '' ) : '' ) }} >Select Type</option>
                                        <option value="fixed" {{ old('admin_commission_type') ? ((old('admin_commission_type') == 'fixed') ? 'selected' : '' ) : (isset($data) && isset($data['admin_commission_type']) ? ($data['admin_commission_type'] == 'fixed' ? 'selected' : '' ) : '' ) }} >Fixed</option>
                                        <option value="percentage" {{ old('admin_commission_type') ? ((old('admin_commission_type') == 'percentage') ? 'selected' : '' ) : (isset($data) && isset($data['admin_commission_type']) ? ($data['admin_commission_type'] == 'percentage' ? 'selected' : '' ) : '' ) }} >Percentage</option>
                                    </select>
                                    @error('admin_commission_type')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Admin Commission <span class="text-danger admin_commission_required">*</span></label>
                                    <input type="text" name="admin_commission" class="form-control admin_commission @error('admin_commission') is-invalid @enderror" placeholder="Admin Commission" value="{{ old('admin_commission', isset($data) ? $data->admin_commission : '' ) }}">
                                    @error('admin_commission')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div> --}}

                            {{-- <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="mt-2"> Tax (%)</label>
                                    <select name="tax_percent" class="form-control form-select @error('tax_percent') is-invalid @enderror">
                                        <option value="" >Select Tax</option>
                                        @foreach($tax as $value)
                                            <option value="{{ $value->id }}" {{ old('tax_percent') ? ((old('tax_percent') == $value->id) ? 'selected' : '' ) : (isset($data) && isset($data->tax) ? ($data->tax->id == $value->id ? 'selected' : '' ) : '') }} >{{$value->title .' ('.$value->tax_percent.' %)'}}</option>
                                        @endforeach
                                    </select>
                                    @error('tax_percent')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="mt-2"> Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control form-select @error('status') is-invalid @enderror" required>
                                        <option value="" {{ old('status') ? ((old('status') == '') ? 'selected' : '' ) : ( (isset($data) && $data->status == 0) ? 'selected' : '' ) }} >Select Status</option>
                                        <option value="1" {{ old('status') ? ((old('status') == 1) ? 'selected' : '' ) : ( (isset($data) && $data->status == 1) ? 'selected' : '' ) }} >Active</option>
                                        <option value="0" {{ old('status') ? ((old('status') == 0) ? 'selected' : '' ) : ( (isset($data) && $data->status == 0) ? 'selected' : '' ) }} >In-Active</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback form-invalid fw-bold" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div> --}}
                            
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

@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        if($('.admin_commission_type').val() == ''){
            $('.admin_commission_required').addClass('hide');
        }
        $(document).on('change', '.admin_commission_type', function(){
            if(this.value != '') {
                $('.admin_commission').attr('required',"required");
                $('.admin_commission_required').removeClass('hide');
            }
            else {
                $('.admin_commission').removeAttr('required');
                $('.admin_commission_required').addClass('hide');
            }
        });
    });
</script>
@endsection