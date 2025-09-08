<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NatureOfCollectionController;
use App\Http\Controllers\DailyReportOfPaymentsController;
use Spatie\Activitylog\Models\Activity;
use App\Http\Controllers\ComparativeDataController;
use App\Http\Controllers\CashReceiptRegisterController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh',  [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('update-user-password', [AuthController::class, 'updateUserPassword']);
});

Route::middleware('api')->get('/activity-logs', function (Request $request) {
    $query = Activity::with('causer')->latest();

    // Exclude activities with null causer
    $query->whereNotNull('causer_id');

    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              // Only search 'email' on users table
              ->orWhere(function ($subQ) use ($search) {
                  $subQ->where('causer_type', \App\Models\User::class)
                       ->whereHasMorph('causer', [\App\Models\User::class], function ($userQ) use ($search) {
                           $userQ->where('email', 'like', "%{$search}%");
                       });
              })
              // Search name fields on user_infos table
              ->orWhere(function ($subQ) use ($search) {
                  $subQ->where('causer_type', \App\Models\UserInfo::class)
                       ->whereHasMorph('causer', [\App\Models\UserInfo::class], function ($infoQ) use ($search) {
                           $infoQ->where('abbreviation', 'like', "%{$search}%")
                                 ->orWhere('firstname', 'like', "%{$search}%")
                                 ->orWhere('middlename', 'like', "%{$search}%")
                                 ->orWhere('lastname', 'like', "%{$search}%")
                                 ->orWhere('suffix', 'like', "%{$search}%");
                       });
              });
        });
    }

    if ($userId = $request->input('user_id')) {
        $query->where('causer_id', $userId);
    }

    if ($startDate = $request->input('start_date')) {
        $query->whereDate('created_at', '>=', $startDate);
    }
    if ($endDate = $request->input('end_date')) {
        $query->whereDate('created_at', '<=', $endDate);
    }
    
    $perPage = $request->input('per_page', 20);

    return $query->paginate($perPage);
});

Route::middleware('api')->apiResource('roles', RoleController::class);
Route::middleware('api')->apiResource('permissions', PermissionController::class);
Route::middleware('api')->apiResource('users', UserController::class);
Route::middleware('api')->apiResource('payments', PaymentController::class);
Route::middleware('api')->apiResource('nature-of-collections', NatureOfCollectionController::class);
Route::middleware('api')->apiResource('daily-report-of-collections', DailyReportOfPaymentsController::class);

Route::middleware('api')->patch('users/{user}/deactivate', [UserController::class, 'deactivate']);
Route::middleware('api')->patch('users/{user}/activate', [UserController::class, 'activate']);

Route::middleware('api')->get('dashboard', [DashboardController::class, 'index']);
Route::get('compare-years', [ComparativeDataController::class, 'compareYears']);
Route::get('cash-receipt-register/daily-report', [CashReceiptRegisterController::class, 'getDailyReport']);
Route::get('cash-receipt-register/monthly-report', [CashReceiptRegisterController::class, 'getMonthlyReport']);