<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MainController;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\City;
use App\Models\Trip;
use App\Models\User\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class BookingController extends MainController
{
    private function _sendNotification($booking)
    {
        // Calculate total price from the first booking detail (assuming one detail per booking)
        $firstDetail = $booking->details->first();
        $totalPrice = $firstDetail ? ($firstDetail->price * $firstDetail->num_of_guests) : 0;
        
        $htmlMessage  = "<b> ការកក់បានជោគជ័យ! </b>\n";
        $htmlMessage .= "- លេខវិក្ក័យបត្រ ៖   {$booking->receipt_number}\n";
        $htmlMessage .= "- អ្នកកក់ ៖   {$booking->name}\n";
        $htmlMessage .= "- ទូរស័ព្ទ ៖   {$booking->phone_number}\n";
        $htmlMessage .= "- ទីកន្លែងកំសាន្ត ៖   {$booking->destination}\n";
        $htmlMessage .= "- ចំនួនអ្នកដំណើរ ​​​៖    {$booking->num_of_guests}\n";
        $htmlMessage .= "- ថ្ងៃចេញដំណើរ ​៖    {$booking->checkin_date}\n";
        $htmlMessage .= "- តម្លៃសរុប ៖    $" . number_format($totalPrice, 2) . "\n";
        $htmlMessage .= "- កាលបរិច្ឆេទកក់ ៖    {$booking->booked_at}";
    
        TelegramService::sendMessage($htmlMessage, env('TELEGRAM_CHAT_ID'));
    }
    
    public function makeBooking(Request $req)
    {
        $this->validate($req, [
            'phone_number'   => 'required|string|max:20',
            'num_of_guests'  => 'required|integer|min:1',
            'trip_id'        => 'required|exists:trips,id'
        ]);
    
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
    
        $trip = Trip::find($req->trip_id);
    
        if (!$trip) {
            return response()->json(['message' => 'Trip not found.'], Response::HTTP_NOT_FOUND);
        }
    
        // Calculate total price
        $totalPrice = $trip->price * $req->num_of_guests;
    
        $booking = new Booking();
        $booking->receipt_number = $this->_generateReceiptNumber();
        $booking->trip_id       = $trip->id;
        $booking->user_id       = $user->id;
        $booking->name          = $user->name;
        $booking->phone_number  = $req->phone_number;
        $booking->num_of_guests = $req->num_of_guests;
        $booking->checkin_date  = $trip->start_date;
        $booking->destination   = $trip->city->name;
        $booking->status        = 'paid';
        $booking->payment       = 'credit card';
        $booking->booked_at     = now();
        $booking->save();
    
        $bookingDetail = new BookingDetail();
        $bookingDetail->booking_id    = $booking->id;
        $bookingDetail->trip_id       = $trip->id;
        $bookingDetail->city_id       = $trip->city_id;
        $bookingDetail->trip_days     = $trip->trip_days;
        $bookingDetail->price         = $trip->price;
        $bookingDetail->num_of_guests = $req->num_of_guests;
        $bookingDetail->save();
    
        $bookingData = Booking::with([
                'user:id,name',
                'trip:id,title,city_id',
                'details:id,booking_id,trip_id,city_id,trip_days,price,num_of_guests',
                'details.city:id,name'
            ])
            ->find($booking->id);
    
        // Add total_price to the booking data before sending notification
        $bookingData->total_price = $totalPrice;
    
        $this->_sendNotification($bookingData);
    
        return response()->json([
            'booking' => $bookingData,
            'total_price' => $totalPrice,
            'message' => 'Booking successful!'
        ], Response::HTTP_OK);
    }


    public function listBookings(Request $request)
{
    $page  = $request->input('page', 1);
    $limit = $request->input('limit', 10);
    $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
    $key   = $request->input('key');

    $query = Booking::with([
            'user:id,name',
            'trip:id,title',
            'details.city:id,name,image'
        ])
        ->select([
            'id',
            'receipt_number',
            'phone_number',
            'num_of_guests',
            'user_id',
            'trip_id',
            'booked_at',
            'checkin_date'
        ])
        ->orderBy('booked_at', $order);

    // 🔍 Filter by keyword
    if (!empty($key)) {
        $query->where(function ($q) use ($key) {
            $q->where('receipt_number', 'like', "%$key%")
              ->orWhere('phone_number', 'like', "%$key%")
              ->orWhereHas('user', function ($sub) use ($key) {
                  $sub->where('name', 'like', "%$key%");
              })
              ->orWhereHas('trip', function ($sub) use ($key) {
                  $sub->where('title', 'like', "%$key%");
              })
              ->orWhereHas('details.city', function ($sub) use ($key) {
                  $sub->where('name', 'like', "%$key%");
              });
        });
    }

    $bookings = $query->paginate($limit, ['*'], 'page', $page);

    $transformed = $bookings->getCollection()->map(function ($booking) {
        $detail = $booking->details->first();

        $guestCount = $detail->num_of_guests ?? $booking->num_of_guests;
        $unitPrice = $detail->price ?? 0;

        return [
            'id'             => $booking->id,
            'receipt_number' => $booking->receipt_number,
            'city_name'      => $detail->city->name ?? null,
            'user_name'      => $booking->user->name ?? null,
            'trip_days'      => $detail->trip_days ?? null,
            'price'          => $unitPrice * $guestCount,
            'phone_number'   => $booking->phone_number,
            'num_of_guests'  => $guestCount,
            'checkin_date'   => $booking->checkin_date,
            'booked_at'      => $booking->booked_at,
        ];
    });


    return response()->json([
        'data'          => $transformed,
        'total'         => $bookings->total(),
        'current_page'  => $bookings->currentPage(),
        'per_page'      => $bookings->perPage(),
        'last_page'     => $bookings->lastPage(),
        'message'       => 'Booking list retrieved successfully'
    ], Response::HTTP_OK);
}





public function listBookingsById(Request $request)
{
    $userId = auth()->id();

    if (!$userId) {
        return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }

    $page  = $request->input('page', 1);
    $limit = $request->input('limit', 10);
    $order = in_array(strtolower($request->input('order', 'desc')), ['asc', 'desc']) ? strtolower($request->input('order')) : 'desc';

    $query = Booking::with([
            'user:id,name',
            'trip:id,title',
            'details.city:id,name'
        ])
        ->where('user_id', $userId)
        ->select([
            'id',
            'receipt_number',
            'phone_number',
            'num_of_guests',
            'user_id',
            'trip_id',
            'booked_at',
            'checkin_date'
        ])
        ->orderBy('booked_at', $order);

    $bookings = $query->paginate($limit, ['*'], 'page', $page);

    $transformed = $bookings->getCollection()->map(function ($booking) {
        $detail = $booking->details->first();

        $guestCount = $detail->num_of_guests ?? $booking->num_of_guests;
        $unitPrice = $detail->price ?? 0;

        return [
            'receipt_number' => $booking->receipt_number,
            'city_name'      => $detail->city->name ?? null,
            'user_name'      => $booking->user->name ?? null,
            'trip_days'      => $detail->trip_days ?? null,
            'price'          => $unitPrice * $guestCount,
            'phone_number'   => $booking->phone_number,
            'num_of_guests'  => $guestCount,
            'booked_at'      => $booking->booked_at,
            'checkin_date'   => $booking->checkin_date
        ];
    });

    return response()->json([
        'data'          => $transformed,
        'total'         => $bookings->total(),
        'current_page'  => $bookings->currentPage(),
        'per_page'      => $bookings->perPage(),
        'last_page'     => $bookings->lastPage(),
        'message'       => 'Booking list retrieved successfully'
    ], Response::HTTP_OK);
}


    private function _generateReceiptNumber()
    {
        do {
            $number = rand(1000000, 9999999);
            $exists = Booking::where('receipt_number', $number)->exists();
        } while ($exists);

        return $number;
    }

    // private function _sendNotification($booking)
    // {
    //     $htmlMessage  = "<b> ការកក់បានជោគជ័យ! </b>\n";
    //     $htmlMessage .= "- លេខវិក្ក័យបត្រ   ៖ {$booking->receipt_number}\n";
    //     $htmlMessage .= "- អ្នកកក់        ៖ {$booking->name}\n";
    //     $htmlMessage .= "- ទូរស័ព្ទ        ៖ {$booking->phone_number}\n";
    //     $htmlMessage .= "- ទីកន្លែងកំសាន្ត   ៖ {$booking->destination}\n";
    //     $htmlMessage .= "- ចំនួនអ្នកដំណើរ   ​​​៖ {$booking->num_of_guests}\n";
    //     $htmlMessage .= "- ថ្ងៃចេញដំណើរ​   ៖ {$booking->checkin_date}\n";
    //     $htmlMessage .= "- កាលបរិច្ឆេទកក់   ៖ {$booking->booked_at}";

    //     TelegramService::sendMessage($htmlMessage, env('TELEGRAM_CHAT_ID'));
    // }
}
