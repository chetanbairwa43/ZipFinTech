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
                <!-- <div class="row tabelhed d-flex justify-content-between">
                </div> -->

                <div class="card mt-5">
                    <div class="card-header ">
                        <div class="row">
                            <div class="col-xl-6 col-md-6 mt-auto">
                                <h5>Beneficiaries Users List</h5>
                            </div>
                             <div class="col-xl-6 col-md-6">
                                <div class="row float-end">
                                    <div class="col-xl-12 d-flex float-end">
                                        <div class="items paginatee">
                                            <form action="" method="GET">
                                            <select class="form-select m-0 items" name="items" id="items" aria-label="Default select example">
                                                <option value='10' {{request()->items == 10 ? 'selected="selected"' : ''}}>10</option>
                                                <option value='20' {{request()->items == 20 ? 'selected="selected"' : ''}}>20</option>
                                                <option value='30' {{request()->items == 30 ? 'selected="selected"' : ''}}>30</option>
                                                <option value='40' {{request()->items == 40 ? 'selected="selected"' : ''}}>40</option>
                                                <option value='50' {{request()->items == 50 ? 'selected="selected"' : ''}}>50</option>
                                            </select>


                                                @if(isset($_GET['role']))<input type="hidden" name="role" value="{{$_GET['role']}}">@endif
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
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
                                        <th>Account HolderName</th>
                                        <th>Business</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>

                                {{--@if(count($data)>0)
                                    @php
                                        isset($_GET['items']) ? $items = $_GET['items'] : $items = 10;
                                        isset($_GET['page']) ? $page = $_GET['page'] : $page = 1;

                                        $i = (($page-1)*$items)+1;
                                    @endphp--}}

                                    @foreach($beneficiaries as $key => $result)

                                  <tr data-entry-id="">
                                        <td>{{ $key + 1 }}</td> 
                                        <td>{{ $result['firstName'] }}</td>
                                        <td>{{ $result['lastName'] }}</td>
                                        <td>{{ $result['email'] }}</td>
                                        <td>{{ $result['phoneNumber'] ?? '' }}</td>
                                        <td>{{ $result['accountHolderName'] ?? '' }}</td>
                                        <td>{{ $result['business'] ?? '' }}</td>
                                        <td>{{ $result['type'] ?? '' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($result['createdAt'])->format('d-M-Y') }}</td>
                                    </tr> 
                                    @endforeach
                              {{--  @else
                                    <tr><td colspan="8">No Data Found</td></tr>
                                @endif--}}
                            </table>
                    

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
