<?php

namespace App\Http\Controllers\course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Rating;
use App\Models\Course;
use App\Http\Requests\RatingRequest;
use App\Http\Resources\RatingCourseResource;
use App\Helpers\ResponseHelper;
class RatingController extends Controller
{
    public function store(RatingRequest $request){
        try{
            DB::beginTransaction();
            $course = Course::findOrFail($request->course_id);

        $rating = Rating::create([
            'course_id' => $request->course_id,
            'user_id'   => auth()->user()->id,
            'rating'    => $request->rating,
            'comment'   => $request->comment,
        ]);

        // Update rating count & sum in `courses` table
        $course->increment('rating_count');
        $course->increment('rating_sum', $request->rating);
        DB::commit();
        return ResponseHelper::success("success", $rating);
         } catch (\Exception $e) {
        DB::rollBack();
        // return response()->json(['error' => 'Failed to submit rating', 'details' => $e->getMessage()], 500);
        return ResponseHelper::error("Failed to submit rating", 400,$e->getMessage());

    }
    }
    public function index($id){
        try{
        $rates = Rating::with('user')->where('course_id', $id)->get();
        return ResponseHelper::success("success", RatingCourseResource::collection($rates));
         } catch (\Exception $e) {
        // return response()->json(['error' => 'Failed to submit rating', 'details' => $e->getMessage()], 500);
        return ResponseHelper::error("Failed to submit rating", 400,$e->getMessage());
    }
    }
}