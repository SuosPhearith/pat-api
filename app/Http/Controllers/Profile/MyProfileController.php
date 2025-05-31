<?php

namespace App\Http\Controllers\Profile;

// ============================================================================>> Core Library
use Illuminate\Http\Request; // For Getting requested Payload from Client
use Illuminate\Http\Response; // For Responsing data back to Client
use Illuminate\Support\Facades\Hash; // For Encripting password

// ============================================================================>> Third Party Library
use Carbon\Carbon; // Data Time format & Calculation
use Tymon\JWTAuth\Facades\JWTAuth; // Get Current Logged User

// ============================================================================>> Custom Library
// Controller
use App\Http\Controllers\MainController;

// Services
use App\Services\FileUpload; // Upload Image/File to File Serivce

// Model
use App\Models\User\User;

class MyProfileController extends MainController
{

    public function view(){

        // ===>> Get current logged user by token
        $auth = JWTAuth::parseToken()->authenticate();

        // ===>> Get user Data from DB;
        $user = User::select('id', 'name', 'phone', 'email', 'avatar')->where('id', $auth->id)->first();

        // ===>> Success Response Back to Client
        return response()->json($user, Response::HTTP_OK);

    }

    public function update(Request $req){

        // ===>>> Data Validation
        $this->validate(
            $req,
            [
                'name'  => 'required|max:60',
                'phone' => 'required|min:9|max:10',
            ],
            [
                'name.required'     => 'សូមបញ្ចូលឈ្មោះ',
                'name.max'          => 'ឈ្មោះមិនអាចលើសពី៦០',
                'phone.required'    => 'សូមបញ្ចូលលេខទូរស័ព្ទ',
                'phone.min'         => 'សូមបញ្ចូលលេខទូរស័ព្ទយ៉ាងតិច៩ខ្ទង់',
                'phone.max'         => 'លេខទូរស័ព្ទយ៉ាងច្រើនមិនលើសពី១០ខ្ទង់'

            ]
        );

        // ===>> Get current logged user by token
        $auth = JWTAuth::parseToken()->authenticate();

        // ===>> Start to update user
        $user = User::findOrFail($auth->id);

        // ===>> Check if user is valid
        if ($user) { // Yes

            // Mapping between database table field and requested data from client
            $user->name         = $req->name;
            $user->phone        = $req->phone;
            $user->email        = $req->email;
            $user->updated_at   = Carbon::now()->format('Y-m-d H:i:s');

             // ===>> Upload Avatar to File Service
             if ($req->avatar) {

                // ===>> Create Folder by Date
                $folder = Carbon::today()->format('d-m-y');

                // ===>> Upload Image to File Service
                $avatar  = FileUpload::uploadFile($req->avatar, 'my-profile/', $req->fileName);

                // ===>> Check if success upload
                if ($avatar['url']) { // Yes

                    $user->avatar     = $avatar['url'];
                }
            }

            // ===>> Save to DB
            $user->save();

            // ===>> Success Response Back to Client
            return response()->json([
                'status'    => 'ជោគជ័យ',
                'message'   => '* ព័ត៌មានផ្ទាល់ខ្លួនរបស់អ្នកត្រូវបានកែប្រែ *',
                'data'      => [
                        'name'  => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                ]
            ], Response::HTTP_OK);

        }else{

            // ===>> Failed Response Back to Client
            return response()->json([
                'status' => 'error',
                'message' => 'ទិន្នន័យរបស់អ្នកមិនត្រឹមត្រូវ'
            ], Response::HTTP_BAD_REQUEST);

        }
    }

    public function changePassword(Request $req){

         // ===>>> Data Validation
        $this->validate(
            $req,
            [
                'old_password'      => 'required|min:6|max:20',
                'new_password'      => 'required|min:6|max:20',
                'confirm_password'  => 'required|min:6|max:20|same:new_password',
            ],
            [
                'old_password.required'     => 'សូមបញ្ចូលពាក្យសម្ងាត់',
                'old_password.min'          => 'ពាក្យសម្ងាត់ចាស់ ត្រូវមាន៦ ខ្ទង់យ៉ាងតិច',
                'old_password.max'          => 'ពាក្យសម្ងាត់ចាស់ ត្រូវមាន២០ ច្រើនបំផុត',

                'new_password.required'     => 'សូមបញ្ចូលពាក្យសម្ងាត់ថ្មី',
                'new_password.min'          => 'ពាក្យសម្ងាត់ថ្មី ត្រូវមាន៦ ខ្ទង់យ៉ាងតិច',
                'new_password.max'          => 'ពាក្យសម្ងាត់ថ្មី ត្រូវមាន២០ ច្រើនបំផុត',

                'confirm_password.same'     => 'សូមបញ្ចាក់ថាពាក្យសម្ងាត់ថ្មី'

            ]
        );

        // ===>> Get current logged user by token
        $auth = JWTAuth::parseToken()->authenticate();

        // ===>> Start to update user
        $user = User::findOrFail($auth->id);

        // ===>> Compare the Old and New Password
        if (Hash::check($req->old_password, $user->password)) { // Yes

            // ===>> Pair Passowrd Field
            $user->password = Hash::make($req->password);

            // ===>> Save to DB
            $user->save();

            // ===>> Success Response Back to Client
            return response()->json([
                'status'    => 'success',
                'message'   => 'លេខសម្ងាត់របស់អ្នកត្រូវបានកែប្រែដោយជោគជ័យ'
            ], Response::HTTP_OK);

        } else { // No

            // ===>> Failed Response Back to Client
            return response()->json([
                'status'    => 'error',
                'message'   => 'ពាក្យសម្ងាត់ចាស់របស់អ្នកមិនត្រឹមត្រូវ'
            ], Response::HTTP_BAD_REQUEST);

        }
    }

}
