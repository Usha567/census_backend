<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AgentController;

//AgentController
Route::resource('agent', AgentController::class);
Route::post('login', [UserController::class, 'login']);
