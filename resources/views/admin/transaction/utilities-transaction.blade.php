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

                                <input type="text" name="keyword" id="keyword" class="form-control" value="{{ request()->keyword }}" placeholder="Search" required>

                                <button class="btn-sm search-btn keyword-btn" type="submit">
                                    <i class="ti-search pl-3" aria-hidden="true"></i>
                                </button>

                                <a href="{{ url()->current() }}" class="btn-sm reload-btn">
                                    <i class="ti-reload pl-3 redirect-icon" aria-hidden="true"></i>
                                </a>

                                @if(isset($_GET['items']))<input type="hidden" name="items" value="{{$_GET['items']}}">@endif
                            </form>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" onclick="addURL(this)" data-href="ebill" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Electricity bill</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-href="bpay" onclick="addURL(this)" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Bill pay</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-href="bphone" onclick="addURL(this)" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Buy phone</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-href="binternet" onclick="addURL(this)" id="internet-tab" data-bs-toggle="tab" data-bs-target="#internet" type="button" role="tab" aria-controls="internet" aria-selected="false">Buy internet</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        {{--Home--}}
                        <div class="card">
                            <div class="card-header ">
                                <div class="row">
                                    <div class="col-xl-6 col-md-6 mt-auto">
                                        <h5>Electricity bill transaction list</h5>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                        <div class="row float-end">
                                            <div class="col-xl-12 d-flex float-end">
                                                <div class="items paginatee">
                                                    <form action="" method="GET">
                                                        <select class="form-select m-0 items" name="items" id="items" aria-label="Default select example">
                                                            <option value='10' {{ isset($items) ? ($items == '10' ? 'selected' : '' ) : '' }}>
                                                                10</option>
                                                            <option value='20' {{ isset($items) ? ($items == '20' ? 'selected' : '' ) : '' }}>
                                                                20</option>
                                                            <option value='30' {{ isset($items) ? ($items == '30' ? 'selected' : '' ) : '' }}>
                                                                30</option>
                                                            <option value='40' {{ isset($items) ? ($items == '40' ? 'selected' : '' ) : '' }}>
                                                                40</option>
                                                            <option value='50' {{ isset($items) ? ($items == '50' ? 'selected' : '' ) : '' }}>
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
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>About</th>
                                                <th>Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>

                                        @if(count($ebill)>0)
                                        @php
                                        isset($_GET['items']) ? $items = $_GET['items'] : $items = 10;
                                        isset($_GET['page']) ? $page = $_GET['page'] : $page = 1;

                                        $i = (($page-1)*$items)+1;
                                        @endphp

                                        @foreach($ebill as $key => $value)
                                        <tr data-entry-id="{{ $value->id }}">
                                            <td>{{ $i++ ?? ''}}</td>
                                            <td>{{ $value->user->fname ?? ''}} {{$value->user->lname ?? ''}}</td>
                                            <td>{{ $value->amount ?? '' }}</td>
                                            <td class="{{($value->transaction_type == 'cr')?'text-success':'text-danger'}}">
                                                {{ ($value->transaction_type == 'cr')? 'Credit':'Debit' }}
                                            </td>
                                            <td>{{ ucfirst($value->transaction_about) ?? "-" }}</td>
                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d F, Y') ?? '' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.transactions.show', $value->id) }}" class="btn btn-sm btn-icon p-1">
                                                    <i class="mdi mdi-eye mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="View"></i>
                                                </a>

                                                {{-- <button type="submit" class="btn btn-sm btn-icon p-1 delete-record" route="{{ route('admin.tDelete', $value->id) }}"><i class="mdi mdi-delete" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="Delete"></i></button> --}}
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="7" class="text-center text-danger">No Data Found</td>
                                        </tr>
                                        @endif
                                    </table>

                                    {{ $ebill->links() }}
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        {{--Profile--}}
                        <div class="card">
                            <div class="card-header ">
                                <div class="row">
                                    <div class="col-xl-6 col-md-6 mt-auto">
                                        <h5>Bill pay transaction list</h5>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                        <div class="row float-end">
                                            <div class="col-xl-12 d-flex float-end">
                                                <div class="items paginatee">
                                                    <form action="" method="GET">
                                                        <select class="form-select m-0 items" name="items" id="items" aria-label="Default select example">
                                                            <option value='10' {{ isset($items) ? ($items == '10' ? 'selected' : '' ) : '' }}>
                                                                10</option>
                                                            <option value='20' {{ isset($items) ? ($items == '20' ? 'selected' : '' ) : '' }}>
                                                                20</option>
                                                            <option value='30' {{ isset($items) ? ($items == '30' ? 'selected' : '' ) : '' }}>
                                                                30</option>
                                                            <option value='40' {{ isset($items) ? ($items == '40' ? 'selected' : '' ) : '' }}>
                                                                40</option>
                                                            <option value='50' {{ isset($items) ? ($items == '50' ? 'selected' : '' ) : '' }}>
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
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>About</th>
                                                <th>Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>

                                        @if(count($bill_pay)>0)
                                        @php
                                        isset($_GET['items']) ? $items = $_GET['items'] : $items = 10;
                                        isset($_GET['page']) ? $page = $_GET['page'] : $page = 1;

                                        $i = (($page-1)*$items)+1;
                                        @endphp

                                        @foreach($bill_pay as $key => $value)
                                        <tr data-entry-id="{{ $value->id }}">
                                            <td>{{ $i++ ?? ''}}</td>
                                            <td>{{ $value->user->fname ?? ''}} {{$value->user->lname ?? ''}}</td>
                                            <td>{{ $value->amount ?? '' }}</td>
                                            <td class="{{($value->transaction_type == 'cr')?'text-success':'text-danger'}}">
                                                {{ ($value->transaction_type == 'cr')? 'Credit':'Debit' }}
                                            </td>
                                            <td>{{ ucfirst($value->transaction_about) ?? "-" }}</td>
                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d F, Y') ?? '' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.transactions.show', $value->id) }}" class="btn btn-sm btn-icon p-1">
                                                    <i class="mdi mdi-eye mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="View"></i>
                                                </a>

                                                <button type="submit" class="btn btn-sm btn-icon p-1 delete-record" route="{{ route('admin.tDelete', $value->id) }}"><i class="mdi mdi-delete" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="Delete"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="7" class="text-center text-danger">No Data Found</td>
                                        </tr>
                                        @endif
                                    </table>

                                    {{ $bill_pay->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        {{--Contact--}}
                        <div class="card">
                            <div class="card-header ">
                                <div class="row">
                                    <div class="col-xl-6 col-md-6 mt-auto">
                                        <h5>Buy phone transaction list</h5>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                        <div class="row float-end">
                                            <div class="col-xl-12 d-flex float-end">
                                                <div class="items paginatee">
                                                    <form action="" method="GET">
                                                        <select class="form-select m-0 items" name="items" id="items" aria-label="Default select example">
                                                            <option value='10' {{ isset($items) ? ($items == '10' ? 'selected' : '' ) : '' }}>
                                                                10</option>
                                                            <option value='20' {{ isset($items) ? ($items == '20' ? 'selected' : '' ) : '' }}>
                                                                20</option>
                                                            <option value='30' {{ isset($items) ? ($items == '30' ? 'selected' : '' ) : '' }}>
                                                                30</option>
                                                            <option value='40' {{ isset($items) ? ($items == '40' ? 'selected' : '' ) : '' }}>
                                                                40</option>
                                                            <option value='50' {{ isset($items) ? ($items == '50' ? 'selected' : '' ) : '' }}>
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
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>About</th>
                                                <th>Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>

                                        @if(count($buy_phon)>0)
                                        @php
                                        isset($_GET['items']) ? $items = $_GET['items'] : $items = 10;
                                        isset($_GET['page']) ? $page = $_GET['page'] : $page = 1;

                                        $i = (($page-1)*$items)+1;
                                        @endphp

                                        @foreach($buy_phon as $key => $value)
                                        <tr data-entry-id="{{ $value->id }}">
                                            <td>{{ $i++ ?? ''}}</td>
                                            <td>{{ $value->user->fname ?? ''}} {{$value->user->lname ?? ''}}</td>
                                            <td>{{ $value->amount ?? '' }}</td>
                                            <td class="{{($value->transaction_type == 'cr')?'text-success':'text-danger'}}">
                                                {{ ($value->transaction_type == 'cr')? 'Credit':'Debit' }}
                                            </td>
                                            <td>{{ ucfirst($value->transaction_about) ?? "-" }}</td>
                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d F, Y') ?? '' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.transactions.show', $value->id) }}" class="btn btn-sm btn-icon p-1">
                                                    <i class="mdi mdi-eye mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="View"></i>
                                                </a>

                                                <button type="submit" class="btn btn-sm btn-icon p-1 delete-record" route="{{ route('admin.tDelete', $value->id) }}"><i class="mdi mdi-delete" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="Delete"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="7" class="text-center text-danger">No Data Found</td>
                                        </tr>
                                        @endif
                                    </table>

                                    {{ $buy_phon->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="internet" role="tabpanel" aria-labelledby="internet-tab">
                        {{--internet--}}
                        <div class="card">
                            <div class="card-header ">
                                <div class="row">
                                    <div class="col-xl-6 col-md-6 mt-auto">
                                        <h5>Buy internet transaction list</h5>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                        <div class="row float-end">
                                            <div class="col-xl-12 d-flex float-end">
                                                <div class="items paginatee">
                                                    <form action="" method="GET">
                                                        <select class="form-select m-0 items" name="items" id="items" aria-label="Default select example">
                                                            <option value='10' {{ isset($items) ? ($items == '10' ? 'selected' : '' ) : '' }}>
                                                                10</option>
                                                            <option value='20' {{ isset($items) ? ($items == '20' ? 'selected' : '' ) : '' }}>
                                                                20</option>
                                                            <option value='30' {{ isset($items) ? ($items == '30' ? 'selected' : '' ) : '' }}>
                                                                30</option>
                                                            <option value='40' {{ isset($items) ? ($items == '40' ? 'selected' : '' ) : '' }}>
                                                                40</option>
                                                            <option value='50' {{ isset($items) ? ($items == '50' ? 'selected' : '' ) : '' }}>
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
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>About</th>
                                                <th>Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>

                                        @if(count($buy_inter)>0)
                                        @php
                                        isset($_GET['items']) ? $items = $_GET['items'] : $items = 10;
                                        isset($_GET['page']) ? $page = $_GET['page'] : $page = 1;

                                        $i = (($page-1)*$items)+1;
                                        @endphp

                                        @foreach($buy_inter as $key => $value)
                                        <tr data-entry-id="{{ $value->id }}">
                                            <td>{{ $i++ ?? ''}}</td>
                                            <td>{{ $value->user->fname ?? ''}} {{$value->user->lname ?? ''}}</td>
                                            <td>{{ $value->amount ?? '' }}</td>
                                            <td class="{{($value->transaction_type == 'cr')?'text-success':'text-danger'}}">
                                                {{ ($value->transaction_type == 'cr')? 'Credit':'Debit' }}
                                            </td>
                                            <td>{{ ucfirst($value->transaction_about) ?? "-" }}</td>
                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d F, Y') ?? '' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.transactions.show', $value->id) }}" class="btn btn-sm btn-icon p-1">
                                                    <i class="mdi mdi-eye mx-1" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" title="View"></i>
                                                </a>

                                                <button type="submit" class="btn btn-sm btn-icon p-1 delete-record" route="{{ route('admin.tDelete', $value->id) }}"><i class="mdi mdi-delete" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="Delete"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="7" class="text-center text-danger">No Data Found</td>
                                        </tr>
                                        @endif
                                    </table>
                                    {{ $buy_inter->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

@endsection
