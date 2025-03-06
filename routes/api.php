<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\course\ViewCourse;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']); // Verify or create a user with phone number
    Route::post('/phone-number', [AuthController::class, 'phoneNumber']); // Verify or create a user with phone number
    Route::post('/verify-code', [AuthController::class, 'verifyCode']); // Verify or create a user with phone number
    Route::post('/complete-profile', [AuthController::class, 'completeProfile'])->middleware('handelAuth');// Complete user profile
    Route::post('/add-child', [AuthController::class, 'addChild'])->middleware('handelAuth'); // Add a child user
    Route::get('/checkUserName/{username}', [AuthController::class, 'checkUserName']);
    Route::get('/myChildren', [AuthController::class, 'myChildren'])->middleware('handelAuth');

    Route::get('/me', [AuthController::class, 'me'])->middleware('handelAuth'); // Get authenticated user details
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('handelAuth'); // Logout user
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('handelAuth'); // Refresh token
});
Route::get('/course', [ViewCourse::class, 'index']);
Route::get('/departmentAndSessions/{id}', [ViewCourse::class, 'departmentAndSessions']);