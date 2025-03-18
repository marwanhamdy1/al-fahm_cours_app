<?php

namespace App\Http\Controllers\favorite;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use Exception;
class FavoriteController extends Controller
{
  public function index(Request $request)
    {
        try {
            $queryUserId = $this->checkChildAndPermission($request);
            $favorites = Favorite::where('user_id', $queryUserId)->with('course')->get();
            return ResponseHelper::success("success", FavoriteResource::collection($favorites));
        } catch (Exception $e) {
            return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
{
    try {
        $queryUserId = $this->checkChildAndPermission($request);

        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        // Check if the favorite already exists
        $existingFavorite = Favorite::where('user_id', $queryUserId)
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingFavorite) {
            return ResponseHelper::error('This course is already in favorites', 409); // 409 Conflict
        }

        // Add to favorites if not exists
        Favorite::create([
            'user_id' => $queryUserId,
            'course_id' => $request->course_id,
        ]);

        return ResponseHelper::success('Course added to favorites');

    } catch (Exception $e) {
        return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
    }
}


    public function destroy(Request $request, $id)
    {
        try {
            $queryUserId = $this->checkChildAndPermission($request);
            $favorite = Favorite::where('user_id', $queryUserId)->where('course_id', $id)->first();

            if (!$favorite) {
                return ResponseHelper::error('Favorite not found', 404);
            }

            $favorite->delete();
            return ResponseHelper::success('Favorite removed');
        } catch (Exception $e) {
            return ResponseHelper::error("حدث خطأ: " . $e->getMessage(), 500);
        }
    }

    private function checkChildAndPermission(Request $request)
    {
        $userId = auth()->user()->id;
        $queryUserId = $request->has('child_id') ? $request->child_id : $userId;

        if ($request->has('child_id') && !auth()->user()->children()->where('id', $queryUserId)->exists()) {
            throw new Exception("ليس لديك صلاحية لتسجيل هذا الطفل");
        }

        return $queryUserId;
    }
}