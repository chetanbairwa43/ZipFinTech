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
                                <input type="date" name="date_search" class="form-control withdrawal-date date_search @error('date_search') is-invalid @enderror" value="{{ isset($date_search) ? $date_search : '' }}" max="{{ date('Y-m-d'); }}">

                                <select class="form-control form-select mx-1" id="status" name="status">
                                    <option value="">Select Status</option>
                                    <option value="pending" {{ isset($status) ? ($status == "pending" ? 'selected' : '' ) : '' }} >Pending</option>
                                    <option value="approved" {{ isset($status) ? ($status == "approved" ? 'selected' : '' ) : '' }} >Approved</option>
                                    <option value="rejected" {{ isset($status) ? ($status == "rejected" ? 'selected' : '' ) : '' }} >Rejected</option>
                                </select>

                                <input type="text" name="keyword" id="keyword" class="form-control" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="Search" required>

                                <button class="btn-sm search-btn keyword-btn" type="submit">
                                    <i class="ti-search pl-3" aria-hidden="true"></i>
                                </button>

                                <a href="{{ route('admin.withdrawal-requests.index') }}" class="btn-sm reload-btn">
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
                                <h5>Withdrawal Requests</h5>
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

                                                @if(isset($_GET['date_search']))<input type="hidden" name="date_search" value="{{$_GET['date_search']}}">@endif
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
                                        <th>S No.</th>
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th class="text-center">Status</th>
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
                                            <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '' }}</td>
                                            <td>₹ {{ $value->amount ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d-M-Y') }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $value->status == 'P' ? 'badge-warning' : ($value->status == 'A' ? 'badge-success' : ($value->status == 'R' ? 'badge-danger' : '')) }} text-capitalize">{{ $value->status == 'P' ? 'Pending' : ($value->status == 'A' ? 'Approved' : ($value->status == 'R' ? 'Rejected' : '')) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($value->status == 'P')
                                                    <a href="{{ route('admin.withdrawal-requests.action', [$value->id, 'action'=> 'approve']) }}" class="btn btn-sm btn-icon p-1" name="action" value="approve">
                                                        <i class="mdi mdi-check-bold mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="Approve"></i>
                                                    </a>

                                                    <a href="{{ route('admin.withdrawal-requests.action', [$value->id, 'action'=> 'reject']) }}" class="btn btn-sm btn-icon p-1" name="action" value="reject">
                                                        <i class="mdi mdi-cancel" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="Reject"></i>
                                                    </a>
                                                @elseif($value->status == 'A')
                                                    <a class="btn btn-sm btn-icon p-1">
                                                        <i class="mdi mdi-check-bold mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="Approved"></i>
                                                    </a>
                                                @elseif($value->status == 'R')
                                                    <a class="btn btn-sm btn-icon p-1">
                                                        <i class="mdi mdi-cancel" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="Rejected"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="6">No Data Found</td></tr>
                                @endif
                            </table>
                            @if ((request()->get('keyword')) || (request()->get('date_search')) || (request()->get('status')) || (request()->get('items')))
                                {{ $data->appends(['keyword' => request()->get('keyword'),'date_search' => request()->get('date_search'),'status' => request()->get('status'),'items' => request()->get('items')])->links() }}
                            @else
                                {{ $data->links() }}
                            @endif
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
    $(document).on('change', '.withdrawal-date', function(){
        $(this).closest('form').submit();
    });
});
</script>
@endsection
