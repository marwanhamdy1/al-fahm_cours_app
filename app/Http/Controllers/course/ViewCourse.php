<?php

namespace App\Http\Controllers\course;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\UserCourseSession;
use App\Models\Department;
use App\Http\Resources\CourseResource;
use Exception;
use Illuminate\Support\Facades\DB;

class ViewCourse extends Controller
{


    public function indexEvents(Request $request) {
            try {
                $query = Course::with(['instructor', 'category'])->where('item_type', 'event');

                if ($request->has('search')) {
                    $search = $request->input('search');
                    $isHebrew = $this->detectLanguage($search) == 'he';

                    $query->where(function($q) use ($search, $isHebrew) {
                        if ($isHebrew) {
                            $q->where("title_he", 'LIKE', "%{$search}%")
                              ->orWhere("description_he", 'LIKE', "%{$search}%");
                        } else {
                            $q->where("title", 'LIKE', "%{$search}%")
                              ->orWhere("description", 'LIKE', "%{$search}%");
                        }
                    });
                }

                $data = $query->get();
                return ResponseHelper::success("success", CourseResource::collection($data));
            } catch (Exception $e) {
                return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
            }
        }


    public function index(Request $request)
        {
            try {

                // DB::enableQueryLog();
                 $query = Course::with(['instructor', 'category']);

                if ($request->has('search')) {
                    if($this->detectLanguage($request->input('search')) == 'he'){
                        $search = $request->input('search');
                        $query->where("title_he", 'LIKE', "%{$search}%")
                        ->orWhere("description_he", 'LIKE', "%{$search}%");
                    }else{
                        $search = $request->input('search');
                        $query->where("title", 'LIKE', "%{$search}%")
                        ->orWhere("description", 'LIKE', "%{$search}%");
                    }
                }

                $data = $query->get();
                // $queries = DB::getQueryLog();
                // $queryCount = count($queries);

                return ResponseHelper::success("success", CourseResource::collection($data));
            } catch (Exception $e) {
                return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
            }
        }
        public function show($id){
            try{
                $data = Course::with(['instructor', 'category'])->find($id);
                return ResponseHelper::success("success",  new CourseResource($data));
                } catch (Exception $e) {
                    return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
                }
        }
        public function indexByCategory($id){
               try {

                // DB::enableQueryLog();
                $data = Course::where('category_id',$id)->with(['instructor'])->get(); // Using `all()` instead of `get()` for simplicity
                // $queries = DB::getQueryLog();
                // $queryCount = count($queries);

                return ResponseHelper::success("success", CourseResource::collection($data));
            } catch (Exception $e) {
                return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
            }
        }
    public function departmentAndSessions(Request $request, $id)
{
    try {
        if (!auth()->check()) {
            return ResponseHelper::error("Unauthenticated", 401);
        }

        $userId = $this->checkChildAndPermission($request); // Use `$this->` to call private method

        $attendedSessionIds = UserCourseSession::where('user_id', $userId)
            ->pluck('course_session_id')
            ->toArray();

        $departments = Department::where('course_id', $id)
            ->with('courseSessions')
            ->get();

        $formattedDepartments = $departments->map(function ($department) use ($attendedSessionIds) {
            return [
                'id' => $department->id,
                'title' => $department->title,
                'session' => $department->courseSessions->map(function ($session) use ($attendedSessionIds) {
                    return [
                        'id' => $session->id,
                        'title' => $session->title,
                        'description' => $session->description,
                        'attends' => in_array($session->id, $attendedSessionIds) ? 1 : 0
                    ];
                }),
            ];
        });

        return ResponseHelper::success("success", $formattedDepartments);

    } catch (Exception $e) {
        return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
    }
}
        function detectLanguage($text)
        {
            return preg_match('/\p{Hebrew}/u', $text) ? 'he' : 'ar';
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