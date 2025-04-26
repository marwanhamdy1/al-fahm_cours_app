<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CompleteProfile;
use App\Http\Requests\PhoneNumberRequest;
use App\Http\Requests\AddChildRequest;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Hash;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

use Exception;

class AuthController extends Controller
{
    use ImageUploadTrait;
   public function phoneNumber(PhoneNumberRequest $request)
{
    try {
        // Check if user with the phone number already exists
        $user = User::where('phone_number', $request->phone_number)->first();

        if ($user) {
            // If the user has a first name and last name, return success
            if ($user->first_name && $user->last_name) {
                return ResponseHelper::success("User Found", ['status' => 1]);
            }

            // If the user exists but profile is incomplete, return appropriate status
            return ResponseHelper::success("User Exists but Profile Incomplete", ['status' => 0]);
        }

        // Create a new user if phone number does not exist
        User::create(['phone_number' => $request->phone_number]);

        return ResponseHelper::success("New User", ['status' => 0]);

    } catch (Exception $e) {
        return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
    }
}
        public function verifyCode(Request $request){
            try{
        $user = User::where('phone_number',  $request->phone_number)
        ->where( 'verify_code', $request->verify_code)->first();
        if(!$user){
            $this->storeLoginLog($request, 'failed');
        return ResponseHelper::error( "verify code does not match", 500);
        }
        // Save FCM token if sent
        if ($request->has('fcm_token')) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }
        $token = Auth::login($user);
          // Store successful login attempt
        $this->storeLoginLog($request, 'success');
      // If the user has a first name and last name, return success
        if ($user->first_name && $user->last_name) {
        return ResponseHelper::success("success", ['user'=>$user,'token' => $token ]);
        }
        return ResponseHelper::success("success", ['user'=>null,'token' => $token ]);
          } catch (Exception $e) {
        return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
    }
        }


    public function completeProfile(CompleteProfile $request)
{
    try {
        $user = auth()->user();
        $data = $request->validated();

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Handle identity_id check and parent linking
        if (!in_array($data['role'], ['parent', 'individual'])) {
            if (!isset($data['identity_id'])) {
                return ResponseHelper::error("Identity ID is required", 400);
            }

            $getParent = User::where("identity_id", $data['parent_identity_id'])->first();

            if (!$getParent) {
                return ResponseHelper::error("Failed to complete profile: Identity ID not found", 404);
            }

            $data['parent_id'] = $getParent->id;
            // unset($data['identity_id']);
        }

        // Update user profile
        $user->update($data);

        // Refresh token
        $token = auth()->refresh();

        return ResponseHelper::success("Profile updated successfully", [
            'user' => $user,
            'token' => $token
        ]);

    } catch (Exception $e) {
        return ResponseHelper::error("Failed to complete profile", 500, $e->getMessage());
    }
    }
    public function checkUserName($username){
        $username = User::where("username",$username)->exists();
        if($username){
            return ResponseHelper::error("username not available", 400);
        }
            return ResponseHelper::success("available", ['available' => true]);

    }
    public function myChildren()
{
    $children = auth()->user()->children()->get();

    if ($children->isEmpty()) {
        return ResponseHelper::error("No children found", 404);
    }

    return ResponseHelper::success("Children found", ['data' => $children]);
}

    public function addChild(AddChildRequest $request)
    {
        try {
            $user = auth()->user();
            // $image = $this->saveImage($request->image);
            $child = User::create([
                "first_name"    => $request->first_name,
                "last_name"     => $request->last_name ?? $request->first_name,
                "username"     => $request->username,
                "password"      => Hash::make($request->password),
                "phone_number"  => $request->phone_number,
                "parent_id"     => $user->id,
                'child_type'    => $request->child_type,
                "color"     => $request->color,
                "date_of_birth"     => $request->date_of_birth,
                "identity_id"     => $request->identity_id,
                "image"     => $request->image,
                "role"          => "child",
            ]);

            return ResponseHelper::success("Child Added Successfully", ['child' => $child]);

        } catch (Exception $e) {
            return ResponseHelper::error("Failed to add child", 500, $e->getMessage());
        }
    }
    public function login(Request $request)
{
    try {
        // Validate request
        $request->validate([
            'username' => 'required|string|exists:users,username',
            'password'     => 'required|string|min:6',
            'fcm_token'     => 'nullable|string',
        ]);

        // Find user by phone number
        $user = User::where('username', $request->username)->first();

        // Check if the password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->storeLoginLog($request, 'failed');
            return ResponseHelper::error("Invalid username or password", 401);
        }
          // Save FCM token if sent
        if ($request->has('fcm_token')) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }
        // Generate a new token for the user
        $token = Auth::login($user);
$this->storeLoginLog($request, 'success');
        return ResponseHelper::success("Login successful", [
            'user'  => $user,
            'token' => $token
        ]);

    } catch (\Exception $e) {
        return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
    }
}


    public function me()
    {
        try {
            return response()->json(auth()->user());
        } catch (Exception $e) {
            return ResponseHelper::error("Failed to fetch user data", 500, $e->getMessage());
        }
    }

    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (Exception $e) {
            return ResponseHelper::error("Logout failed", 500, $e->getMessage());
        }
    }

    public function refresh()
    {
        try {
            return $this->respondWithToken(auth()->refresh());
        } catch (Exception $e) {
            return ResponseHelper::error("Failed to refresh token", 500, $e->getMessage());
        }
    }

    protected function respondWithToken($token)
    {
        try {
            return response()->json([
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth()->factory()->getTTL() * 60
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error("Failed to generate token response", 500, $e->getMessage());
        }
    }
    // trash will
     public function loginAdminTrash(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Get user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and is an admin/super_admin
        if (!$user || !in_array($user->role, ['admin', 'super_admin','moderator'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
            'role' => $user->role,
        ]);
    }
   public function image()
{
    $images = [
        asset('storage/images/person1.png'),
        asset('storage/images/person2.png'),
        asset('storage/images/person3.png')
    ];

    return ResponseHelper::success("success", ['data' => $images]);
}
public function storeLoginLog(Request $request, $status = 'failed')
{
    try {
        // Access 'logs' data from the request
        $data = $request->input('logs');  // You can access it like this
        if (!$data) {
          return ;
        }

        // Optionally, you can check if all necessary fields are present in the 'logs'
        $data['status'] = $status;  // Add the status to the log data

        // Create a new log entry
        $log = LoginLog::create($data);
        return $log;
    } catch (Exception $e) {
        // return ResponseHelper::error("Failed to store login log", 500, $e->getMessage());
    }
}
}