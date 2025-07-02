<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
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
Route::middleware('cross-origin')->group(function () {
    
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/dashboard',[DashboardController::class, 'index'])->middleware('auth:sanctum');
});


