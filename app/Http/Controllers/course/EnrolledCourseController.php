<?php

namespace App\Http\Controllers\course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EnrolledCourse;
use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreEnrolledCourseRequest;
use App\Http\Requests\GetMyCourseRequest;
use Exception;
use App\Http\Requests\AssignCourseRequest;
use App\Http\Resources\EnrolledCourseResource;
use App\Models\UserCourseSession;


class EnrolledCourseController extends Controller
{
   public function store(StoreEnrolledCourseRequest $request)
    {
        try {
            $queryUserId = $this->checkChildAndPermission($request);
            $valid = EnrolledCourse::where('user_id', $queryUserId)->where("course_id", $request->course_id)->first();
            if ($valid) {
                return ResponseHelper::error("لقد تم التسجيل في هذه الدورة من قبل", 400);
            }
            EnrolledCourse::create([
                "user_id"          => $queryUserId,
                "course_id"        => $request->course_id,
            ]);
            return ResponseHelper::success("تم التسجيل بنجاح");
        } catch (Exception $e) {
            return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
        }
    }
    public function enrolledToPending(Request $request) {
    try {
            $queryUserId = $this->checkChildAndPermission($request);
        $updated = EnrolledCourse::where('user_id', $queryUserId)->where("status", "on_basket")
            ->update(['status' => "pending"]); // Update all matching records

        if (!$updated) {
            return ResponseHelper::error("لم يتم العثور على دورات مسجلة", 404);
        }

        return ResponseHelper::success("تم تحديث الحالة لجميع الدورات المسجلة");
    } catch (Exception $e) {
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
        try{
        $queryUserId = $this->checkChildAndPermission($request);
        $data = EnrolledCourse::where('assigned_by',$queryUserId)
        ->orWhere("user_id", $queryUserId)->get();
        $data->transform(function ($enrolledCourse) {
            $course = $enrolledCourse->course;
            if ($course) {
            $attendedSessions = UserCourseSession::where('user_id', auth()->user()->id)
                ->where('course_session_id', $course->id)
                ->count();

            // Avoid division by zero
            $attendancePercentage =$course->session_count > 0
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
        if (in_array($user->role, ['super_admin', 'admin'])) {
            return $queryUserId;
        }

        if ($request->has('child_id') && !$user->children()->where('id', $queryUserId)->exists()) {
            throw new Exception("ليس لديك صلاحية لتسجيل هذا الطفل", 403);
        }

        return $queryUserId;

    }
}