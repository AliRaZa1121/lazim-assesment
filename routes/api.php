<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RestApi\AuthController;
use App\Http\Controllers\RestApi\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/resend-verify-email', [AuthController::class, 'resendVerifyEmail'])->name('api.resend-verify-email');
Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('api.verify-email');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.forgot-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.reset-password');
Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout')->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->group(function () {
    // Task routes
    Route::get('/tasks', [TaskController::class, 'index'])->name('api.tasks');
    Route::post('/tasks', [TaskController::class, 'store'])->name('api.tasks.store');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('api.tasks.show');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('api.tasks.update');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('api.tasks.destroy');
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
