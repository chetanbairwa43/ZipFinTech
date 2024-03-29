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
                            {{-- <a class="ad-btn btn text-center" href="{{ route('admin.tertiary-categories.create') }}" style="background-color:#7ED957"> Add</a> --}}
                    </div>

                    <div class="col-lg-10 col-md-10">

                        <div class="right-item d-flex justify-content-end" >
                            <form action="" method="GET" class="d-flex">
                            <select class="form-control mx-1" id="status" name="status">
                                <option value="">Select Status</option>
                                <option value="verified" {{ (request()->get('status') == "verified" ? 'selected' : '' ) }} >Verified</option>
                                <option value="un-verified" {{ (request()->get('status') == "un-verified" ? 'selected' : '' ) }} >Un-verified</option>
                                </select>
                            <input type="date" name="date_search" class="form-control withdrawal-date date_search @error('date_search') is-invalid @enderror" value="{{ isset($date_search) ? $date_search : '' }}" max="{{ date('Y-m-d'); }}">
                                <input type="text" name="keyword" id="keyword" class="form-control" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="Search" required>

                                <button class="btn-sm search-btn keyword-btn" type="submit">
                                    <i class="ti-search pl-3" aria-hidden="true"></i>
                                </button>

                                <a href="{{ route('admin.virtual-card.index') }}" class="btn-sm reload-btn">
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
                                <h5>Card Holder</h5>
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
                                        <th>User ID</th>
                                        <th>User Email</th>
                                        <th>CardHolder ID</th>
                                        <th>Card ID</th>
                                        <th>Status</th>
                                        <th>Date</th>

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
                                            <td>{{ $value->user_id ?? '' }}</td>
                                            <td>{{ $value->user->email ?? '' }}</td>
                                            <td>{{ $value->cardholder_id ?? '' }}</td>
                                            <td>
                                                @foreach($value->cardData as $userCard)
                                                    {{ $userCard->card_id ?? '-'}} 
                                                @endforeach
                                            </td>
                                            {{-- <td>{{ $value->cardData->card_id ?? '' }}</td> --}}
                                            <td>{{ $value->status ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d-M-Y') }}</td>
                                            {{--  <td class="text-center">
                                                <a href="{{ route('admin.tertiary-categories.show', $value->id) }}" class="btn btn-sm btn-icon p-1">
                                                    <i class="mdi mdi-eye mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="View"></i>
                                                </a>

                                                <a href="{{ route('admin.tertiary-categories.edit', $value->id) }}" class="btn btn-sm btn-icon p-1">
                                                    <i class="mdi mdi-pencil" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="Edit"></i>
                                                </a>
                                                <button type="submit" class="btn btn-sm btn-icon p-1 delete-record" route="{{ route('admin.tertiary-categories.destroy', $value->id) }}"><i class="mdi mdi-delete" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="Delete"></i></button>
                                            </td> --}}
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="5">No Data Found</td></tr>
                                @endif
                            </table>
                            @if ((request()->get('keyword')) || (request()->get('status')) || (request()->get('items')))
                                {{ $data->appends(['keyword' => request()->get('keyword'),'status' => request()->get('status'),'items' => request()->get('items')])->links() }}
                            @else
                                {{ $data->links() }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                     
               

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
