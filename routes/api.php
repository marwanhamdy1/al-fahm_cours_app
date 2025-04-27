<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\course\ViewCourse;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\CourseSessionController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OverViewController;
use App\Http\Controllers\course\EnrolledCourseController;
use App\Http\Controllers\course\RatingController;
use App\Http\Controllers\course\InstructorRatingController;
use App\Http\Controllers\Event\EventsController;
use App\Http\Controllers\favorite\FavoriteController;
use App\Http\Controllers\Instructors\InstructorsAuthController;
use App\Http\Controllers\Instructors\InstructorsController;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
    Route::get('/image', [AuthController::class, 'image']); // Verify or create a user with phone number


Route::prefix('auth')->group(function () {
    Route::post('/loginAdmin', [AuthController::class, 'loginAdminTrash']); // Verify or create a user with phone number
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
    // update
    Route::post('/user/update', [UserController::class, 'update'])->middleware('handelAuth'); // Refresh token
    Route::post('/user/changePassword', [UserController::class, 'changePassword'])->middleware('handelAuth'); // Refresh token
    Route::post('/user/updateImage', [UserController::class, 'updateImage'])->middleware('handelAuth'); // Refresh token
    Route::get('/user/getMyPoints', [UserController::class, 'getMyPoints'])->middleware('handelAuth'); // Refresh token
    Route::get('/user/AddPointsTest/{point}', [UserController::class, 'AddPointsTest'])->middleware('handelAuth'); // Refresh token
});
Route::get('/course', [ViewCourse::class, 'index']);
Route::get('/events', [ViewCourse::class, 'indexEvents']);
Route::get('/course/{id}', [ViewCourse::class, 'show']);
Route::get('/course/category/{id}', [ViewCourse::class, 'indexByCategory']);
Route::get('/departmentAndSessions/{id}', [ViewCourse::class, 'departmentAndSessions']);
// enroll
Route::post('/course', [EnrolledCourseController::class, 'store'])->middleware('handelAuth'); // Refresh token;
Route::get('/course/destroy/{id}', [EnrolledCourseController::class, 'destroy'])->middleware('handelAuth'); // Refresh token;
Route::get('/course/enroll/toPending', [EnrolledCourseController::class, 'enrolledToPending'])->middleware('handelAuth'); // Refresh token;
Route::post('/course/assignCourse', [EnrolledCourseController::class, 'assignCourse'])->middleware('handelAuth'); // Refresh token;
Route::get('/course/get/courses', [EnrolledCourseController::class, 'getMyCourses'])->middleware('handelAuth'); // Refresh token;
// fav
Route::get('/favorites', [FavoriteController::class, 'index'])->middleware('handelAuth');;
Route::post('/favorites', [FavoriteController::class, 'store'])->middleware('handelAuth');;
Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy'])->middleware('handelAuth');
//rate
Route::get('/rating/{id}', [RatingController::class, 'index'])->middleware('handelAuth');;
Route::post('/rating', [RatingController::class, 'store'])->middleware('handelAuth');;
// instructor rating
Route::get('/instructorRating/{id}', [InstructorRatingController::class, 'index'])->middleware('handelAuth');
Route::post('/instructorRating/{id}', [InstructorRatingController::class, 'store'])->middleware('handelAuth');
Route::post('/instructorRating/update/{id}', [InstructorRatingController::class, 'update'])->middleware('handelAuth');
Route::delete('/instructorRating/{id}', [InstructorRatingController::class, 'destroy'])->middleware('handelAuth');

//Notifications
Route::get('/notifications', [NotificationController::class, 'index'])->middleware('handelAuth');
//Events
Route::get('/my-events', [EventsController::class, 'index'])->middleware('handelAuth'); // Refresh token;
Route::get('/pay-my-events', [EventsController::class, 'payEventToApprove'])->middleware('handelAuth'); // Refresh token;
// MARK:- DashBoard
// Category Routes
Route::post('/categories', [CategoryController::class, 'store'])->middleware('admin');
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show'])->middleware('moderator');;
Route::post('/categories-update/{category}', [CategoryController::class, 'update'])->middleware('admin');;
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('admin');;

// Instructor Routes
Route::post('/instructors', [InstructorController::class, 'store'])->middleware('admin');
Route::get('/instructors', [InstructorController::class, 'index'])->middleware('moderator');
Route::get('/instructors/{instructor}', [InstructorController::class, 'show'])->middleware('moderator');
Route::post('/instructors/{instructor}', [InstructorController::class, 'update'])->middleware('admin');
Route::delete('/instructors/{instructor}', [InstructorController::class, 'destroy'])->middleware('admin');
Route::get('/instructorCourses/{id}', [InstructorController::class, 'instructorCourses'])->middleware('moderator');
Route::get('/instructorRating/{id}', [InstructorController::class, 'instructorRating'])->middleware('moderator');
Route::get('/changeInstructorRatingStatusReview/{id}/{status}', [InstructorController::class, 'changeStatusReview'])->middleware('moderator');

// Department Routes
Route::post('/departments', [DepartmentController::class, 'store'])->middleware('admin');
Route::get('/departments/{id}', [DepartmentController::class, 'index'])->middleware('moderator');
Route::post('/departments/{department}', [DepartmentController::class, 'update'])->middleware('admin');
Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->middleware('admin');

// Course Session Routes
Route::post('/course-sessions', [CourseSessionController::class, 'store'])->middleware('admin');
Route::get('/course-sessions', [CourseSessionController::class, 'index'])->middleware('moderator');
Route::get('/course-sessions/{courseSession}', [CourseSessionController::class, 'show'])->middleware('moderator');
Route::put('/course-sessions/{courseSession}', [CourseSessionController::class, 'update'])->middleware('admin');
Route::delete('/course-sessions/{courseSession}', [CourseSessionController::class, 'destroy'])->middleware('admin');

// Course Routes
Route::post('/courses-add', [CourseController::class, 'store'])->middleware('admin');
Route::get('/courses', [CourseController::class, 'index'])->middleware('moderator');
Route::get('/courses/{course}', [CourseController::class, 'show'])->middleware('moderator');
Route::post('/courses/{course}', [CourseController::class, 'update'])->middleware('admin');
Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->middleware('admin');
Route::get('/usersCourses/{course}', [CourseController::class, 'usersCourses'])->middleware('moderator');
Route::get('/usersCoursesBySession/{course}/{sessionId}', [CourseController::class, 'usersCoursesBySession'])->middleware('moderator');
Route::post('/makeAttendForUsers', [CourseController::class, 'makeAttendForUser'])->middleware('moderator');
Route::get('/changeCourseStatus/{id}', [CourseController::class, 'changeCourseStatus'])->middleware('admin');
Route::get('/getUsersEnrolledInCourse/{id}', [CourseController::class, 'getUsersEnrolledInCourse'])->middleware('moderator');
Route::post('/makePaymentForUser', [CourseController::class, 'makePaymentForUser'])->middleware('moderator');
Route::get('/allPayments', [CourseController::class, 'allPayments'])->middleware('admin');
Route::get('/courseRating/{id}', [CourseController::class, 'courseRating'])->middleware('moderator');
Route::get('/changeCourseRatingStatusReview/{id}/{status}', [CourseController::class, 'changeStatusReview'])->middleware('moderator');
//users
Route::get('/users/info', [UsersController::class, 'getUsers'])->middleware('moderator');
Route::get('/users/info/parent', [UsersController::class, 'getParents'])->middleware('moderator');
Route::get('/users/info/parent/{id}/children', [UsersController::class, 'getChildrenParent'])->middleware('moderator');
Route::post('/users/updateUserInfo/{id}', [UsersController::class, 'updateUserInfo'])->middleware('moderator');
Route::get('/users/changeStatusUser/{id}', [UsersController::class, 'changeStatusUser'])->middleware('admin');
Route::get('/users/deleteUser/{id}', [UsersController::class, 'deleteUser'])->middleware('admin');
Route::post('/users/addParent', [UsersController::class, 'addParent'])->middleware('admin');
Route::post('/users/addChild/{parentID}', [UsersController::class, 'addChild'])->middleware('admin');

Route::get('/sendNotification', [NotificationController::class, 'sendNotification'])->middleware('moderator');

Route::get('/overview', [OverViewController::class, 'overview'])->middleware('moderator');
Route::post('/createAdminOrModerator', [OverViewController::class, 'createAdminOrModerator'])->middleware('admin');
Route::get('/getAllRole', [OverViewController::class, 'getAllRole'])->middleware('moderator');
Route::get('/deleteRole/{id}', [OverViewController::class, 'deleteRole'])->middleware('admin');
Route::get('/logsLogin', [OverViewController::class, 'logsLogin'])->middleware('moderator');

Route::get('/createAdmin', function () {
    $user = User::updateOrCreate(
        ['email' => 'admin@bemo.com'], // Check if an account with this email exists
        [
            'first_name' => 'Bemo',
            'last_name' => ' Admin',
            'password' => Hash::make('bemomaroo'),
            'role' => 'admin', // Change to 'super_admin' if needed
        ]
    );

    return response()->json([
        'message' => 'Admin account created successfully',
        'user' => $user
    ]);
});

// Public routes (no auth required)
Route::prefix('instructor')->group(function () {
    // Authentication routes
    Route::post('login', [InstructorsAuthController::class, 'login']);

    // Add other public routes here if needed
});

// Protected routes (require instructor auth)
Route::prefix('instructor')->group(function () {
    // Auth-related routes
    Route::post('logout', [InstructorsAuthController::class, 'logout']);
    Route::post('refresh', [InstructorsAuthController::class, 'refresh']);
    Route::get('me', [InstructorsAuthController::class, 'me']);

    // Instructor-specific routes
    Route::get('courses', [InstructorsController::class, 'myCourse']);
    Route::get('courses/{courseId}/users', [InstructorsController::class, 'usersInCourse']);
    Route::get('courses/{courseId}/{courseSessionId}', [InstructorsController::class, 'usersCoursesBySession']);
    Route::post('courses/attendance', [InstructorsController::class, 'makeAttendForUser']);

    // Add other protected routes here
});