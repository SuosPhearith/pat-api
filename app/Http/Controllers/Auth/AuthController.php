<?php

namespace App\Http\Controllers\Auth;

// ===================================================>> Core Library
use Illuminate\Http\Request; // For Getting requested Payload from Client
use Illuminate\Http\Response; // For Responsing data back to Client

// ===================================================>> Third Party Library fuck
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

// ===================================================>> Custom Library
use App\Http\Controllers\MainController;
use App\Models\User\User;

class AuthController extends MainController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $req)
    {
        // ===>> Data Validation
        $this->validate($req,
            [
                'username' => ['required'],
                'password' => 'required|min:6|max:20'
            ],
            [
                'username.required' => 'សូមបញ្ចូលអុីម៉ែលឬលេខទូរស័ព្ទ',
                'password.required' => 'សូមបញ្ចូលលេខសម្ងាត់',
                'password.min'      => 'លេខសម្ងាត់ត្រូវធំជាងឬស្មើ៦',
                'password.max'      => 'លេខសម្ងាត់ត្រូវតូចជាងឬស្មើ២០',
            ]
        );
    
        try {
            // ===>> Set JWT Token Time To Live
            JWTAuth::factory()->setTTL(1200); // 1200 minutes
    
            $loginField = filter_var($req->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
    
            // ===>> Prepare credentials
            $credentials = [
                $loginField    => $req->username,
                'password'     => $req->password,
                'is_active'    => 1,
                'deleted_at'   => null,
            ];
    
            // ===>> Attempt login
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'ឈ្មោះអ្នកប្រើឬពាក្យសម្ងាត់មិនត្រឹមត្រូវ។'
                ], Response::HTTP_UNAUTHORIZED);
            }
    
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'បរាជ័យ',
                'message' => 'Cannot Login',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    
        // ===>> Get authenticated user
        $user = auth()->user();
    
        $dataUser = [
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => $user->avatar,
            'phone'  => $user->phone
        ];
    
        // ===>> Determine role
        $role = $user->type_id == 2 ? 'Guest' : 'Admin';
    
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() / 60 . ' hours',
            'user'         => $dataUser,
            'role'         => $role
        ], Response::HTTP_OK);
    }
    
    public function register(Request $req)
    {
        // ✅ Validate Input
        $this->validate($req, [
            'name'      => 'required|string|max:50',
            'email'     => 'required|email|unique:user,email',
            'password'  => 'required|string|min:6|max:20',
        ], [
            'name.required'     => 'សូមបញ្ចូលឈ្មោះ',
            'email.required'    => 'សូមបញ្ចូលអុីម៉ែល',
            'email.email'       => 'អុីម៉ែលមិនត្រឹមត្រូវ',
            'email.unique'      => 'អុីម៉ែលមានរួចហើយ',
            'password.required' => 'សូមបញ្ចូលលេខសម្ងាត់',
            'password.min'      => 'លេខសម្ងាត់ត្រូវធំជាងឬស្មើ៦',
            'password.max'      => 'លេខសម្ងាត់ត្រូវតូចជាងឬស្មើ២០',
        ]);

        try {
            // ✅ Create New User
            $user = User::create([
                'type_id'   => 2, // default to Guest
                'name'      => $req->name,
                'email'     => $req->email,
                'password'  => bcrypt($req->password),
                'is_active' => 1, // active by default (optional)
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'ការចុះឈ្មោះជោគជ័យ',
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'ការចុះឈ្មោះបរាជ័យ',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // ===>> Make Application Logout
        auth()->logout();

        // ===>> Success Response Back to Client
        return response()->json(['message' => 'Successfully logged out'], 200);
    }


}
