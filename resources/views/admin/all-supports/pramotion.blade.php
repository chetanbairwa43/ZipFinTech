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
                <div class="row tabelhed d-flex justify-content-between">
                    <div class="col-lg-2 col-md-2 col-sm-2 d-flex">
                    </div>

                    <div class="col-lg-10 col-md-10"> 
                        <div class="right-item d-flex justify-content-end" >
                            <form action="" method="GET" class="d-flex">
                                {{-- <input type="date" name="date_search" class="form-control withdrawal-date date_search @error('date_search') is-invalid @enderror" value="{{ isset($date_search) ? $date_search : '' }}" max="{{ date('Y-m-d'); }}"> --}}

                                <select class="form-control form-select mx-1" id="status" name="status">
                                    <option value="">Select Status</option>
                                    <option value="success" {{ isset($status) ? ($status == "success" ? 'selected' : '' ) : '' }} >Success</option>
                                    
                                    <option value="failed" {{ isset($status) ? ($status == "failed" ? 'selected' : '' ) : '' }} >Failed</option>
                                </select>

                                <input type="text" name="keyword" id="keyword" class="form-control" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="Search">

                                <button class="btn-sm search-btn keyword-btn" type="submit">
                                    <i class="ti-search pl-3" aria-hidden="true"></i>
                                </button>

                                <a href="{{ route('admin.transaction.pramotion') }}" class="btn-sm reload-btn">
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
                                <h5>Pramotion Transactions</h5>
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

                                                {{-- @if(isset($_GET['date_search']))<input type="hidden" name="date_search" value="{{$_GET['date_search']}}">@endif --}}
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
                                        <th>Phone</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Remark</th>
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
                                            <td>{{ $value->name ?? '' }}</td>
                                            <td>{{ $value->phone ?? '' }}</td>
                                            <td>₹ {{ $value->amount ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d-M-Y') }}</td>
                                            <td>{{ $value->status == 'S' ? 'Success': ($value->status == 'F' ? 'Failed' : '')}}</td>
                                            <td>{{ $value->remark ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="7">No Data Found</td></tr>
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