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
Route::get("/get_banks", [TransactionController::class, "getBanks"]);
Route::post("/validate_account", [TransactionController::class, "verifyAccount"]);


Route::get('is_verified', function(){
    return response()->json(["success" => true, "message" => "User is verified"]);
})->middleware(JWTMiddleware::class);

//create authenticated routes

Route::middleware(JWTMiddleware::class)->group(function(){
    Route::post('/dashboard', [UserController::class, 'dashboard']);
    Route::post("/user/link_bank-account", [TransactionController::class, "link_account"]);
});
