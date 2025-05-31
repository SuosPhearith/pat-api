<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MainController;
use Illuminate\Database\QueryException;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
class PrintController extends MainController
{
    private $JS_BASE_URL;
    private $JS_USERNAME;
    private $JS_PASSWORD;
    private $JS_TEMPLATE;

    public function __construct()
    {
        $this->JS_BASE_URL   = env('JS_BASE_URL');
        $this->JS_USERNAME   = env('JS_USERNAME');
        $this->JS_PASSWORD   = env('JS_PASSWORD');
        $this->JS_TEMPLATE   = env('JS_TEMPLATE');
    }


    public function printAllTripReportsWithBookingsOnly(Request $request)
    {
        try {
            $url = $this->JS_BASE_URL . "/api/report";
    
            // Get optional date filters
            $inputStartDate = $request->input('start_date');
            $inputEndDate   = $request->input('end_date');
    
            // Default to this month if not provided
            $startDate = $inputStartDate ?? Carbon::now()->startOfMonth()->toDateString();
            $endDate   = $inputEndDate ?? Carbon::now()->toDateString();
    
            // Query trips with bookings in the given or default date range
            $trips = Trip::with(['city', 'bookings.details' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->whereHas('bookings.details', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();
    
            // Prepare report data
            $reportData = $trips->map(function ($trip) {
                $totalDays = 0;
                $totalPrice = 0;
    
                foreach ($trip->bookings as $booking) {
                    foreach ($booking->details as $detail) {
                        if ($detail->trip_id === $trip->id) {
                            $totalDays += (int) $detail->trip_days;
                            $totalPrice += $detail->price * $detail->num_of_guests;
                        }
                    }
                }
    
                return [
                    'trip_name'   => $trip->city->name ?? 'N/A',
                    'trip_days'   => $totalDays,
                    'total_price' => round($totalPrice, 2),
                ];
            })->filter(fn($trip) => $trip['total_price'] > 0)->values();
    
            // ✅ Move start_date, end_date, and currentDate INTO the data payload
            $payload = [
                'template' => [
                    'name' => $this->JS_TEMPLATE, // Named template
                ],
                'data' => [
                    'trips'       => $reportData,
                ],
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'currentDate' => Carbon::now()->toDateTimeString(),
            ];
            // return $payload;
    
            $response = Http::withBasicAuth($this->JS_USERNAME, $this->JS_PASSWORD)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);
    
            if (!$response->successful()) {
                return [
                    'file_base64' => '',
                    'error'       => 'JSReport error: ' . $response->body(),
                ];
            }
    
            return [
                'file_base64' => base64_encode($response->body()),
                'error'       => '',
            ];
        } catch (\Exception $e) {
            return [
                'file_base64' => '',
                'error'       => $e->getMessage(),
            ];
        }
    }
    
    public function printTripReportById(Request $request, $tripId)
    {
        try {
            $url = $this->JS_BASE_URL . "/api/report";

            $startDate = $request->input('start_date');
            $endDate   = $request->input('end_date');

            $trip = Trip::with(['city', 'bookings.details' => function ($query) use ($startDate, $endDate, $tripId) {
                $query->where('trip_id', $tripId);

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }])
            ->where('id', $tripId)
            ->whereHas('bookings.details', function ($query) use ($startDate, $endDate, $tripId) {
                $query->where('trip_id', $tripId);

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->first();

            if (!$trip) {
                return ['file_base64' => '', 'error' => 'Trip not found or no bookings in selected date range'];
            }

            $totalDays = 0;
            $totalPrice = 0;

            foreach ($trip->bookings as $booking) {
                foreach ($booking->details as $detail) {
                    if ($detail->trip_id == $trip->id) {
                        $totalDays += (int)$detail->trip_days;
                        $totalPrice += $detail->price * $detail->num_of_guests;
                    }
                }
            }

            $reportData = [
                [
                    'trip_name'   => $trip->title,
                    'city_name'   => $trip->city->name ?? 'N/A',
                    'trip_days'   => $totalDays,
                    'total_price' => round($totalPrice, 2),
                ]
            ];

            $payload = [
                'template' => ['name' => $this->JS_TEMPLATE],
                'data'     => ['trips' => $reportData],
            ];

            $response = Http::withBasicAuth($this->JS_USERNAME, $this->JS_PASSWORD)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if (!$response->successful()) {
                return ['file_base64' => '', 'error' => 'JSReport error: ' . $response->body()];
            }

            return [
                'file_base64' => base64_encode($response->body()),
                'error'       => '',
            ];
        } catch (\Exception $e) {
            return ['file_base64' => '', 'error' => $e->getMessage()];
        }
    }



}