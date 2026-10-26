<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PublicSettingController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\Admin\PropertyModerationController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\Admin\PropertyTypeController;
use App\Http\Controllers\Api\Admin\CityController;
use App\Http\Controllers\Api\Admin\AmenityController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\StatsController;
use App\Http\Controllers\Api\Admin\AnalyticsController;
use App\Http\Controllers\Api\Admin\InquiryController as AdminInquiryController;

Route::get('/property-types', [MasterDataController::class, 'propertyTypes']);
Route::get('/cities', [MasterDataController::class, 'cities']);
Route::get('/amenities', [MasterDataController::class, 'amenities']);
Route::get('/settings/public', [PublicSettingController::class, 'index']);
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{slug}', [PropertyController::class, 'show']);
Route::post('/properties/{property}/inquiries', [InquiryController::class, 'store'])->middleware('throttle:inquiries');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/password', [ProfileController::class, 'changePassword']);
    Route::get('/my-inquiries', [InquiryController::class, 'myInquiries']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{property}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{property}', [FavoriteController::class, 'destroy']);
    Route::get('/owner/inquiries', [InquiryController::class, 'index']);
    Route::get('/owner/properties', [PropertyController::class, 'ownerIndex']);
});
Route::middleware(['auth:sanctum', 'permission:create-properties'])->group(function () {
    Route::post('/owner/properties', [PropertyController::class, 'store']);
});
Route::middleware(['auth:sanctum', 'permission:edit-own-properties'])->group(function () {
    Route::post('/owner/properties/{property}/images', [PropertyController::class, 'uploadImages']);
    Route::put('/owner/properties/{property}', [PropertyController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'permission:delete-own-properties'])->group(function () {
    Route::delete('/owner/properties/{property}', [PropertyController::class, 'destroy']);
});


Route::middleware(['auth:sanctum', 'permission:approve-properties'])->prefix('admin')->group(function () {
    Route::get('/properties/pending', [PropertyModerationController::class, 'pending']);
    Route::put('/properties/{property}/approve', [PropertyModerationController::class, 'approve']);
    Route::put('/properties/{property}/toggle-featured', [PropertyModerationController::class, 'toggleFeatured']);
});

Route::middleware(['auth:sanctum', 'permission:reject-properties'])->prefix('admin')->group(function () {
    Route::put('/properties/{property}/reject', [PropertyModerationController::class, 'reject']);
});

Route::middleware(['auth:sanctum', 'permission:manage-users'])->prefix('admin')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::put('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus']);
    Route::put('/users/{user}/assign-role', [UserManagementController::class, 'assignRole']);
    Route::put('/users/{user}/verify-broker', [UserManagementController::class, 'verifyBroker']);
    Route::put('/users/{user}/reject-broker', [UserManagementController::class, 'rejectBroker']);
});


Route::middleware(['auth:sanctum', 'permission:manage-master-data'])->prefix('admin')->group(function () {
    Route::get('/property-types', [PropertyTypeController::class, 'index']);
    Route::post('/property-types', [PropertyTypeController::class, 'store']);
    Route::put('/property-types/{propertyType}', [PropertyTypeController::class, 'update']);
    Route::delete('/property-types/{propertyType}', [PropertyTypeController::class, 'destroy']);

    Route::get('/cities', [CityController::class, 'index']);
    Route::post('/cities', [CityController::class, 'store']);
    Route::put('/cities/{city}', [CityController::class, 'update']);
    Route::delete('/cities/{city}', [CityController::class, 'destroy']);

    Route::get('/amenities', [AmenityController::class, 'index']);
    Route::post('/amenities', [AmenityController::class, 'store']);
    Route::put('/amenities/{amenity}', [AmenityController::class, 'update']);
    Route::delete('/amenities/{amenity}', [AmenityController::class, 'destroy']);
});



Route::middleware(['auth:sanctum', 'permission:manage-settings'])->prefix('admin')->group(function () {
    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'permission:view-activity-logs'])->prefix('admin')->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});


Route::middleware(['auth:sanctum', 'permission:manage-users'])->prefix('admin')->group(function () {
    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/analytics', [AnalyticsController::class, 'index']);
});


Route::middleware(['auth:sanctum', 'permission:manage-users'])->prefix('admin')->group(function () {
    Route::get('/inquiries', [AdminInquiryController::class, 'index']);
    Route::put('/inquiries/{inquiry}/status', [AdminInquiryController::class, 'updateStatus']);
});
