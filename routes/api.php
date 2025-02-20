<?php

use Illuminate\Http\Request;
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
