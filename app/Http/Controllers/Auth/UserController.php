<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PointHistory;
use App\Helpers\ResponseHelper;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Hash;
use App\Traits\ImageUploadTrait;
use App\Http\Resources\PointHistoryResources;
use Illuminate\Support\Facades\Validator;
use Exception;
class UserController extends Controller
{
    use ImageUploadTrait;
    public function update(UserUpdateRequest $request){
    $user = auth()->user(); // Get the authenticated user

    $user->update($request->validated()); // Update only validated fields

    return ResponseHelper::success("User information updated successfully", $user);
    }
    public function changePassword(Request $request){
        $user = auth()->user(); // Get authenticated user

        // Validate request manually to catch validation errors
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
            'old_password' => $user->password ? 'required|string|min:8' : 'nullable', // Require old password if user has one
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->first(), 422);
        }

        try {
            // If user has an existing password, verify old password before updating
            if ($user->password && !Hash::check($request->old_password, $user->password)) {
                return ResponseHelper::error("Old password is incorrect", 422);
            }

            // Hash the new password and update it
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return ResponseHelper::success("Password updated successfully", []);

        } catch (\Exception $e) {
            return ResponseHelper::error("Failed to update password: " . $e->getMessage(), 500);
        }
    }
    public function updateImage(Request $request){
        // Manually validate to catch validation errors
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Limit to 2MB and specific formats
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->first(), 422);
        }

        try {
            $user = auth()->user();

            // Save the image (Assuming `saveImage` is a function for storing images)
            $image = $this->saveImage($request->file('image'));

            // Update the user's image
            $user->update([
                'image' => $image,
            ]);

            return ResponseHelper::success("Image updated successfully", $user);
        } catch (\Exception $e) {
            return ResponseHelper::error("Failed to update image: " . $e->getMessage(), 500);
        }
    }
    public function getMyPoints(Request $request){
        try{
        $userId = $this->checkChildAndPermission($request);
        $pointUser = User::find($userId);
        $point = PointHistory::where('user_id', auth()->user()->id)->get();
        return ResponseHelper::success("success", ["data"=>PointHistoryResources::collection($point) , "total_points"=>$pointUser->points]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return ResponseHelper::error("User not found", 404);
    } catch (Exception $e) {
        return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
    }
    }
    public function AddPointsTest($point){
        // Create a new PointHistory record
        $pointHistory = PointHistory::create([
            'user_id' => auth()->user()->id,
            'points' => $point,
            'description' => "for test bro only",
        ]);

        // Retrieve the authenticated user
        $user = auth()->user();

        // Add the points from the created PointHistory record
        $user->points = $user->points + $pointHistory->points;
        $user->save(); // Don't forget to save the updated user points

        return ResponseHelper::success("success", new PointHistoryResources($pointHistory));
    }
    private function checkChildAndPermission($request) {
        $user = auth()->user();
        $userId = $user->id;
        $queryUserId = $request->has('child_id') ? $request->child_id : $userId;
        // Skip the check if the user is a super_admin or admin
        if (in_array($user->role, ['super_admin', 'admin', 'Admin'])) {
            return $queryUserId;
        }

        if ($request->has('child_id') && !$user->children()->where('id', $queryUserId)->exists()) {
            throw new Exception("ليس لديك صلاحية لتسجيل هذا الطفل", 403);
        }

        return $queryUserId;

    }
}