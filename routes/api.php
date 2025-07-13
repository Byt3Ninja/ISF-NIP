<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ExpertiseController;
use App\Http\Controllers\API\RegistrationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::match(['post', 'get'], 'register-company', [RegistrationController::class, 'register']);
Route::get('uploads/companies/{path}', [RegistrationController::class, 'showImage'])->where('path', '.*');
Route::get('company', [RegistrationController::class, 'getCompany']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/dashboard',[DashboardController::class, 'index'])->middleware('auth:sanctum');
Route::get('/expertises',[ExpertiseController::class, 'index']);
Route::get('/me', [ProfileController::class, 'me'])->middleware('auth:sanctum');
Route::patch('/users/{id}/deactivate', [UserController::class, 'deactivate'])->middleware('auth:sanctum');
Route::apiResource('users', UserController::class)->middleware('auth:sanctum');
Route::get('/roles', [RoleController::class, 'index'])->middleware('auth:sanctum');
Route::put('/profile', [ProfileController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('auth:sanctum');
Route::put('/profile/bio', [ProfileController::class, 'updateBio'])->middleware('auth:sanctum');
Route::get('/countries', [CountryController::class, 'index'])->middleware('auth:sanctum');
Route::get('countries/{id}/states', [CountryController::class, 'getStates']);
Route::post('/profile/image', [ProfileController::class, 'uploadImage'])->middleware('auth:sanctum');



