<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MainController;
use App\Models\City;
use App\Models\Country;
use App\Services\FileUpload;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CityController extends MainController
{
    public function setUpData()
    {
        $countries = Country::select('id', 'name')->orderBy('name')->get();

        return response()->json($countries, Response::HTTP_OK);
    }

    public function listing(Request $req)
    {
        // Base query with country relationship and only select 'name' field
        $data = City::with(['country:id,name']); // 👈 only fetch id & name from country

        // Filter by city name or country name
        if ($req->key && $req->key != '') {
            $data = $data->where(function ($query) use ($req) {
                $query->where('cities.name', 'LIKE', '%' . $req->key . '%')
                    ->orWhereHas('country', function($q) use ($req) {
                        $q->where('name', 'LIKE', '%' . $req->key . '%');
                    });
            });
        }

        // Order and paginate
        $data = $data->orderBy('id', 'desc')
                    ->paginate($req->limit ?? 50, ['*'], 'per_page');

        // Transform the response to include only country name
        $transformed = $data->through(function ($city) {
            return [
                'id'    => $city->id,
                'name'  => $city->name,
                'image' => $city->image,
                'country' => $city->country->name ?? null,
                'created_at' => $city->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return response
        return response()->json($transformed, Response::HTTP_OK);
    }


    public function view($id = 0)
    {
        // Find city with country name only
        $data = City::with(['country:id,name'])->find($id);

        if ($data) {
            // Transform manually since it's a single model, not a collection
            $transformed = [
                'id'      => $data->id,
                'name'    => $data->name,
                'image'   => $data->image,
                'country' => $data->country->name ?? null,
                'created_at' => $data->created_at->format('Y-m-d H:i:s'),
            ];

            return response()->json($transformed, Response::HTTP_OK);
        } else {
            return response()->json([
                'status'  => 'បរាជ័យ',
                'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ',
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    public function create(Request $req)
    {
        // Check validation
        $this->validate(
            $req,
            [
                'name'          => 'required|max:150',
                'country_id'     => 'required|exists:countries,id',
                // 'trip_days'      => 'required|integer|min:1',
                // 'price'          => 'required|string|max:10',
            ],
            [
                'name.required'         => 'សូមបញ្ចូលឈ្មោះទីក្រុង',
                'name.max'               => 'ឈ្មោះទីក្រុងមិនអាចលើសពី១៥០ខ្ទង់',
                'country_id.required'   => 'សូមជ្រើសរើសប្រទេស',
                'country_id.exists'      => 'ប្រទេសដែលជ្រើសរើសមិនត្រឹមត្រូវ',
                // 'trip_days.required'    => 'សូមបញ្ចូលចំនួនថ្ងៃធ្វើដំណើរ',
                // 'trip_days.integer'     => 'ចំនួនថ្ងៃធ្វើដំណើរត្រូវតែជាលេខគត់',
                // 'trip_days.min'         => 'ចំនួនថ្ងៃធ្វើដំណើរត្រូវតែយ៉ាងតិច១ថ្ងៃ',
                // 'price.required'        => 'សូមបញ្ចូលតម្លៃ',
            ]
        );

        $city = new City;
        $city->name = $req->name;
        $city->country_id = $req->country_id;
        // $city->trip_days = $req->trip_days;
        // $city->price = $req->price;

        // Save the data to DB
        $city->save();

        // Upload image
        if ($req->image) {
            $folder = Carbon::today()->format('d-m-y');
            $image = FileUpload::uploadFile($req->image, 'cities/' . $folder, $req->fileName);

            // Save image
            $city->image = $image['url'];
            $city->save();
        }

        return response()->json([
            'data'      => City::with('country')->find($city->id),
            'message'   => 'ទីក្រុងត្រូវបានបង្កើតដោយជោគជ័យ។'
        ], Response::HTTP_OK);
    }

    public function update(Request $req, $id = 0)
    {
        $this->validate(
            $req,
            [
                'name'          => 'required|max:150',
                'country_id'   => 'required|exists:countries,id',
                // 'trip_days'     => 'required|integer|min:1',
                // 'price'        => 'required|string|max:10',
            ],
            [
                'name.required'         => 'សូមបញ្ចូលឈ្មោះទីក្រុង',
                'name.max'               => 'ឈ្មោះទីក្រុងមិនអាចលើសពី១៥០ខ្ទង់',
                'country_id.required'   => 'សូមជ្រើសរើសប្រទេស',
                'country_id.exists'      => 'ប្រទេសដែលជ្រើសរើសមិនត្រឹមត្រូវ',
                // 'trip_days.required'    => 'សូមបញ្ចូលចំនួនថ្ងៃធ្វើដំណើរ',
                // 'trip_days.integer'     => 'ចំនួនថ្ងៃធ្វើដំណើរត្រូវតែជាលេខគត់',
                // 'trip_days.min'         => 'ចំនួនថ្ងៃធ្វើដំណើរត្រូវតែយ៉ាងតិច១ថ្ងៃ',
                // 'price.required'        => 'សូមបញ្ចូលតម្លៃ',
            ]
        );

        $city = City::find($id);

        if ($city) {
            $city->name = $req->name;
            $city->country_id = $req->country_id;
            // $city->trip_days = $req->trip_days;
            // $city->price = $req->price;

            $city->save();

            if ($req->image) {
                $folder = Carbon::today()->format('d-m-y');
                $image = FileUpload::uploadFile($req->image, 'cities/' . $folder, $req->fileName);

                if ($image['url']) {
                    $city->image = $image['url'];
                    $city->save();
                }
            }

            return response()->json([
                'status'    => 'ជោគជ័យ',
                'message'   => 'ទីក្រុងត្រូវបានកែប្រែជោគជ័យ',
                'data' => [
                    'id' => $city->id,
                    'name' => $city->name,
                    'image' => $city->image,
                    'country' => $city->country->name ?? null,
                    'created_at' => $city->created_at->format('Y-m-d H:i:s'),
                ],
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status'    => 'បរាជ័យ',
                'message'   => 'ទិន្នន័យមិនត្រឹមត្រូវ',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete($id = 0)
    {
        $data = City::find($id);

        if ($data) {
            $data->delete();
            return response()->json([
                'status'    => 'ជោគជ័យ',
                'message'   => 'ទិន្នន័យត្រូវបានលុប',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status'    => 'បរាជ័យ',
                'message'   => 'ទិន្នន័យមិនត្រឹមត្រូវ',
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}