<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MainController;
use App\Models\Trip;
use App\Models\City;
use App\Models\Country;
use App\Services\FileUpload;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TripController extends MainController
{
    public function setUpData()
    {
        $cities = City::with('country')->select('id', 'name', 'country_id')->orderBy('name')->get();

        return response()->json($cities, Response::HTTP_OK);
    }

    public function listing(Request $req)
    {
        // Load trips with city and country
        $data = Trip::with(['city.country']);

        // Filter by city or country name
        if ($req->key && $req->key != '') {
            $data = $data->whereHas('city', function ($query) use ($req) {
                $query->where('name', 'LIKE', '%' . $req->key . '%')
                      ->orWhereHas('country', function ($q) use ($req) {
                          $q->where('name', 'LIKE', '%' . $req->key . '%');
                      });
            });
        }

        // Order and paginate
        $data = $data->orderBy('id', 'desc')->paginate($req->limit ?? 50, ['*'], 'per_page');

        return response()->json($data, Response::HTTP_OK);
    }

    public function view($id = 0)
    {
        $trip = Trip::with('city.country')->find($id);

        if ($trip) {
            return response()->json($trip, Response::HTTP_OK);
        }

        return response()->json([
            'status' => 'បរាជ័យ',
            'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ',
        ], Response::HTTP_BAD_REQUEST);
    }

    public function create(Request $req)
    {
        $this->validate($req, [
            'title' => 'required|max:150',
            'description' => 'required|string',
            'city_id' => 'required|exists:cities,id',
            'trip_days' => 'required|integer|min:1',
            'price' => 'required|string|max:10',
            'start_date' => 'required|date',
        ], [
            'title.required' => 'សូមបញ្ចូលឈ្មោះដំណើរ',
            'description.required' => 'សូមបញ្ចូលការពិពណ៌នា',
            'city_id.required' => 'សូមជ្រើសរើសទីក្រុង',
            'trip_days.required' => 'សូមបញ្ចូលចំនួនថ្ងៃធ្វើដំណើរ',
            'price.required' => 'សូមបញ្ចូលតម្លៃ',
            'start_date.required' => 'សូមបញ្ចូលថ្ងៃចេញដំណើរ',
        ]);
    
        $trip = new Trip();
        $trip->title = $req->title;
        $trip->description = $req->description;
        $trip->city_id = $req->city_id;
        $trip->trip_days = $req->trip_days;
        $trip->price = $req->price;
    
        // Corrected date logic
        $startDate = Carbon::parse($req->start_date);
        $trip->start_date = $startDate;
        $trip->end_date = $startDate->copy()->addDays($req->trip_days);
    
        $trip->save();
    
        return response()->json([
            'data' => Trip::with('city.country')->find($trip->id),
            'message' => 'ដំណើរត្រូវបានបង្កើតដោយជោគជ័យ។'
        ], Response::HTTP_OK);
    }
    
    

    public function update(Request $req, $id = 0)
    {
        $this->validate($req, [
            'title' => 'nullable|max:150',
            'description' => 'nullable|string',
            'city_id' => 'nullable|exists:cities,id',
            'trip_days' => 'nullable|integer|min:1',
            'price' => 'nullable|string|max:10',
            'start_date' => 'nullable|date',
        ]);

        $trip = Trip::find($id);

        if (!$trip) {
            return response()->json([
                'status' => 'បរាជ័យ',
                'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ',
            ], Response::HTTP_BAD_REQUEST);
        }

        $trip->title = $req->title ?? $trip->title;
        $trip->description = $req->description ?? $trip->description;
        $trip->city_id = $req->city_id ?? $trip->city_id;
        $trip->trip_days = $req->trip_days ?? $trip->trip_days;
        $trip->price = $req->price ?? $trip->price;
        $trip->start_date = $req->start_date ?? $trip->start_date;
        $trip->end_date = \Carbon\Carbon::parse($trip->start_date)->addDays($trip->trip_days); // ✅
        $trip->save();

        // Optional image update
        if ($req->image) {
            $folder = Carbon::today()->format('d-m-y');
            $image = FileUpload::uploadFile($req->image, 'trips/' . $folder, $req->fileName);
            if ($image['url']) {
                $trip->image = $image['url'];
                $trip->save();
            }
        }

        return response()->json([
            'status' => 'ជោគជ័យ',
            'message' => 'ដំណើរត្រូវបានកែប្រែជោគជ័យ',
            'data' => Trip::with('city.country')->find($trip->id),
        ], Response::HTTP_OK);
    }


    public function delete($id = 0)
    {
        $trip = Trip::find($id);

        if (!$trip) {
            return response()->json([
                'status' => 'បរាជ័យ',
                'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ',
            ], Response::HTTP_BAD_REQUEST);
        }

        $trip->delete();

        return response()->json([
            'status' => 'ជោគជ័យ',
            'message' => 'ទិន្នន័យត្រូវបានលុប',
        ], Response::HTTP_OK);
    }
}
