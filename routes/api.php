<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AgentController;

Route::post('login', [UserController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('add_initial_family_detail', [UserController::class, 'addInitFamilyDetail']);
    Route::resource('agent', AgentController::class);
});

