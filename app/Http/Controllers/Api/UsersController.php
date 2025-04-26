<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\ResponseHelper;
use App\Http\Requests\AddChildRequest;
use App\Http\Requests\AddParentRequest;
use App\Http\Resources\UsersInfoForDashBoardResource;
use App\Http\Requests\UpdateUserInfoRequest;
use Exception;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function getUsers(){
        $users = User::with(['enrolledCourses.course'])->where("role", "child")->latest()->get();
        return ResponseHelper::success("success",UsersInfoForDashBoardResource::collection($users , "child"));
    }
    public function getParents(){
        $users = User::with(['enrolledCourses.course'])->withCount('children')->where("role", "parent")->latest()->get();
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
        public function changeStatusUser($id)
        {
            try {
                $user = User::findOrFail($id);
                $user->update([
                    'status' => $user->status == 1 ? 0 : 1
                ]);

                $statusText = $user->status == 1 ? 'نشط' : 'غير نشط';

                return ResponseHelper::success("تم تغيير حالة المستخدم إلى: $statusText");
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return ResponseHelper::error("حدث خطأ أثناء محاولة تغيير الحالة: " . $e->getMessage(), 500);
            }
        }
    public function deleteUser($id){
        try{
        $user = User::findOrFail($id);
        $user->delete();
        return ResponseHelper::success("تم حذف المستخدم");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::error("حدث خطأ أثناء محاولة حذف : " . $e->getMessage(), 500);

        }
    }
    public function addParent(AddParentRequest $request){
    try {
       $parent = User::create($request->validated());
        return ResponseHelper::success("Parent profile created successfully", $parent);
    } catch (Exception $e) {
        return ResponseHelper::error("Failed to complete profile", 500, $e->getMessage());
    }
    }
    public function addChild(AddChildRequest $request,$parent_id){
    try {
            // $image = $this->saveImage($request->image);
            $child = User::create([
                "first_name"    => $request->first_name,
                "last_name"     => $request->last_name ?? $request->first_name,
                "username"     => $request->username,
                "password"      => Hash::make($request->password),
                "phone_number"  => $request->phone_number,
                "parent_id"     =>$parent_id,
                'child_type'    => $request->child_type,
                "color"     => $request->color,
                "date_of_birth"     => $request->date_of_birth,
                "identity_id"     => $request->identity_id,
                "image"     => $request->image,
                "role"          => "child",
            ]);

            return ResponseHelper::success("Child Added Successfully",  $child);
    } catch (Exception $e) {
        return ResponseHelper::error("Failed to complete profile", 500, $e->getMessage());
    }
    }
}