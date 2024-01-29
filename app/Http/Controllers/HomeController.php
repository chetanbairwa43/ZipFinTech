<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Transaction;
use App\Models\VirtualAccounts;
use Carbon\Carbon;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard()
    {
        if (Auth::user()->roles->contains('5')) {
            return redirect()->route('admin.users.index');
        }
        // ** Daily ** //
        $d['recentTransaction'] = Transaction::whereDate('created_at', Carbon::today())->latest()->take(10)->get();
        $d['zip2zip'] = Transaction::whereDate('created_at', Carbon::today())->where('user_type', 'ziptozip')->latest()->take(5)->get();
        $d['zip2other'] = Transaction::whereDate('created_at', Carbon::today())->where('user_type', 'otherusers')->latest()->take(5)->get();
        $d['card_count'] = VirtualAccounts::whereDate('created_at', Carbon::today())->whereDate('created_at', Carbon::today())->count();

        $trtype = Transaction::whereDate('created_at', Carbon::today())->selectRaw('
            SUM(CASE WHEN transaction_type = "cr" THEN amount ELSE 0 END) AS totalcr,
            SUM(CASE WHEN transaction_type = "dr" THEN amount ELSE 0 END) AS totaldr
        ')
            ->first();
        $d['totalcr'] = $trtype->totalcr;
        $d['totaldr'] = $trtype->totaldr;
        $d['user_count'] = User::whereDate('created_at', Carbon::today())->whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->count();
        // ** Weekly ** //
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();
        $d['weeklyData_recentTransaction'] = Transaction::whereBetween('created_at', [$startDate, $endDate])->latest()->take(10)->get();
        $d['weeklyData_zip2zip'] = Transaction::whereBetween('created_at', [$startDate, $endDate])->where('user_type', 'ziptozip')->latest()->take(5)->get();
        $d['weeklyData_zip2other'] = Transaction::whereBetween('created_at', [$startDate, $endDate])->where('user_type', 'otherusers')->latest()->take(5)->get();
        $d['weeklyData_card_count'] = VirtualAccounts::whereBetween('created_at', [$startDate, $endDate])->count();
        $trtype = Transaction::whereBetween('created_at', [$startDate, $endDate])->selectRaw('
        SUM(CASE WHEN transaction_type = "cr" THEN amount ELSE 0 END) AS totalcr,
        SUM(CASE WHEN transaction_type = "dr" THEN amount ELSE 0 END) AS totaldr
    ')
            ->first();
        $d['weeklyData_totalcr'] = $trtype->totalcr;
        $d['weeklyData_totaldr'] = $trtype->totaldr;
        $d['weeklyData_user_count'] = User::whereBetween('created_at', [$startDate, $endDate])->whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->count();

        // ** Monthly ** //


        $d['monthlyData_recentTransaction'] = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->latest()->take(10)->get();

        $d['monthlyData_zip2zip'] = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->where('user_type', 'ziptozip')->latest()->take(5)->get();

        $d['monthlyData_zip2other'] = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->where('user_type', 'otherusers')->latest()->take(5)->get();
        $d['monthlyData_card_count'] = VirtualAccounts::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
        $trtype = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->selectRaw('
         SUM(CASE WHEN transaction_type = "cr" THEN amount ELSE 0 END) AS totalcr,
         SUM(CASE WHEN transaction_type = "dr" THEN amount ELSE 0 END) AS totaldr
     ')
            ->first();
        $d['monthlyData_totalcr'] = $trtype->totalcr;
        $d['monthlyData_totaldr'] = $trtype->totaldr;
        $d['monthlyData_user_count'] = User::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->whereHas('roles', function ($q) {
                $q->where('role_id', 2);
            })->count();
        // ** Quarterly  ** //
        $quarter = Carbon::now()->quarter;
        $year = Carbon::now()->year;

        switch ($quarter) {
            case 1:
                $startDate = Carbon::create($year, 1, 1);
                $endDate = Carbon::create($year, 3, 31);
                break;
            case 2:
                $startDate = Carbon::create($year, 4, 1);
                $endDate = Carbon::create($year, 6, 30);
                break;
            case 3:
                $startDate = Carbon::create($year, 7, 1);
                $endDate = Carbon::create($year, 9, 30);
                break;
            case 4:
                $startDate = Carbon::create($year, 10, 1);
                $endDate = Carbon::create($year, 12, 31);
                break;
        }


        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();
        $d['quarterData_recentTransaction'] = Transaction::whereBetween('created_at', [$startDate, $endDate])->latest()->take(10)->get();

        $d['quarterData_zip2zip'] = Transaction::whereBetween('created_at', [$startDate, $endDate])->where('user_type', 'ziptozip')->latest()->take(5)->get();

        $d['quarterData_zip2other'] = Transaction::whereBetween('created_at', [$startDate, $endDate])->where('user_type', 'otherusers')->latest()->take(5)->get();
        $d['quarterData_card_count'] = VirtualAccounts::whereBetween('created_at', [$startDate, $endDate])->count();
        $trtype = Transaction::whereBetween('created_at', [$startDate, $endDate])->selectRaw('
         SUM(CASE WHEN transaction_type = "cr" THEN amount ELSE 0 END) AS totalcr,
        SUM(CASE WHEN transaction_type = "dr" THEN amount ELSE 0 END) AS totaldr')
            ->first();
        $d['quarterData_totalcr'] = $trtype->totalcr;
        $d['quarterData_totaldr'] = $trtype->totaldr;
        $d['quarterData_user_count'] = User::whereBetween('created_at', [$startDate, $endDate])->whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->count();
        // ** Yearly  ** //
        $d['yearlyData_recentTransaction'] = Transaction::whereYear('created_at', Carbon::now()->year)->latest()->take(10)->get();
        $d['yearlyData_zip2zip'] = Transaction::whereYear('created_at', Carbon::now()->year)->where('user_type', 'ziptozip')->latest()->take(5)->get();
        $d['yearlyData_zip2other'] = Transaction::whereYear('created_at', Carbon::now()->year)->where('user_type', 'otherusers')->latest()->take(5)->get();
        $d['yearlyData_card_count'] = VirtualAccounts::whereYear('created_at', Carbon::now()->year)->count();
        $trtype = Transaction::whereYear('created_at', Carbon::now()->year)->selectRaw('
          SUM(CASE WHEN transaction_type = "cr" THEN amount ELSE 0 END) AS totalcr,
          SUM(CASE WHEN transaction_type = "dr" THEN amount ELSE 0 END) AS totaldr
      ')
            ->first();
        $d['yearlyData_totalcr'] = $trtype->totalcr;
        $d['yearlyData_totaldr'] = $trtype->totaldr;
        $d['yearlyData_user_count'] = User::whereYear('created_at', Carbon::now()->year)->whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->count();

        // $d['card_count']   = VirtualAccounts::count();


        return view('admin.index', $d);
    }

}
