<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Validator;

class UserController extends BaseController
{
    //login agent/user
    public function login(Request $request):JsonResponse
    {
        $validator = Validator::make($request->all(),[
           'name'=>'required',
           'mobile'=>'required',
           'role_id'=>'required'
        ]);
        if($request->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        // Attempt to find the user by email or username
        $user = User::where('name', $request->name)
                    ->where('mobile', $request->mobile)
                    ->where('role_id', $request->role_id)
                    ->first();

        if (!$user) {
            return $this->sendError('Login credentials are invalid.', ['error' => 'Unauthorised']);
        }
        
        // Return the response with the token and user details
        return response()->json([
            'user' => $user,
            'success' => true,
            'isLogined' =>true,
            'usertype' => Roles::where("id", $request->role_id)->get('type'),
            'message' => 'User login successfully.'
        ]);
    }
}
