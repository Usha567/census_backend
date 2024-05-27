<?php 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AgentController;
use App\Http\Controllers\API\UserController;

Route::prefix('api')->group(function(){
    echo 'eeee';
    Route::resource('/agent-data', AgentController::class)->except(['index', 'show', 'store','update','destroy']);
    Route::post('/login',[UserController::class, 'login']);
});
