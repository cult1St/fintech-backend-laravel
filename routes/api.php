<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\JWTMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post("/logout", [UserController::class, 'logout']);
Route::post("/get_banks", [TransactionController::class, "getBanks"]);


Route::get('is_verified', function(){
    return response()->json(["success" => true, "message" => "User is verified"]);
})->middleware(JWTMiddleware::class);

//create authenticated routes

Route::middleware(JWTMiddleware::class)->group(function(){
    Route::post('dashboard', [UserController::class, 'dashboard']);
});
