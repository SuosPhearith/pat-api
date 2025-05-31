<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\MainController;
use App\Models\Country;
use App\Models\Continent;
use App\Services\FileUpload;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ContryController extends MainController
{
    public function setUpData()
    {
        $countries = Continent::select('id', 'name')->orderBy('name')->get();

        return response()->json($countries, Response::HTTP_OK);
    }
    public function listing(Request $req)
    {
        // Base query with continent relationship
        $data = Country::with('continent');

        // ===>> Filter Data
        if ($req->key && $req->key != '') {
            $data = $data->where(function ($query) use ($req) {
                $query->where('name', 'LIKE', '%' . $req->key . '%')
                    ->orWhereHas('continent', function ($q) use ($req) {
                        $q->where('name', 'LIKE', '%' . $req->key . '%');
                    });
            });
        }

        // Paginate the data
        $paginated = $data->orderBy('id', 'desc')
                        ->paginate($req->limit ?? 10, ['*'], 'per_page');

        // Map the response to include continent name
        $result = $paginated->getCollection()->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'continent' => $country->continent ? $country->continent->name : null,
                'population' => $country->population,
                'territory' => $country->territory,
                'description' => $country->description,
                'image' => $country->image,
                'created_at' => $country->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return response with pagination
        return response()->json([
            'data' => $result,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
        ], Response::HTTP_OK);
    }

    public function view($id = 0)
    {
        // Eager-load the continent relationship
        $country = Country::with('continent')->find($id);

        // ===>> Check if data exists
        if ($country) {
            // Return formatted response with continent name
            return response()->json([
                'id' => $country->id,
                'name' => $country->name,
                'continent' => $country->continent ? $country->continent->name : null,
                'population' => $country->population,
                'territory' => $country->territory,
                'description' => $country->description,
                'image' => $country->image,
                'created_at' => $country->created_at->format('Y-m-d H:i:s'),
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'បរាជ័យ',
                'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function create(Request $req)
    {
        $this->validate(
            $req,
            [
                'name'         => 'required|max:50',
                'continent_id' => 'required|exists:continents,id',
                'population'   => 'required|max:100',
                'territory'    => 'required|max:100',
                'description'  => 'required|max:10000',
            ],
            [
                'name.required'         => 'សូមបញ្ចូលឈ្មោះផលិតផល',
                'name.max'              => 'ឈ្មោះផលិតផលមិនអាចលើសពី50ខ្ទង់',

                'continent_id.required' => 'សូមជ្រើសរើសទ្វីប',
                'continent_id.exists'   => 'ទ្វីបទាំងនេះមិនមានទេ',

                'population.required'   => 'សូមបញ្ចូលប្រជាជន',
                'population.max'        => 'សូមបញ្ចូលប្រជាជនមិនអោយលើសពី100ខ្ទង់',

                'territory.required'    => 'សូមបញ្ចូលផ្ទៃដី',
                'territory.max'         => 'សូមបញ្ចូលផ្ទៃដីមិនអោយលើសពី100ខ្ទង់',

                'description.required'  => 'សូមបញ្ចូលការបរិយាយ',
                'description.max'       => 'ការបរិយាយមិនអាចលើសពី10000ខ្ទង់',
            ]
        );

        $country = new Country;
        $country->name         = $req->name;
        $country->continent_id = $req->continent_id;
        $country->population   = $req->population;
        $country->territory    = $req->territory;
        $country->description  = $req->description;
        $country->save();

        if ($req->image) {
            $folder = Carbon::today()->format('d-m-y');
            $image = FileUpload::uploadFile($req->image, 'contries/' . $folder, $req->fileName);

            if (!empty($image['url'])) {
                $country->image = $image['url'];
                $country->save();
            }
        }

        return response()->json([
            'data'    => Country::with('continent')->find($country->id),
            'message' => 'ប្រទេសត្រូវបានបង្កើតដោយជោគជ័យ។'
        ], Response::HTTP_OK);
    }

    public function update(Request $req, $id = 0)
    {
        $this->validate(
            $req,
            [
                'name'         => 'required|max:50',
                'continent_id' => 'required|exists:continents,id',
                'population'   => 'required|max:100',
                'territory'    => 'required|max:100',
                'description'  => 'required|max:10000',
            ],
            [
                'name.required'         => 'សូមបញ្ចូលឈ្មោះផលិតផល',
                'name.max'              => 'ឈ្មោះផលិតផលមិនអាចលើសពី50ខ្ទង់',

                'continent_id.required' => 'សូមជ្រើសរើសទ្វីប',
                'continent_id.exists'   => 'ទ្វីបទាំងនេះមិនមានទេ',

                'population.required'   => 'សូមបញ្ចូលប្រជាជន',
                'population.max'        => 'សូមបញ្ចូលប្រជាជនមិនអោយលើសពី100ខ្ទង់',

                'territory.required'    => 'សូមបញ្ចូលផ្ទៃដី',
                'territory.max'         => 'សូមបញ្ចូលផ្ទៃដីមិនអោយលើសពី100ខ្ទង់',

                'description.required'  => 'សូមបញ្ចូលការបរិយាយ',
                'description.max'       => 'ការបរិយាយមិនអាចលើសពី10000ខ្ទង់',
            ]
        );

        $country = Country::find($id);

        if (!$country) {
            return response()->json([
                'status'  => 'បរាជ័យ',
                'message' => 'រកមិនឃើញទិន្នន័យ',
            ], Response::HTTP_NOT_FOUND);
        }

        $country->name         = $req->name;
        $country->continent_id = $req->continent_id;
        $country->population   = $req->population;
        $country->territory    = $req->territory;
        $country->description  = $req->description;
        $country->save();

        if ($req->image) {
            $folder = Carbon::today()->format('d-m-y');
            $image  = FileUpload::uploadFile($req->image, 'contries/' . $folder, $req->fileName);

            if (!empty($image['url'])) {
                $country->image = $image['url'];
                $country->save();
            }
        }

        return response()->json([
            'status'  => 'ជោគជ័យ',
            'message' => 'ទិន្នន័យត្រូវបានកែប្រែដោយជោគជ័យ',
            'data'    => [
                'id' => $country->id,
                'name' => $country->name,
                'continent' => $country->continent ? $country->continent->name : null,
                'population' => $country->population,
                'territory' => $country->territory,
                'description' => $country->description,
                'image' => $country->image,
                'created_at' => $country->created_at->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_OK);

    }


    public function delete($id = 0){

        // Find record from DB
        $data = Country::find($id);

        // ===>> Check if data is valide
        if ($data) { // Yes

            // ===>> Delete Data from DB
            $data->delete();

            // ===> Success Response Back to Client
            return response()->json([
                'status'    => 'ជោគជ័យ',
                'message'   => 'ទិន្នន័យត្រូវបានលុប',
            ], Response::HTTP_OK);

        } else { // No

            // ===> Failed Response Back to Client
            return response()->json([
                'status'    => 'បរាជ័យ',
                'message'   => 'ទិន្នន័យមិនត្រឹមត្រូវ',
            ], Response::HTTP_BAD_REQUEST);

        }
    }
}
