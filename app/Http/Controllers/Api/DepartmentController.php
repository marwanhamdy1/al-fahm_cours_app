<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Resources\DepartmentResource;

class DepartmentController extends Controller
{
    public function index($id)
    {
        $data = Department::where('course_id', $id)->with(['courseSessions'])->get();

        return response()->json( DepartmentResource::collection($data), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'title_he' => 'required|string',
            'course_id' => 'required|exists:courses,id',
        ]);

        $department = Department::create($request->all());

        return response()->json($department, 201);
    }

    public function show(Department $department)
    {
        return response()->json($department, 200);
    }

    public function update(Request $request, Department $department)
    {
        $department->update($request->all());
        return response()->json($department, 200);
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(null, 204);
    }
}