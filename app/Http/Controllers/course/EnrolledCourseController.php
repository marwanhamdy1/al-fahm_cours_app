<?php

namespace App\Http\Controllers\course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EnrolledCourse;
use App\Models\Course;
use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreEnrolledCourseRequest;
use App\Http\Requests\GetMyCourseRequest;
use Exception;
use App\Http\Requests\AssignCourseRequest;
use App\Http\Resources\EnrolledCourseResource;
use App\Models\CourseSession;
use App\Models\UserCourseSession;


class EnrolledCourseController extends Controller
{
   public function store(StoreEnrolledCourseRequest $request)
    {
        try {
            $course = Course::find($request->course_id);
            if(!$course){
            return ResponseHelper::error("course not found" , 400);
            }
            $queryUserId = $this->checkChildAndPermission($request);
            $valid = EnrolledCourse::where('user_id', $queryUserId)->where("course_id", $request->course_id)->first();
            if ($valid) {
                return ResponseHelper::error("لقد تم التسجيل في هذه الدورة من قبل", 400);
            }
            EnrolledCourse::create([
                "user_id"          => $queryUserId,
                "course_id"        => $request->course_id,
                "remaining_amount" => $course->price,
                'is_event'=> $course->item_type =="course" ? 0:1
            ]);

            return ResponseHelper::success("تم التسجيل بنجاح");
        } catch (Exception $e) {
            return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
        }
    }
        public function destroy(Request $request,$id)
    {
        try {
            $queryUserId = $this->checkChildAndPermission($request);
            $course = EnrolledCourse::findOrFail($id); // Throws ModelNotFoundException if not found
            // if($course->is_event){

            // }
            if(!$course->is_event){
                 if(!in_array($course->status, ["on_basket", "pending"]) || $course->user_id != $queryUserId){
             return ResponseHelper::error("لم يتم العثور على دورة", 404);
            }
            }


            
            $course->delete();

            return ResponseHelper::success("تم حذف الدورة");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::error("لم يتم العثور على دورة", 404);

        } catch (Exception $e) {
            return ResponseHelper::error("حدث خطأ أثناء محاولة حذف الدورة: " . $e->getMessage(), 500);
        }
    }
    private function updateCourseAndEnrolled($enrolledCourseId) {
        try {

        } catch (Exception $e) {
            return ResponseHelper::error("حدث خطاء" . $e->getMessage(), 500);
        }
    }
    public function enrolledToPending(Request $request)
{
    try {
        $queryUserId = $this->checkChildAndPermission($request);

        // 1. Get all enrolled courses in the basket
        $enrolledCourses = EnrolledCourse::where("is_event",0)->where('user_id', $queryUserId)
            ->where("status", "on_basket")
            ->with('course') // Eager load the course relationship (if defined)
            ->get();

        if ($enrolledCourses->isEmpty()) {
            return ResponseHelper::error("لم يتم العثور على دورات مسجلة", 404);
        }

        // 2. Increment signed_people for each related course
        foreach ($enrolledCourses as $enrolledCourse) {
            if ($enrolledCourse->course) { // Check if course exists
                $enrolledCourse->course->increment('signed_people'); // +1
            }
        }

        // 3. Update all matching records to "pending"
        $updatedCount = EnrolledCourse::where("is_event",0)->where('user_id', $queryUserId)
            ->where("status", "on_basket")
            ->update(['status' => "pending"]);

        return ResponseHelper::success("تم تحديث الحالة لجميع الدورات المسجلة");

    } catch (\Exception $e) {
        return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
    }
}

    public function assignCourse(AssignCourseRequest $request)
    {
        try {
            $course = EnrolledCourse::findOrFail($request->id);
            $user = User::findOrFail(id: $request->child_id);

            if ($user->parent_id !== auth()->user()->id) {
                return ResponseHelper::error("ليس لديك صلاحية لتسجيل هذا الطفل", 403);
            }

            $course->update([
                "assigned_by" => $request->child_id,
            ]);

            return ResponseHelper::success("تم تسجيل ابنك {$user->first_name} في الكورس بنجاح");

        } catch (Exception $e) {
            return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
        }
    }
    public function getMyCourses(GetMyCourseRequest $request) {
    try {
        $queryUserId = $this->checkChildAndPermission($request);
        // Get the status filter from the request (if provided)
        $status = $request->input('status'); // Single status (e.g., 'pending')
        $statuses = $request->input('statuses', []); // Array of statuses (e.g., ['pending', 'approved'])

        // Start building the query
        $query = EnrolledCourse::where("is_event",0)->where(function($q) use ($queryUserId) {
            $q->where('assigned_by', $queryUserId)
              ->orWhere("user_id", $queryUserId);
        });

        // Apply status filter if provided
        if ($status) {
            $query->where('status', $status); // Filter by single status
        } elseif (!empty($statuses)) {
            $query->whereIn('status', $statuses); // Filter by multiple statuses
        }

        $data = $query->get();

        $data->transform(function ($enrolledCourse) use($queryUserId) {
            $course = $enrolledCourse->course;
            if ($course) {
                $attendedSessions = UserCourseSession::query()
                ->where('user_id', $queryUserId)
                ->whereHas('session', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->count();

                // Avoid division by zero
                $attendancePercentage = $course->session_count > 0
                    ? round(($attendedSessions / $course->session_count) * 100, 2)
                    : 0;

                $enrolledCourse->attendance_percentage = $attendancePercentage;
            }
            return $enrolledCourse;
        });

        return ResponseHelper::success("success", EnrolledCourseResource::collection($data));
    } catch (Exception $e) {
        return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
    }
}
     /**
     * Check if the authenticated user has permission to access child data
     *
     * @param Request $request
     * @return int $queryUserId
     * @throws \Exception
     */
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