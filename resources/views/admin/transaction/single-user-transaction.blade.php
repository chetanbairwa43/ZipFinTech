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
                    <div class="col-lg-8 col-md-8 col-sm-8 d-flex flex-column">
                        <div>
                    <h3><span class="text-danger">Name :</span> <span class="text-success">{{ucfirst($user->fname) ?? 'N?A'}}</span></h3> </div> <div><h3><span class="text-danger">Total Balance :</span> <span class="text-success"> {{$user->available_amount ?? 0}}</span></h3></div>
                    </div>
                    <div class="col-lg-4 col-md-4">

                        <div class="right-item d-flex justify-content-end">
                            <form action="" method="GET" class="d-flex">
                                <select class="form-control mx-1" id="status" name="type">
                                    <option value="">Select Type</option>
                                    <option value="cr" {{ (request()->get('type') == "cr" ? 'selected' : '' ) }}>Credit
                                    </option>
                                    <option value="dr" {{ (request()->get('type') == "dr" ? 'selected' : '' ) }}>Debit
                                    </option>
                                </select>
                                {{--<input type="text" name="keyword" id="keyword" class="form-control"
                                    value="{{ isset($keyword) ? $keyword : '' }}" placeholder="Search" required>

                                <button class="btn-sm search-btn keyword-btn" type="submit">
                                    <i class="ti-search pl-3" aria-hidden="true"></i>
                                </button>--}}

                                <a href="{{route('admin.single-user-transaction',request()->id)}}" class="btn-sm reload-btn">
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
                                <h5>Single user transactions</h5>
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
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>About</th>
                                        <th>Transaction ID</th>
                                        <th>Date</th>
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
                                    <td>{{ $value->amount ?? '' }}</td>
                                    <td class="{{($value->transaction_type == 'cr')?'text-success':'text-danger'}}">
                                        {{ ($value->transaction_type == 'cr')? 'Credit':'Debit' }}</td>
                                    <td>{{ ucfirst($value->transaction_about) ?? "-" }}</td>
                                    <td>{{ $value->t_id}}</td>
                                    <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d F, Y') ?? '' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.transactions.show', $value->id) }}"
                                            class="btn btn-sm btn-icon p-1">
                                            <i class="mdi mdi-eye mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4"
                                                data-bs-placement="top" title="View"></i>
                                        </a>

                                        {{-- <button type="submit" class="btn btn-sm btn-icon p-1 delete-record"
                                            route="{{ route('admin.tDelete', $value->id) }}"><i class="mdi mdi-delete"
                                                data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top"
                                                data-bs-html="true" title="Delete"></i></button> --}}
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
