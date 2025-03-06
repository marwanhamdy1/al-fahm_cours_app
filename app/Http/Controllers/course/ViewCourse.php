<?php

namespace App\Http\Controllers\course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Models\Course;
use App\Models\Department;
use App\Http\Resources\CourseResource;
use App\Http\Resources\DepartmentResource;
use Exception;
use Illuminate\Support\Facades\DB;

class ViewCourse extends Controller
{

    public function index()
        {
            try {
                //   // تشغيل تسجيل الاستعلامات
                // DB::enableQueryLog();
                $data = Course::with(['instructor'])->get(); // Using `all()` instead of `get()` for simplicity
                //  // جلب الاستعلامات المسجلة
                // $queries = DB::getQueryLog();
                // $queryCount = count($queries); // عدد الاستعلامات

                return ResponseHelper::success("success", CourseResource::collection($data));
            } catch (Exception $e) {
                return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
            }
        }
    public function departmentAndSessions($id){
        try {
            $data = Department::where('course_id', $id)->with(['courseSessions'])->get();
            return ResponseHelper::success("success", DepartmentResource::collection($data));
        } catch (Exception $e) {
            return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
        }
    }

}