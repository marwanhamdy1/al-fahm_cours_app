<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\EnrolledCourse;
use App\Models\UserCourseSession;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\AddUserToCourseRequest;
use App\Traits\ImageUploadTrait;
use App\Helpers\ResponseHelper;
use App\Http\Resources\CourseResource;
use App\Http\Resources\UserCourseResources;
use App\Http\Resources\UsersEnrolledInCourseResources;
use App\Http\Resources\PaymentsResource;
use App\Http\Resources\RatingCourseResource;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class CourseController extends Controller
{
    use ImageUploadTrait;

    public function changeCourseStatus($id)
{
    $course = Course::findOrFail($id); // Get the actual model or fail

    $course->active = $course->active == 0 ? 1 : 0;
    $course->save(); // Save the updated status

    return ResponseHelper::success('success', new CourseResource($course), 200);
}

    public function index()
    {
        $data =Course::with(['instructor', 'category'])->latest()->get();
        return ResponseHelper::success('success',CourseResource::collection($data), 200);
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

    public function show($id)
    {
    $data =Course::with(['instructor', 'category'])->where('id', $id)->first();
    return ResponseHelper::success('success',new CourseResource($data), 200);
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
    public function usersCourses($courseId){
        try {
        // Start building the query
        $query = EnrolledCourse::with(relations: ['user','assignedBy'])->where("status","approved",)->where("course_id",$courseId)->get();

        return ResponseHelper::success("success", UserCourseResources::collection($query) );
    } catch (\Exception $e) {
        return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
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
        // Step 1: Validate input
    $validator = Validator::make($request->only(['course_session_id','user_ids']), [
    'course_session_id' => 'required|exists:course_sessions,id',
    'user_ids' => 'required|array|min:1',
    'user_ids.*' => 'exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
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
                ]);
            }
        }

        // Step 3: Return response
        return response()->json([
            'status' => true,
            'message' => 'Attendance marked successfully.',
            'created_count' => count($created),
            'created_records' => $created, // optional
        ]);
    }
    public function getUsersEnrolledInCourse($courseId){
          try {
        // Start building the query
        $query = EnrolledCourse::with(relations: ['user','assignedBy'])->where("course_id",$courseId)
        ->whereIn("status",["approved","pending"])->get();

        return ResponseHelper::success("success", UsersEnrolledInCourseResources::collection($query) );
    } catch (\Exception $e) {
        return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
    }
    }
    public function makePaymentForUser(Request $request)
    {
        $amount = (float) $request->amount;
        $enrollCourse = EnrolledCourse::findOrFail($request->id);

        $currentPaid = (float) $enrollCourse->amount_paid;
        $remaining = (float) $enrollCourse->remaining_amount;
        $point = $enrollCourse->course->earnings_point;
        $user = User::find($enrollCourse->user_id);
        $newPaid = $currentPaid + $amount;
        if ($newPaid >= $remaining) {
            // Full payment completed or overpaid
            $enrollCourse->update([
                'amount_paid' => $remaining,
                'remaining_amount' => 0,
                'status' => 'approved',
                'payment_status' => 'paid',
            ]);

       // Give points only when full payment is completed
        $user->points += $point;
        $user->save();
        } else {
            // Partial payment
            $enrollCourse->update([
                'amount_paid' => $newPaid,
                'remaining_amount' => $remaining - $amount,
                'status' => 'approved',
                'payment_status' => 'partially_paid',
            ]);
        }

        return ResponseHelper::success('تم تحديث الدفع بنجاح');
    }
    // add user to course from dashboard

    public function addUserToCourse(AddUserToCourseRequest $request)
    {
        try {
            // Get the course and user
            $course = Course::findOrFail($request->course_id);
            $user = User::findOrFail($request->user_id);

            // Check if user is already enrolled in this course
            $existingEnrollment = EnrolledCourse::where('user_id', $user->id)
                ->where('course_id', $request->course_id)
                ->first();

            if ($existingEnrollment) {
                return ResponseHelper::error("User is already enrolled in this course", 400);
            }

            // Check if course is active
            if (!$course->active) {
                return ResponseHelper::error("Course is not active", 400);
            }

            // Calculate remaining amount
            $amountPaid = $request->amount_paid ?? 0;
            $remainingAmount = $course->price - $amountPaid;

            // Determine payment status
            $paymentStatus = $request->payment_status ?? 'unpaid';
            if ($amountPaid > 0 && $amountPaid < $course->price) {
                $paymentStatus = 'partially_paid';
            } elseif ($amountPaid >= $course->price) {
                $paymentStatus = 'paid';
                $remainingAmount = 0;
            }

            // Create enrollment
            $enrollment = EnrolledCourse::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'assigned_by' => $request->assigned_by ?? null,
                'amount_paid' => $amountPaid,
                'remaining_amount' => $remainingAmount,
                'payment_status' => $paymentStatus,
                'status' => $paymentStatus === 'paid' ? 'approved' : 'pending',
                'is_event' => $course->item_type == "course" ? 0 : 1
            ]);

            // Add points if fully paid
            if ($paymentStatus === 'paid' && $course->earnings_point > 0) {
                $user->points += $course->earnings_point;
                $user->save();
            }

            return ResponseHelper::success("User successfully enrolled in course", new UserCourseResources($enrollment->load(['user', 'assignedBy'])));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::error("Course or User not found", 404);
        } catch (\Exception $e) {
            return ResponseHelper::error("Failed to enroll user: " . $e->getMessage(), 500);
        }
    }
    public function allPayments(){
        try{
        $query = EnrolledCourse::with(relations: ['user','assignedBy','course'])
        ->whereIn("payment_status",["partially_paid","paid"])->get();
        return ResponseHelper::success("success", PaymentsResource::collection($query) );
        } catch (\Exception $e) {
        return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
    }
    }
    public function courseRating($id){
        try{
        $rates = Rating::with('user')->where('course_id', $id)->get();
        return ResponseHelper::success("success", RatingCourseResource::collection($rates));
         } catch (\Exception $e) {
        // return response()->json(['error' => 'Failed to submit rating', 'details' => $e->getMessage()], 500);
        return ResponseHelper::error("Failed to submit rating", 400,$e->getMessage());
    }
    }
     public function changeStatusReview($id, $status)
{
    // Validate the status input
    if (!in_array($status, [0, 1])) {
        return ResponseHelper::error("Invalid status value", 422);
    }

    // Find the rating or fail
    $rating = Rating::find($id);
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
