<?php

namespace App\Http\Controllers\course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InstructorRating;
use App\Models\Instructor;
use App\Helpers\ResponseHelper;
class InstructorRatingController extends Controller
{
    //
    public function  index($id){
        $rates = InstructorRating::where('instructor_id', $id)
        ->where("is_accept",1)->get();
        return ResponseHelper::success("success", $rates);
    }
    public function store(Request $request, $id)
    {
        try {
            $instructor = Instructor::findOrFail($id);
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string',
            ]);

            $rating = InstructorRating::create([
                'instructor_id' => $instructor->id,
                'user_id' => auth()->user()->id,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            return response()->json(['message' => 'Rating added successfully', 'data' => $rating], 201);

        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            return response()->json(['message' => 'Instructor not found', 'error' => $e->getMessage()], 404);
        }
         catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $instructorRating = InstructorRating::findOrFail($id);
        try {
            $request->validate([
                'rating' => 'sometimes|required|integer|min:1|max:5',
                'review' => 'nullable|string',
            ]);

            $instructorRating->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            return response()->json(['message' => 'Rating updated successfully', 'data' => $instructorRating]);
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            return response()->json(['message' => 'Instructor not found', 'error' => $e->getMessage()], 404);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $instructorRating = InstructorRating::findOrFail($id);
            $instructorRating->delete();
            return response()->json(['message' => 'Rating deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }


}