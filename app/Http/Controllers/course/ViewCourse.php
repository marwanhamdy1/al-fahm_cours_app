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
                 $data = Course::find($id)->with(['instructor','category'])->get();
                return ResponseHelper::success("success",  CourseResource::collection($data));
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
    public function departmentAndSessions($id)
{
    try {
        $attendedSessionIds = UserCourseSession::where('user_id', auth()->user()->id)
            ->pluck('course_session_id')
            ->toArray();

        $departments = Department::where('course_id', $id)
            ->with(['courseSessions'])
            ->get();

        // Manually format the response
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

        return ResponseHelper::success("success", $formattedDepartments
        );
    } catch (Exception $e) {
        return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
    }
}
        function detectLanguage($text)
        {
            return preg_match('/\p{Hebrew}/u', $text) ? 'he' : 'ar';
        }

}