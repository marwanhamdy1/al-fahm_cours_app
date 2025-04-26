<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\EnrolledCourse;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OverViewController extends Controller
{
public function overview()
{
    try {
        // Define time range (e.g. last 7 days)
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // === 1. Profit Chart: Sum amount_paid per day ===
        $profitData = EnrolledCourse::whereIn("payment_status", ["partially_paid", "paid"])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("SUM(amount_paid) as value")
            )
            ->groupBy("date")
            ->orderBy("date")
            ->get();

        // === 2. User Chart: Count new users per day ===
        $userData = User::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("COUNT(*) as value")
            )
            ->groupBy("date")
            ->orderBy("date")
            ->get();

        // === 3. Totals (example metrics) ===
        $totals = [
            'child_users' => User::where('role', 'child')->count(),
            'parent_users' => User::where('role', 'parent')->count(),
            'individual_users' => User::where('role', 'individual')->count(),
            'enrollments' => EnrolledCourse::count(),
            'course' => Course::count(),
            'instructor' => Instructor::count(),
            'category' => Category::count(),
        ];

        return response()->json([
            'profit_chart' => $profitData,
            'user_chart' => $userData,
            'totals' => $totals,
            'date_range' => $startDate->format('M d') . ' - ' . $endDate->format('M d, Y'),
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ: ' . $e->getMessage()
        ], 500);
    }
}
public function createAdminOrModerator(Request $request)
{
    $request->validate([
        'email' => 'required|email|unique:users,email',
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'role' => 'required|in:admin,moderator', // adjust roles as needed
        'password' => 'nullable|string|min:6',
    ]);

    try {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password ?? 'defaultPassword123'), // fallback
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
public function getAllRole(Request $request)
{
    try {
        $user = User::whereIn('role', ["super_admin",'admin', 'moderator'])->get();

        return response()->json([
            'message' => 'User  successfully',
            'user' => $user,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
public function deleteRole($id){
    $user = User::find($id);
    if ($user && $user->role !="super_admin") {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    } else {
        return response()->json(['message' => 'User not found'], 404);
    }
}
public function logsLogin(){
    $logs  = LoginLog::latest()->get();

    return response()->json([
        'logs' => $logs,
    ]);
}
}