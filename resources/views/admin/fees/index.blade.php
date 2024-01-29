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
                    <div class="card-header ">
                        <div class="row">
                            <div class="col-xl-6 col-md-6 mt-auto">
                                <h5>Fees</h5>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table">
                            <table id="example" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="text-center">Value</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                @if(count($data)>0)
                                    @php 
                                        isset($_GET['items']) ? $items = $_GET['items'] : $items = 10;
                                        isset($_GET['page']) ? $page = $_GET['page'] : $page = 1;

                                        $i = (($page-1)*$items)+1; 
                                    @endphp

                                    @foreach($data as $key => $value)
                                        <tr data-entry-id="{{ $value->id }}">
                                        <td>{{ ucwords(str_replace('_', ' ', $value->key)) ?? '' }}</td>
                                            <form action="{{ route('admin.fees.update') }}" method="POST" enctype="multipart/form-data" id="basic-form">
                                              @csrf

                                                <td class="text-center">
                                                    <input type="number" name="values[{{ $value->key }}]" value="{{ $value->value }}" />
                                                </td>
                                                <td class="text-center">
                                                    <button type="submit" class="btn btn-primary btn-icon p-1 update-record" data-key="{{ $value->key }}">Update</button>
                                                </td>
                                            </form>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="4">No Data Found</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection




