<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\EnrolledCourse;
use App\Helpers\ResponseHelper;
use Exception;
use App\Http\Resources\EnrolledCourseResource;
use App\Models\User;
use App\Models\PointHistory;
use App\Models\UserCourseSession;
class EventsController extends Controller
{
    public function index(Request $request){
         try {
        $queryUserId = $this->checkChildAndPermission($request);
        // Get the status filter from the request (if provided)
        $status = $request->input('status'); // Single status (e.g., 'pending')
        $statuses = $request->input('statuses', []); // Array of statuses (e.g., ['pending', 'approved'])

        // Start building the query
        $query = EnrolledCourse::where("is_event",1)->where(function($q) use ($queryUserId) {
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
    public function payEventToApprove(Request $request){
        try {
            $price=0;
        $queryUserId = $this->checkChildAndPermission($request);
        $user = User::find($queryUserId);
        // 1. Get all enrolled courses in the basket
        $enrolledCourses = EnrolledCourse::where("is_event",1)->where('user_id', $queryUserId)
            ->where("status", "on_basket")
            ->with('course') // Eager load the course relationship (if defined)
            ->get();

        if ($enrolledCourses->isEmpty()) {
            return ResponseHelper::error("لم يتم العثور على دورات مسجلة", 404);
        }

        foreach ($enrolledCourses as $enrolledCourse) {
            if ($enrolledCourse->course) {
                $price += $enrolledCourse->course->price;

                if ($price > $user->points) {
                    return ResponseHelper::error("لا تملك عدد كافٍ من النقاط للتسجيل في الايفينت: {$price}", 400);
                }
            }
        }

        // 3. All good → now increment signed_people
        foreach ($enrolledCourses as $enrolledCourse) {
            if ($enrolledCourse->course) {
                $enrolledCourse->course->increment('signed_people');
            }
        }
        $user->decrement('points', $price);
        PointHistory::create([
            'user_id' => $queryUserId,
            'points' => $price * -1,
            'description' => "تسجيل دورة ايفينت"
        ]);
        // 3. Update all matching records to "pending"
        $updatedCount = EnrolledCourse::where("is_event",1)->where('user_id', $queryUserId)
            ->where("status", "on_basket")
            ->update(['status' => "approved",'payment_status' => "paid"]);

        return ResponseHelper::success("تم تحديث الحالة لجميع الدورات المسجلة");

    } catch (\Exception $e) {
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