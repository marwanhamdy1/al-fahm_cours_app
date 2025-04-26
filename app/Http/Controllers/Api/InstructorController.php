<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\InstructorRating;
use Illuminate\Http\Request;
use App\Traits\ImageUploadTrait;
use App\Helpers\ResponseHelper;
use App\Http\Resources\CourseResource;
use App\Http\Resources\InstructorRatingResources;
use Illuminate\Support\Facades\Hash;

class InstructorController extends Controller
{
    use ImageUploadTrait;
    public function index()
{
    $instructors = Instructor::latest()->get()->map(function ($instructor) {
        $instructor->image = asset('storage/' . $instructor->image);
        return $instructor; // ✅ This should be $instructor, not $instructors
    });

    return ResponseHelper::success('success', $instructors, 200);
}


    public function store(Request $request)
    {
        try{
      $validData=  $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'image' => 'required|image',
            'date_of_birth' => 'nullable|date',
            'bio' => 'nullable|string',
            'info' => 'nullable|string',
            'email' => 'nullable|email|unique:instructors,email',
            "phone_number"=>"nullable|unique:instructors,phone_number",
           'password'      => 'nullable|string|min:6', // يفضل تحديد حد أدنى للأمان
        ]);
          // Check if a new image is uploaded and process it
           // Hash the password if provided
        if (!empty($validData['password'])) {
            $validData['password'] = Hash::make($validData['password']);
        }
    if ($request->hasFile('image')) {
        $validData['image'] = $this->saveImage($request->image);
    }
        $instructor = Instructor::create($validData);

        return response()->json($instructor, 201);
    }catch(\Exception $e){
        return response()->json($e->getMessage(), 400);

        }
    }

    public function show(Instructor $instructor)
    {
        return ResponseHelper::success('success',$instructor, 200);
    }

    public function update(Request $request, Instructor $instructor)
    {
        try{

         $validData=  $request->validate([
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'image' => 'sometimes|image',
            'date_of_birth' => 'sometimes|date',
            'bio' => 'sometimes|string',
            'info' => 'nullable|string',
            'email' => 'sometimes|email|unique:instructors,email',
            'password' => 'sometimes|string|min:6',
            "phone_number" => "sometimes|string"
        ]);
         // Hash password if it's present
        if (array_key_exists('password', $validData)) {
            $validData['password'] = Hash::make($validData['password']);
        }
         if ($request->hasFile('image')) {
        $validData['image'] = $this->saveImage($request->image);
    }
        $instructor->update($validData);
        return response()->json($instructor, 200);
         }catch(\Exception $e){
        return response()->json($e->getMessage(), 400);

        }
    }

    public function destroy(Instructor $instructor)
    {
        $instructor->delete();
        return response()->json(null, 204);
    }
    public function instructorCourses($id){
        $courses= Course::with(['instructor', 'category'])->where("instructor_id" , $id)->get();
        return ResponseHelper::success('success', CourseResource::collection($courses), 200);
    }
    public function instructorRating($id){
        $rates = InstructorRating::with('user')->where('instructor_id', $id)->get();
        return ResponseHelper::success("success", InstructorRatingResources::collection($rates));
    }
    public function changeStatusReview($id, $status)
{
    // Validate the status input
    if (!in_array($status, [0, 1])) {
        return ResponseHelper::error("Invalid status value", 422);
    }

    // Find the rating or fail
    $rating = InstructorRating::find($id);
    if (!$rating) {
        return ResponseHelper::error("Rating not found", 404);
    }

    // Add authorization check (example using middleware, but could also be here)
    // if (!auth()->user()->can('moderate-ratings')) {
    //     return ResponseHelper::error("Unauthorized", 403);
    // }

    try {
        $rating->is_accept = $status;
        $rating->save(); // Using save() instead of update() to trigger model events

        // Optional: Log the action
        // activity()->log(auth()->user()->name." changed rating status to {$status}");

        return ResponseHelper::success("Status updated successfully", $rating);

    } catch (\Exception $e) {
        // Log the error if needed
        // \Log::error("Error updating rating status: ".$e->getMessage());

        return ResponseHelper::error("Failed to update status", 500);
    }
}
}