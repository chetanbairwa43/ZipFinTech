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
                <div class="row tabelhed d-flex justify-content-end">
                    <div class="col-lg-10 col-md-10">
                        <div class="right-item d-flex justify-content-end" >
                            <form action="" method="GET" class="d-flex">
                            <select class="form-control mx-1" id="status" name="is_approved">
                            <option value="">Select Approve</option>
                            <option value="rejected" {{ (request()->get('is_approved') == "rejected" ? 'selected' : '' ) }} >Rejected</option>
                            <option value="pending" {{ (request()->get('is_approved') == "pending" ? 'selected' : '' ) }} >Pending</option>
                            <option value="approved" {{ (request()->get('is_approved') == "approved" ? 'selected' : '' ) }} >Approve</option>
                            </select>
                                <input type="text" name="keyword" id="keyword" class="form-control" value="{{ request()->keyword }}" placeholder="Search" required>

                                <button class="btn-sm search-btn keyword-btn" type="submit">
                                    <i class="ti-search pl-3" aria-hidden="true"></i>
                                </button>

                                <a href="{{ route('admin.bankloan.index') }}" class="btn-sm reload-btn">
                                    <i class="ti-reload pl-3 redirect-icon" aria-hidden="true"></i>
                                </a>
                                @if(isset($_GET['items']))<input type="hidden" name="items" value="{{$_GET['items']}}">@endif
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header ">
                        <div class="row">
                            <div class="col-xl-6 col-md-6 mt-auto">
                                <h5>Loan Details</h5>
                            </div>
                            <div class="col-xl-6 col-md-6">
                                <div class="row float-end">
                                    <div class="col-xl-12 d-flex float-end">
                                        <div class="items paginatee">
                                            <form action="" method="GET">
                                                <select class="form-select m-0 items" name="items" id="items" aria-label="Default select example">
                                                    <option value='10' {{ isset($items) ? ($items == '10' ? 'selected' : '' ) : '' }}>10</option>
                                                    <option value='20' {{ isset($items) ? ($items == '20' ? 'selected' : '' ) : '' }}>20</option>
                                                    <option value='30' {{ isset($items) ? ($items == '30' ? 'selected' : '' ) : '' }}>30</option>
                                                    <option value='40' {{ isset($items) ? ($items == '40' ? 'selected' : '' ) : '' }}>40</option>
                                                    <option value='50' {{ isset($items) ? ($items == '50' ? 'selected' : '' ) : '' }}>50</option>
                                                </select>

                                                {{-- @if(isset($_GET['role']))<input type="hidden" name="role" value="{{$_GET['role']}}">@endif --}}
                                                @if(isset($_GET['status']))<input type="hidden" name="status" value="{{$_GET['status']}}">@endif
                                                @if(isset($_GET['keyword']))<input type="hidden" name="keyword" value="{{$_GET['keyword']}}">@endif
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table">
                            <table id="example" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.no.</th>
                                        <th>First Name</th>
                                        {{-- <th>Last Name</th> --}}
                                        <th>Loan Purpose</th>
                                        {{-- <th>Residential Status</th> --}}
                                        {{-- <th>Employed Status</th>--}}
                                        <th>Monthly Income</th>
                                        <th>Duration of time</th>
                                        <th>Approved</th>
                                        <!--<th class="text-center">Status</th>-->
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
                                            <td>{{ $i++ ?? ''}}</td>
                                            <td>{{ $value->user->fname ?? '' }}</td>
                                            {{-- <td>{{ $value->user->lname ?? '' }}</td> --}}
                                            <td>{{ $value->loan_purpose ?? '' }}</td>
                                           {{-- <td>{{ $value->residential_status ?? '' }}</td>  --}}
                                            {{-- <td>{{ $value->employed_status ?? '' }}</td> --}}
                                            <td>{{ $value->monthly_income ?? '' }}</td>
                                            <td>{{ $value->duration_of_loan ?? ''  }}</td>
                                            <td>{{ $value->is_approved ?? ''  }}</td>

                                            <!--<td class="text-center">           -->
                                                {{-- <!--<a href="{{ route('admin.bankloan.edit', $value->id) }}" class="btn btn-sm btn-icon p-1">--> --}}
                                                <!--    <i class="mdi mdi-pencil" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="Edit"></i>-->
                                                <!--</a>-->
                                            <!--</td>-->
                                            <td class="text-center">
                                                <a href="{{ route('admin.bankloan.show', $value->id) }}" class="btn btn-sm btn-icon p-1">
                                                    <i class="mdi mdi-eye mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="View"></i>
                                                </a>

                                                {{-- <a href="{{ route('admin.users.edit', $value->id) }}" class="btn btn-sm btn-icon p-1">
                                                    <i class="mdi mdi-pencil" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="Edit"></i>
                                                </a> --}}

                                                <button type="submit" class="btn btn-sm btn-icon p-1 delete-record" route="{{ route('admin.users.destroy', $value->id) }}"><i class="mdi mdi-delete" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="Delete"></i></button>

                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="4">No Data Found</td></tr>
                                @endif
                            </table>
                            {{ $data->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
