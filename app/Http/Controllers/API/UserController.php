<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\Role;
use App\Models\InitialFamilyDetails;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\DB;
use File;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Mail;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    //login user 
    public function login(Request $request): JsonResponse
    {
        //login agent/user
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'mobile'=>'required',
            'role_id'=>'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }

        $credentials = $request->only('name', 'mobile', 'role_id');
        $user = User::where('name', $credentials['name'])
                    ->where('mobile', $credentials['mobile'])
                    ->where('role_id', $credentials['role_id'])
                    ->first();
    
        if ($user && Auth::guard('web')->loginUsingId($user->id)) {
            $token = $user->createToken('API Token')->plainTextToken;
            $role = Role::find($request->role_id);
            return response()->json([
                'user' => $user,
                'success' => true,
                'isLogined' => true,
                'token' => $token,
                'usertype' => $role->role_name,
                'message' => 'User login successfully.'
            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    
    //logout user
    public function logout(Request $request): JsonResponse
    {
        // Get user and delete the token
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'User logged out successfully.'], 200);
    }

    //add initial family details
    public function addInitFamilyDetail(Request $request):JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'state'=>'required',
            'district'=>'required',
            'city'=>'required',
            'family_Id'=>'required|unique:initial_family_details,family_Id',
            'family_photo'=>'sometimes|image|mimes:jpeg,png,jpg,gif',
            'native_address'=>'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        $initfamily = new InitialFamilyDetails;
        $initfamily->state = $request->state;
        $initfamily->district = $request->district;
        $initfamily->city = $request->city;
        $initfamily->family_Id = $request->family_Id;
        $initfamily->family_photo = $request->family_photo;
        $initfamily->native_address = $request->native_address;
        $initfamily->created_at = now();
        $initfamily->timestamps = false; 
        $initfamily->save();
        return $this->sendResponse($initfamily,'Initial family details added successfully');
    }
}