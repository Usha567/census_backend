<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AgentController;
use App\Http\Controllers\API\FamilyDetailController;

Route::post('login', [UserController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('add_initial_family_detail', [UserController::class, 'addInitFamilyDetail']);
    Route::get('getall_initial_family_details', [UserController::class, 'getAllInitFamilyDetails']);
    Route::get('get_initial_family_details_byId/{id}/', [UserController::class, 'getInitFamilyDetailsById']);
    Route::delete('delte_initial_family_details/{id}/', [UserController::class, 'deleteInitFamilyDetails']);
    Route::resource('agent', AgentController::class);
    Route::resource('family-details', FamilyDetailController::class);
    Route::post('get_family_details/{id}/', [FamilyDetailController::class, 'getFamilyData']);
    Route::post('delete_child_member/{id}/', [FamilyDetailController::class, 'deleteChildMember']);
    Route::get('states', [UserController::class, 'getAllStates']);
    Route::get('district/{id}/', [UserController::class, 'getDistrictByState']);
    Route::get('city/{id}/', [UserController::class, 'getCityByDistrict']);
});