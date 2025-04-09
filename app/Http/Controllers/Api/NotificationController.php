<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Exception;

class NotificationController extends Controller
{
    // Get all notifications
    public function index()
    {
        try {
            $notifications = Notification::latest()->get();
            return response()->json($notifications, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch notifications', 'message' => $e->getMessage()], 500);
        }
    }

    // Store a new notification
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'body' => 'nullable|string',
                'type' => 'nullable|string',
                'user_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $notification = Notification::create($validator->validated());

            return response()->json($notification, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create notification', 'message' => $e->getMessage()], 500);
        }
    }

}