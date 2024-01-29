@extends('layouts.master')
@section('content')

<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">

        @if(Session::has('success'))
        @section('scripts')
        <script>
        swal("Good job!", "{{ Session::get('success') }}", "success");
        </script>
        @endsection
        @endif

        @if(Session::has('error'))
        @section('scripts')
        <script>
        swal("Oops...", "{{ Session::get('error') }}", "error");
        </script>
        @endsection
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="row tabelhed d-flex justify-content-between">
                    <div class="col-lg-2 col-md-2 col-sm-2 d-flex">
                        <!-- <a class="ad-btn btn text-center" href="{{ route('admin.pages.create') }}"> Add</a> -->
                    </div>

                    <div class="col-lg-10 col-md-10">

                        <div class="right-item d-flex justify-content-end">
                            <form action="" method="GET" class="d-flex">
                                {{--<select class="form-control mx-1" id="status" name="type">
                                    <option value="">Select Type</option>
                                    <option value="cr" {{ (request()->get('type') == "cr" ? 'selected' : '' ) }}>Credit
                                    </option>
                                    <option value="dr" {{ (request()->get('type') == "dr" ? 'selected' : '' ) }}>Debit
                                    </option>
                                </select>--}}
                                <input type="text" name="keyword" id="keyword" class="form-control"
                                    value="{{ isset($keyword) ? $keyword : '' }}" placeholder="Search" required>

                                <button class="btn-sm search-btn keyword-btn" type="submit">
                                    <i class="ti-search pl-3" aria-hidden="true"></i>
                                </button>

                                <a href="{{ route('admin.customer-balance') }}" class="btn-sm reload-btn">
                                    <i class="ti-reload pl-3 redirect-icon" aria-hidden="true"></i>
                                </a>

                                @if(isset($_GET['items']))<input type="hidden" name="items"
                                    value="{{$_GET['items']}}">@endif
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header ">
                        <div class="row">
                            <div class="col-xl-6 col-md-6 mt-auto">
                                <h5>Customer Balance</h5>
                            </div>
                            <div class="col-xl-6 col-md-6">
                                <div class="row float-end">
                                    <div class="col-xl-12 d-flex float-end">
                                        <div class="items paginatee">
                                            <form action="" method="GET">
                                                <select class="form-select m-0 items" name="items" id="items"
                                                    aria-label="Default select example">
                                                    <option value='10'
                                                        {{ isset($items) ? ($items == '10' ? 'selected' : '' ) : '' }}>
                                                        10</option>
                                                    <option value='20'
                                                        {{ isset($items) ? ($items == '20' ? 'selected' : '' ) : '' }}>
                                                        20</option>
                                                    <option value='30'
                                                        {{ isset($items) ? ($items == '30' ? 'selected' : '' ) : '' }}>
                                                        30</option>
                                                    <option value='40'
                                                        {{ isset($items) ? ($items == '40' ? 'selected' : '' ) : '' }}>
                                                        40</option>
                                                    <option value='50'
                                                        {{ isset($items) ? ($items == '50' ? 'selected' : '' ) : '' }}>
                                                        50</option>
                                                </select>
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
                                        <th>Name </th>
                                        <th>Available Balance</th>
                                        <th>Last transaction</th>
                                        <th>Last tran. date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                @if(count($data)>0)
                                @php
                                isset($_GET['items']) ? $items = $_GET['items'] : $items = 10;
                                isset($_GET['page']) ? $page = $_GET['page'] : $page = 1;

                                $i = (($page-1)*$items)+1;
                                @endphp

                                @foreach($data as $value)
                                <tr data-entry-id="{{ $value->id }}">
                                    <td>{{ $i++}}</td>
                                    <td>{{ $value->fname.' '.$value->lname ?? '-'}}</td>
                                    <td>{{ $value->available_amount ?? 0 }}</td>
                                    {{-- <td>{{ $data->available_amount ?? 0 }}</td> --}}
                                    <td>{{ optional($value->last_transaction)->amount.' '. optional($value->last_transaction)->transaction_type }}</td>
                                    <td>{{ !empty($value->last_transaction)?\Carbon\Carbon::parse($value->last_transaction->created_at)->format('d F, Y'): '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.single-user-transaction', $value->id) }}"
                                            class="btn btn-sm btn-icon p-1">
                                            <i class="mdi mdi-eye mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4"
                                                data-bs-placement="top" title="View"></i>
                                        </a>
                                        <!-- <td class="text-center"> -->
                                       <a href="{{ route('admin.add-balance', $value->id) }}" class="btn btn-sm btn-primary ">Add Balance</a>

                                                <!-- </td> -->
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-center text-danger">No Data Found</td>
                                </tr>
                                @endif
                            </table>
                            @if ((request()->get('keyword')) || (request()->get('items')))
                            {{ $data->appends(['keyword' => request()->get('keyword'),'items' => request()->get('items')])->links() }}
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