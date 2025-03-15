<?php

namespace App\Http\Controllers\course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EnrolledCourse;
use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreEnrolledCourseRequest;
use Exception;
use App\Http\Requests\AssignCourseRequest;
use App\Http\Resources\EnrolledCourseResource;
use App\Models\UserCourseSession;
use Illuminate\Support\Facades\DB;


class EnrolledCourseController extends Controller
{
   public function store(StoreEnrolledCourseRequest $request)
    {
        try {
            $valid = EnrolledCourse::where('user_id', auth()->user()->id)->where("course_id", $request->course_id)->first();
            if ($valid) {
                return ResponseHelper::error("لقد تم التسجيل في هذه الدورة من قبل", 400);
            }
            EnrolledCourse::create([
                "user_id"          => auth()->user()->id,
                "course_id"        => $request->course_id,
            ]);
            return ResponseHelper::success("تم التسجيل بنجاح");
        } catch (Exception $e) {
            return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
        }
    }
    public function enrolledToPending() {
    try {
        $updated = EnrolledCourse::where('user_id', auth()->user()->id)
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
    public function getMyCourses(){
        try{
        $data = EnrolledCourse::where('assigned_by', auth()->user()->id)
        ->orWhere("user_id", auth()->user()->id)->get();
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
}