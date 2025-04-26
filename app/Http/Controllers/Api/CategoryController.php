<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\ImageUploadTrait;
use App\Helpers\ResponseHelper;

class CategoryController extends Controller
{
    use ImageUploadTrait;
    public function index()
    {
        $categories = Category::withCount('courses')->get()->map(function ($category) {
    $category->image =  asset('storage/' . $category->image);
    return $category;
});

return ResponseHelper::success('success', $categories, 200);
    }

    public function store(Request $request)
    {
        try{
        $image = $this->saveImage($request->image);
        $request->validate([
            'name_ar' => 'required|string',
            'name_he' => 'required|string',
            'image' => 'required|image',
        ]);
        $image = $this->saveImage($request->image);
        $category = Category::create([
            'name_ar' => $request->name_ar,
            'name_he' =>  $request->name_he,
            'image' => $image,
        ]);

        return response()->json($category, 201);

    }catch(\Exception $e){
        return response()->json(['message'=>$e->getMessage()], 400);
    }
    }

    public function show(Category $category)
    {
       return ResponseHelper::success('success',$category, 200);
    }

    public function update(Request $request, Category $category)
{
    $validatedData = $request->validate([
        'name_ar' => 'sometimes|string',
        'name_he' => 'sometimes|string',
        'image' => 'sometimes|image', // Ensure valid image if provided
    ]);

    // Check if a new image is uploaded and process it
    if ($request->hasFile('image')) {
        $validatedData['image'] = $this->saveImage($request->image);
    }

    // Update the category with validated data
    $category->update($validatedData);

    return response()->json($category, 200);
}

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}