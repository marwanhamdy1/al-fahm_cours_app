<?php

namespace App\Http\Controllers\Instructors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\EnrolledCourse;
use App\Models\UserCourseSession;
use App\Models\CourseSession;
use App\Http\Resources\UserCourseResources;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class InstructorsController extends Controller
{
    public function myCourse()
    {
        try {
            $instructor = Auth::guard('instructor')->user();
            $courses = Course::where('instructor_id', $instructor->id)
                          ->latest()
                          ->get();

            return ResponseHelper::success("Courses retrieved successfully", $courses);
        } catch (\Exception $e) {
            return ResponseHelper::error("Error retrieving courses: " . $e->getMessage(), 500);
        }
    }

    public function usersInCourse($courseId)
    {
        try {
            // Verify the course belongs to the instructor
            $instructor = Auth::guard('instructor')->user();
            $course = Course::where('id', $courseId)
                           ->where('instructor_id', $instructor->id)
                           ->first();

            if (!$course) {
                return ResponseHelper::error("Course not found or not authorized", 404);
            }

            $enrolledUsers = EnrolledCourse::with(['user', 'assignedBy'])
                ->where("status", "approved")
                ->where("course_id", $courseId)
                ->get();

            return ResponseHelper::success("Enrolled users retrieved successfully",
                UserCourseResources::collection($enrolledUsers));
        } catch (\Exception $e) {
            return ResponseHelper::error("Error retrieving enrolled users: " . $e->getMessage(), 500);
        }
    }
     public function usersCoursesBySession($courseId, $courseSessionId)
    {
        try {
            // Get all approved enrolled users in this course with related user data
            $enrolledUsers = EnrolledCourse::with(['user', 'assignedBy'])
                ->where('status', 'approved')
                ->where('course_id', $courseId)
                ->get();

            // Get all user IDs who attended the specific session
            $attendedUserIds = UserCourseSession::where('course_session_id', $courseSessionId)
                ->pluck('user_id')
                ->toArray();

            // Add attended_session field to each enrolled user
            $enrolledUsers->transform(function ($item) use ($attendedUserIds) {
                $item->attended_session = in_array($item->user_id, $attendedUserIds) ? 1 : 0;
                return $item;
            });

            return ResponseHelper::success("success", UserCourseResources::collection($enrolledUsers));
        } catch (\Exception $e) {
            return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
        }
    }

    public function makeAttendForUser(Request $request)
    {
        try {
            // Step 1: Validate input
            $validator = Validator::make($request->only(['course_session_id', 'user_ids']), [
                'course_session_id' => 'required|exists:course_sessions,id',
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'exists:users,id',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error("Validation error", 422, $validator->errors());
            }

            // Verify the session belongs to instructor's course
            $instructor = Auth::guard('instructor')->user();
            $sessionCourse = CourseSession::find($request->course_session_id)
                                ->course()
                                ->where('instructor_id', $instructor->id)
                                ->exists();

            if (!$sessionCourse) {
                return ResponseHelper::error("Unauthorized to mark attendance for this session", 403);
            }

            $courseSessionId = $request->course_session_id;
            $userIds = $request->user_ids;
            $created = [];

            foreach ($userIds as $userId) {
                // Step 2: Prevent duplicate attendance
                $exists = UserCourseSession::where('user_id', $userId)
                    ->where('course_session_id', $courseSessionId)
                    ->exists();

                if (!$exists) {
                    $created[] = UserCourseSession::create([
                        'user_id' => $userId,
                        'course_session_id' => $courseSessionId,
                        'marked_by' => $instructor->id, // Track who marked attendance
                        'attended_at' => now(),
                    ]);
                }
            }

            // Step 3: Return response
            return ResponseHelper::success("Attendance marked successfully", [
                'created_count' => count($created),
                'created_records' => $created,
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error("Error marking attendance: " . $e->getMessage(), 500);
        }
    }
}