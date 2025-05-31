<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MainController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
// Models
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\Trip;
use App\Models\User\User;

class DashboardController extends MainController
{
    public function getDashboardInfo(Request $request)
    {
        // === Handle Date Filters ===
        $filter = $request->query('filter');
        $from   = $request->query('from');
        $to     = $request->query('to');

        $startDate = null;
        $endDate   = null;

        switch ($filter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate   = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday();
                $endDate   = Carbon::yesterday()->endOfDay();
                break;
            case 'thisWeek':
                $startDate = Carbon::now()->startOfWeek();
                $endDate   = Carbon::now()->endOfWeek();
                break;
            case 'thisMonth':
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();
                break;
            case 'threeMonthAgo':
                $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();
                break;
            case 'sixMonthAgo':
                $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();
                break;
            default:
                if ($from && $to) {
                    $startDate = Carbon::parse($from)->startOfDay();
                    $endDate   = Carbon::parse($to)->endOfDay();
                }
                break;
        }

        // === Basic Statistics (No Date Filter) ===
        $totalBookings  = Booking::count();
        $totalCities    = City::count();
        $totalCountries = Country::count();
        $totalTrips     = Trip::count();
        $totalUsers     = User::count();
        $totalRevenue   = DB::table('booking_details')->sum('price');

        // === Revenue Change (Today vs Yesterday) ===
        $todayRevenue = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->whereDate('bookings.created_at', today())
            ->sum('booking_details.price');

        $yesterdayRevenue = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->whereDate('bookings.created_at', today()->subDay())
            ->sum('booking_details.price');

        $totalPercentageIncrease = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 2)
            : 0;
        $saleIncreasePreviousDay = ($totalPercentageIncrease > 0 ? '+' : '') . $totalPercentageIncrease;

        // === Sales by Day of Week (if filtered) ===
        $salesQuery = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->selectRaw("DAYNAME(bookings.created_at) as day, SUM(booking_details.price) as total");

        if ($startDate && $endDate) {
            $salesQuery->whereBetween('bookings.created_at', [$startDate, $endDate]);
        } else {
            $salesQuery->whereBetween('bookings.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        $salesByDay = $salesQuery->groupBy('day')->get()->pluck('total', 'day');

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $salesData = [
            'labels' => $daysOfWeek,
            'data' => collect($daysOfWeek)->map(fn($day) => (int)($salesByDay[$day] ?? 0))
        ];

        $tripTypes = Trip::selectRaw('countries.name as country_name, COUNT(trips.id) as total')
    ->join('cities', 'trips.city_id', '=', 'cities.id')
    ->join('countries', 'cities.country_id', '=', 'countries.id')
    ->groupBy('countries.name')
    ->get();

$productTypeData = [
    'labels' => $tripTypes->pluck('country_name'),
    'data'   => $tripTypes->pluck('total'),
];


        // === Cashier Data ===
        $cashiers = Booking::with('user')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get()
            ->groupBy('user_id')
            ->map(function ($bookings, $userId) use ($startDate, $endDate) {
                $user = $bookings->first()->user;

                $totalAmount = DB::table('booking_details')
                    ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
                    ->where('bookings.user_id', $userId)
                    ->when($startDate && $endDate, fn($q) => $q->whereBetween('bookings.created_at', [$startDate, $endDate]))
                    ->sum('booking_details.price');

                $today = Carbon::today();
                $yesterday = $today->copy()->subDay();

                $todayAmount = DB::table('booking_details')
                    ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
                    ->where('bookings.user_id', $userId)
                    ->whereDate('bookings.created_at', $today)
                    ->sum('booking_details.price');

                $yesterdayAmount = DB::table('booking_details')
                    ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
                    ->where('bookings.user_id', $userId)
                    ->whereDate('bookings.created_at', $yesterday)
                    ->sum('booking_details.price');

                $percentageChange = $yesterdayAmount > 0
                    ? number_format((($todayAmount - $yesterdayAmount) / $yesterdayAmount) * 100, 2)
                    : '0.00';

                return [
                    'id'               => $user->id,
                    'name'             => $user->name,
                    'avatar'           => $user->avatar ?? 'static/pos/user/avatar.png',
                    'totalAmount'      => (int)$totalAmount,
                    'percentageChange' => $percentageChange,
                ];
            })->values();

        // === Final Response ===
        return response()->json([
            'dashboard' => [
                'statistic' => [
                    'totalProduct'             => $totalTrips,
                    'totalProductType'         => $totalCountries,
                    'totalUser'                => $totalUsers,
                    'totalOrder'               => $totalBookings,
                    'total'                    => (int)$totalRevenue,
                    'totalPercentageIncrease'  => $totalPercentageIncrease,
                    'saleIncreasePreviousDay'  => $saleIncreasePreviousDay
                ],
                'salesData'       => $salesData,
                'productTypeData' => $productTypeData,
                'cashierData'     => ['data' => $cashiers]
            ],
            'message' => 'ទទួលបានទិន្នន័យដោយជោគជ័យ'
        ], Response::HTTP_OK);
    }

}
