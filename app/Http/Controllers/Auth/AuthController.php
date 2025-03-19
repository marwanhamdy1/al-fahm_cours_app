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
use Illuminate\Support\Facades\Hash;
use App\Traits\ImageUploadTrait;
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
        return ResponseHelper::error( "verify code does not match", 500);
        }
        $token = Auth::login($user);
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

            $getParent = User::where("identity_id", $data['identity_id'])->first();

            if (!$getParent) {
                return ResponseHelper::error("Failed to complete profile: Identity ID not found", 404);
            }

            $data['parent_id'] = $getParent->id;
            unset($data['identity_id']);
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
            $image = $this->saveImage($request->image);
            $child = User::create([
                "first_name"    => $request->first_name,
                "last_name"     => $request->last_name,
                "username"     => $request->username,
                "password"      => Hash::make($request->password),
                "phone_number"  => $request->phone_number,
                "parent_id"     => $user->id,
                'child_type'    => $request->child_type,
                "color"     => $request->color,
                "date_of_birth"     => $request->date_of_birth,
                "image"     => $image,
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
        ]);

        // Find user by phone number
        $user = User::where('username', $request->username)->first();

        // Check if the password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::error("Invalid username or password", 401);
        }

        // Generate a new token for the user
        $token = Auth::login($user);

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
   public function image()
{
    $images = [
        asset('storage/images/person1.png'),
        asset('storage/images/person2.png'),
        asset('storage/images/person3.png')
    ];

    return ResponseHelper::success("success", ['data' => $images]);
}
}