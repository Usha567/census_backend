<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AgentController;
use App\Http\Controllers\API\FamilyDetailController;

Route::post('login', [UserController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function(){
    //user logout
    Route::post('logout', [UserController::class, 'logout']);

    //Init family details
    Route::post('add_initial_family_detail', [UserController::class, 'addInitFamilyDetail']);
    Route::get('getall_initial_family_details', [UserController::class, 'getAllInitFamilyDetails']);
    Route::get('get_initial_family_details_byId/{id}/', [UserController::class, 'getInitFamilyDetailsById']);
    Route::delete('delte_initial_family_details/{id}/', [UserController::class, 'deleteInitFamilyDetails']);

    //agent details
    Route::resource('agent', AgentController::class);
    Route::post('search_agents', [AgentController::class,'searchAgents']);

    //family details
    Route::resource('family-details', FamilyDetailController::class);
    Route::post('get_family_details/{id}/', [FamilyDetailController::class, 'getFamilyData']);
    Route::post('search_family_details/', [FamilyDetailController::class, 'searchFamilyDetails']);
    Route::post('delete_child_member/{id}/', [FamilyDetailController::class, 'deleteChildMember']);

    //suggestions
    Route::post('add_suggestion', [FamilyDetailController::class, 'addSuggestion']);
    Route::get('get_all_suggestion', [FamilyDetailController::class, 'getAllSuggestion']);
    Route::get('get_suggestion_by_Id/{id}', [FamilyDetailController::class, 'getSuggestionById']);
    Route::delete('delete_suggetion/{id}/', [FamilyDetailController::class, 'deleteSuggestion']);

    //Addresses
    Route::get('states', [UserController::class, 'getAllStates']);
    Route::get('district/{id}/', [UserController::class, 'getDistrictByState']);
    Route::get('city/{id}/', [UserController::class, 'getCityByDistrict']);
    
    //searching addresses
    Route::get('districts', [UserController::class, 'getAllDistricts']);
    //get all other state name if state is other type
    Route::get('other_states/{id}', [UserController::class, 'getAllOtherStates']);
    //get all other districts if district is other type
    Route::get('other_districts/{id}', [UserController::class, 'getAllOtherDistricts']);
});