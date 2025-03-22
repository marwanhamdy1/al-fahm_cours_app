<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
class CourseSessionController extends Controller
{
    public function index()
    {
        return ResponseHelper::success('success',CourseSession::all(), 200);
    }

    public function store(Request $request)
    {
        try{
        $valid =$request->validate([
            'course_id' => 'required|exists:courses,id',
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string',
            'title_he' => 'required|string',
        ]);

        $courseSession = CourseSession::create($valid);

        return response()->json($courseSession, 201);
    }catch(\Exception $e){
        return response()->json(['message'=>$e->getMessage()], 400);

    }
    }

    public function show(CourseSession $courseSession)
    {
        return ResponseHelper::success('success',$courseSession, 200);
    }

    public function update(Request $request, CourseSession $courseSession)
    {
        $courseSession->update($request->all());
        return response()->json($courseSession, 200);
    }

    public function destroy(CourseSession $courseSession)
    {
        $courseSession->delete();
        return response()->json(null, 204);
    }
}