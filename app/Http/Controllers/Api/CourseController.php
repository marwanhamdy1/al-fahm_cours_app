<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCourseRequest;
use App\Traits\ImageUploadTrait;
use App\Helpers\ResponseHelper;


class CourseController extends Controller
{
    use ImageUploadTrait;
    public function index()
    {
        return ResponseHelper::success('success',Course::all(), 200);
    }

   public function store(StoreCourseRequest $request)
{
    try{
    $data = $request->validated();
    if($request->hasFile('image')) {
        $data['image'] = $this->saveImage($request->image);
    }
    $course = Course::create( $data);

    return response()->json([
        'success' => true,
        'message' => 'Course created successfully',
        'data' => $course
    ], 201);
    }catch(\Exception $e){
         return response()->json([
        'success' => False,
        'message' => $e->getMessage(),
    ], 201);

    }
}

    public function show(Course $course)
    {
    return ResponseHelper::success('success',$course, 200);
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'sometimes|string',
            'title_he' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'earnings_point' => 'nullable|integer',
            'address' => 'sometimes|string',
            'address_he' => 'sometimes|string',
            'description' => 'nullable|string',
            'description_he' => 'nullable|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'max_people' => 'sometimes|integer|min:1',
            'signed_people' => 'nullable|integer|min:0',
            'age_range' => 'nullable|string',
            'session_count' => 'nullable|integer|min:1',
            'category_id' => 'sometimes|exists:categories,id',
            'instructor_id' => 'sometimes|exists:instructors,id',
            'active' => 'sometimes|boolean',
            'type' => 'nullable|string',
        ]);

        $course->update($request->all());

        return response()->json($course, 200);
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json(null, 204);
    }
}