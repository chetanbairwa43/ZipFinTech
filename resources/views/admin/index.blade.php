@extends('layouts.admin')
@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="home-tab">
                {{-- @php
                    use Illuminate\Support\Facades\Mail;
                    use App\Mail\NewSignUp;
                    use Illuminate\Support\Facades\Log;
                
                    try {
                        $config = [
                            'from_email' => env('MAIL_FROM_ADDRESS'),
                            'name' => env('MAIL_FROM_NAME'),
                            'subject' => 'testing',
                            'message' => 'message',
                        ];
                
                        // Attempt to send email
                        Mail::to('eoxys.mobile.api@gmail.com')->send(new NewSignUp($config));
                
                        // Check if there were any failures
                        if (Mail::failures()) {
                            foreach (Mail::failures() as $failure) {
                                Log::error('Failed to send email: ' . $failure->getMessage());
                            }
                        } else {
                            // Log success message
                            Log::info('Email sent successfully');
                        }
                    } catch (\Exception $e) {
                        // Log error message
                        Log::error('Email sending failed: ' . $e->getMessage());
                    }
                @endphp --}}
            
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" onclick="addURL(this)" data-href="ebill" id="home-tab"
                            data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home"
                            aria-selected="true">Daily</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" onclick="addURL(this)" data-href="ebill" id="home-tab"
                            data-bs-toggle="tab" data-bs-target="#daily" type="button" role="tab" aria-controls="daily"
                            aria-selected="false"> Weekly</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-href="bpay" onclick="addURL(this)" id="profile-tab"
                            data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab"
                            aria-controls="profile" aria-selected="false">Monthly</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-href="bphone" onclick="addURL(this)" id="contact-tab"
                            data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab"
                            aria-controls="contact" aria-selected="false">Quarterly </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-href="binternet" onclick="addURL(this)" id="internet-tab"
                            data-bs-toggle="tab" data-bs-target="#internet" type="button" role="tab"
                            aria-controls="internet" aria-selected="false">Yearly </button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row my-4">
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-1">

                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-1 rounded">
                                                    <i class="mdi mdi-card-bulleted font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Virtual Cards</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $card_count ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-2">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-2 rounded">
                                                    <i class="mdi mdi-account-multiple font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">User Created</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{$user_count ?? 0}}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-3">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-3 rounded">
                                                    <i class="mdi mdi-cash font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Incoming</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $totalcr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-4">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-4 rounded">
                                                    <i class="mdi mdi-checkbox-marked-circle-outline font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Outgoing</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $totaldr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row grid-margin">
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to other</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($zip2other as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to Zip</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($zip2zip as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card card-rounded">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h4 class="card-title card-title-dash text-center text-info">
                                                            Recent Transaction Summary</h4>
                                                    </div>
                                                    <hr>
                                                    <div class="">
                                                        <table class="table table-borderless recent_customers_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>User Name</th>
                                                                    <th> Amount</th>
                                                                    <th> CR/DR</th>
                                                                    <th>Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($recentTransaction as $value)
                                                                <tr>
                                                                    <td>{{ $value->user->fname ?? '' }}
                                                                        {{ $value->user->lname ?? '' }}</td>
                                                                    <td> {{$value->amount ?? ''}}</td>
                                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}
                                                                    </td>
                                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}
                                                                    </td>
                                                                    <td class="detail_btn"><a
                                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                                    </td>
                                                                </tr>
                                                                @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-danger">No
                                                                        data
                                                                        found</td>
                                                                </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>

                                                    </div>
                                                    <div class="mt-3">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane fade show" id="daily" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row my-4">
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-1">

                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-1 rounded">
                                                    <i class="mdi mdi-card-bulleted font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Virtual Cards</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $weeklyData_card_count ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-2">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-2 rounded">
                                                    <i class="mdi mdi-account-multiple font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">User Created</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{$weeklyData_user_count ?? 0}}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-3">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-3 rounded">
                                                    <i class="mdi mdi-cash font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Incoming</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $weeklyData_totalcr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-4">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-4 rounded">
                                                    <i class="mdi mdi-checkbox-marked-circle-outline font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Outgoing</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $weeklyData_totaldr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row grid-margin">
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to other</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($weeklyData_zip2other as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to Zip</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($weeklyData_zip2zip as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card card-rounded">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h4 class="card-title card-title-dash text-center text-info">
                                                            Recent Transaction Summary</h4>
                                                    </div>
                                                    <hr>
                                                    <div class="">
                                                        <table class="table table-borderless recent_customers_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>User Name</th>
                                                                    <th> Amount</th>
                                                                    <th> CR/DR</th>
                                                                    <th>Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($weeklyData_recentTransaction as $value)
                                                                <tr>
                                                                    <td>{{ $value->user->fname ?? '' }}
                                                                        {{ $value->user->lname ?? '' }}</td>
                                                                    <td> {{$value->amount ?? ''}}</td>
                                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}
                                                                    </td>
                                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}
                                                                    </td>
                                                                    <td class="detail_btn"><a
                                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                                    </td>
                                                                </tr>
                                                                @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-danger">No
                                                                        data
                                                                        found</td>
                                                                </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>

                                                    </div>
                                                    <div class="mt-3">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane fade show" id="profile" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row my-4">
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-1">

                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-1 rounded">
                                                    <i class="mdi mdi-card-bulleted font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Virtual Cards</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $monthlyData_card_count ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-2">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-2 rounded">
                                                    <i class="mdi mdi-account-multiple font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">User Created</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{$monthlyData_user_count ?? 0}}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-3">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-3 rounded">
                                                    <i class="mdi mdi-cash font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Incoming</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $monthlyData_totalcr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-4">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-4 rounded">
                                                    <i class="mdi mdi-checkbox-marked-circle-outline font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Outgoing</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $monthlyData_totaldr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row grid-margin">
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to other</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($monthlyData_zip2other as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to Zip</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($monthlyData_zip2zip as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card card-rounded">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h4 class="card-title card-title-dash text-center text-info">
                                                            Recent Transaction Summary</h4>
                                                    </div>
                                                    <hr>
                                                    <div class="">
                                                        <table class="table table-borderless recent_customers_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>User Name</th>
                                                                    <th> Amount</th>
                                                                    <th> CR/DR</th>
                                                                    <th>Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($monthlyData_recentTransaction as $value)
                                                                <tr>
                                                                    <td>{{ $value->user->fname ?? '' }}
                                                                        {{ $value->user->lname ?? '' }}</td>
                                                                    <td> {{$value->amount ?? ''}}</td>
                                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}
                                                                    </td>
                                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}
                                                                    </td>
                                                                    <td class="detail_btn"><a
                                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                                    </td>
                                                                </tr>
                                                                @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-danger">No
                                                                        data
                                                                        found</td>
                                                                </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>

                                                    </div>
                                                    <div class="mt-3">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane fade show" id="contact" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row my-4">
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-1">

                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-1 rounded">
                                                    <i class="mdi mdi-card-bulleted font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Virtual Cards</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $quarterData_card_count ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-2">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-2 rounded">
                                                    <i class="mdi mdi-account-multiple font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">User Created</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{$quarterData_user_count ?? 0}}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-3">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-3 rounded">
                                                    <i class="mdi mdi-cash font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Incoming</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $quarterData_totalcr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-4">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-4 rounded">
                                                    <i class="mdi mdi-checkbox-marked-circle-outline font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Outgoing</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $quarterData_totaldr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row grid-margin">
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to other</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($quarterData_zip2other as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to Zip</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($quarterData_zip2zip as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card card-rounded">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h4 class="card-title card-title-dash text-center text-info">
                                                            Recent Transaction Summary</h4>
                                                    </div>
                                                    <hr>
                                                    <div class="">
                                                        <table class="table table-borderless recent_customers_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>User Name</th>
                                                                    <th> Amount</th>
                                                                    <th> CR/DR</th>
                                                                    <th>Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($quarterData_recentTransaction as $value)
                                                                <tr>
                                                                    <td>{{ $value->user->fname ?? '' }}
                                                                        {{ $value->user->lname ?? '' }}</td>
                                                                    <td> {{$value->amount ?? ''}}</td>
                                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}
                                                                    </td>
                                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}
                                                                    </td>
                                                                    <td class="detail_btn"><a
                                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                                    </td>
                                                                </tr>
                                                                @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-danger">No
                                                                        data
                                                                        found</td>
                                                                </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>

                                                    </div>
                                                    <div class="mt-3">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane fade show" id="internet" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row my-4">
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-1">

                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-1 rounded">
                                                    <i class="mdi mdi-card-bulleted font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Virtual Cards</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $yearlyData_card_count ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-2">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-2 rounded">
                                                    <i class="mdi mdi-account-multiple font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">User Created</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{$yearlyData_user_count ?? 0}}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-3">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-3 rounded">
                                                    <i class="mdi mdi-cash font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Incoming</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $yearlyData_totalcr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 grid-margin stretch-card">
                                <div class="card rounded-1">
                                    <div class="card-body card-4">
                                        <div class="d-flex">
                                            <div class="avatar">
                                                <span class="avatar-title bg-soft-card-4 rounded">
                                                    <i class="mdi mdi-checkbox-marked-circle-outline font-size-24"></i>
                                                </span>
                                            </div>
                                            <div class="ms-4">
                                                <p class="text-muted mb-0">Total Outgoing</p>
                                                <h4 class="mt-1 mb-0">
                                                    {{ $yearlyData_totaldr ?? 0 }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row grid-margin">
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to other</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($yearlyData_zip2other as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="card ">
                                    <div class="card-body ">
                                        <h4 class="text-center text-info">Recent Zip to Zip</h4>
                                        <hr>
                                        <table class="table table-borderless recent_customers_table">
                                            <thead>
                                                <tr>
                                                    <th>User Name</th>
                                                    <th> Amount</th>
                                                    <th> CR/DR</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($yearlyData_zip2zip as $value)
                                                <tr>
                                                    <td>{{ $value->user->fname ?? '' }} {{ $value->user->lname ?? '-' }}
                                                    </td>
                                                    <td> {{$value->amount ?? ''}}</td>
                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}</td>
                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}</td>
                                                    <td class="detail_btn"><a
                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">No data
                                                        found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card card-rounded">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h4 class="card-title card-title-dash text-center text-info">
                                                            Recent Transaction Summary</h4>
                                                    </div>
                                                    <hr>
                                                    <div class="">
                                                        <table class="table table-borderless recent_customers_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>User Name</th>
                                                                    <th> Amount</th>
                                                                    <th> CR/DR</th>
                                                                    <th>Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($yearlyData_recentTransaction as $value)
                                                                <tr>
                                                                    <td>{{ $value->user->fname ?? '' }}
                                                                        {{ $value->user->lname ?? '' }}</td>
                                                                    <td> {{$value->amount ?? ''}}</td>
                                                                    <td> {{($value->transaction_type == "cr") ?'Credit':'Debit'}}
                                                                    </td>
                                                                    <td>{{ date('Y-m-d', strtotime($value->created_at)) }}
                                                                    </td>
                                                                    <td class="detail_btn"><a
                                                                            href="{{ route('admin.transactions.show', $value->id ?? '') }}"
                                                                            class="vier_order_btn ad-btn btn btn-sm">Details</a>
                                                                    </td>
                                                                </tr>
                                                                @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-danger">No
                                                                        data
                                                                        found</td>
                                                                </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>

                                                    </div>
                                                    <div class="mt-3">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    @endsection
    @section('js')

    @endsection