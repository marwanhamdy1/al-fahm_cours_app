<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\ResponseHelper;
use App\Http\Resources\UsersInfoForDashBoardResource;
use App\Http\Requests\UpdateUserInfoRequest;
use Exception;
class UsersController extends Controller
{
    public function getUsers(){
        $users = User::with(['enrolledCourses.course'])->where("role", "child")->get();
        return ResponseHelper::success("success",UsersInfoForDashBoardResource::collection($users , "child"));
    }
    public function getParents(){
        $users = User::with(['enrolledCourses.course'])->where("role", "parent")->get();
        return ResponseHelper::success("success",UsersInfoForDashBoardResource::collection($users ,"parent"));
    }
   public function getChildrenParent($id)
{
    try {
        // Get the parent with their children
        $parent = User::with(['children' => function($query) {
            $query->where('role', 'child'); // Ensure only children are returned
        }])->where('id', $id)
          ->where('role', 'parent') // Ensure the ID belongs to a parent
          ->first();

        if (!$parent) {
            return ResponseHelper::error("Parent not found", 404);
        }

        if ($parent->children->isEmpty()) {
            return ResponseHelper::error("No children found for this parent", 404);
        }

        return ResponseHelper::success(
            "Children found",
            $parent->children
        );

    } catch (\Exception $e) {
        return ResponseHelper::error("Error retrieving children: " . $e->getMessage(), 500);
    }
}

    public function updateUserInfo(UpdateUserInfoRequest $request ,$id)
    {
        try{
        $user = User::findOrFail($id);

        $data = $request->validated();

        $user->update($data);

        return response()->json([
            'message' => 'User information updated successfully',
            'user' => new UsersInfoForDashBoardResource($user,"child")
        ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::error("لم يتم العثور على دورة", 404);

        } catch (Exception $e) {
            return ResponseHelper::error("حدث خطأ أثناء محاولة حذف الدورة: " . $e->getMessage(), 500);
        }
        }
}