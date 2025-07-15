<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\CurrencyController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ExpertiseController;
use App\Http\Controllers\API\OrganizationController;
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
Route::get('/expertises',[ExpertiseController::class, 'index']);
Route::get('countries/{id}/states', [CountryController::class, 'getStates']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard',[DashboardController::class, 'index']);
    Route::get('/me', [ProfileController::class, 'me']);
    Route::patch('/users/{id}/deactivate', [UserController::class, 'deactivate']);
    Route::apiResource('users', UserController::class);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/profile', [ProfileController::class, 'updateProfile']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::put('/profile/bio', [ProfileController::class, 'updateBio']);
    Route::get('currencies', [CurrencyController::class, 'index']);

});



