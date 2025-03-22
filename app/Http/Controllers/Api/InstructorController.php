<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\Request;
use App\Traits\ImageUploadTrait;
use App\Helpers\ResponseHelper;

class InstructorController extends Controller
{
    use ImageUploadTrait;
    public function index()
    {
        return ResponseHelper::success('success',Instructor::all(), 200);
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
        ]);
          // Check if a new image is uploaded and process it
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
        ]);
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
}