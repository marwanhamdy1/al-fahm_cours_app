<?php

namespace App\Http\Controllers\Instructors;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Instructor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class InstructorsAuthController extends Controller
{
    public function login(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:instructors,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error("Validation error", 422, $validator->errors());
        }

        // Attempt authentication with JWT guard
        $token = Auth::guard('instructor')->attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);

        if (!$token) {
            return ResponseHelper::error("Invalid credentials", 401);
        }

        // Get the authenticated instructor
        $instructor = Auth::guard('instructor')->user();

        return ResponseHelper::success("Login successful", [
            'instructor' => $instructor,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('instructor')->factory()->getTTL() * 60
        ]);

    } catch (\Exception $e) {
        return ResponseHelper::error("Something went wrong", 500, $e->getMessage());
    }
}

    public function logout()
    {
        try {
            Auth::guard('instructor')->logout();
            return ResponseHelper::success("Successfully logged out");
        } catch (\Exception $e) {
            return ResponseHelper::error("Logout failed", 500, $e->getMessage());
        }
    }

    public function refresh()
    {
        try {
            $token = Auth::guard('instructor')->refresh();
            return ResponseHelper::success("Token refreshed", [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('instructor')->factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error("Token refresh failed", 401, $e->getMessage());
        }
    }

    public function me()
    {
        try {
            $instructor = auth("instructor")->user();
            return ResponseHelper::success("Instructor data", $instructor);
        } catch (\Exception $e) {
            return ResponseHelper::error("Failed to get instructor data", 500, $e->getMessage());
        }
    }
}