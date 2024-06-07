<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\Role;
use App\Models\InitialFamilyDetails;
use App\Models\FamilyDetails;
use App\Models\State;
use App\Models\District;
use App\Models\City;
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
        if($request->other_state){
            $initfamily->other_state = $request->other_state;
        }
        if($request->other_district){
            $initfamily->other_district = $request->other_district;
        }
        if($request->other_city){
            $initfamily->other_city = $request->other_city;
        }
        if($request->hasfile('family_photo')){
            $img = $request->file('family_photo');
            $imgName = time().'_'.uniqid().'_'.$img ->getClientOriginalName();
            $saveImage =  $img->move(public_path('uploads/family_pictures'), $imgName);
            $initfamily->family_photo =  $imgName;
        }
        $initfamily->native_address = $request->native_address;
        $initfamily->created_at = now();
        $initfamily->timestamps = false; 
        $initfamily->save();
        return $this->sendResponse($initfamily,'Initial family details added successfully');
    }

    //Get all initial family details
    public function getAllInitFamilyDetails(Request $request):JsonResponse
    {
        $initfamilydetails = InitialFamilyDetails::with('familymemberdetails')->get();
        if(count($initfamilydetails)==0){
            return $this->sendError('Family Details Not Found');
        }
        return $this->sendResponse($initfamilydetails, 'Successfully get all family details.');
    }

    //Get all initial family details
    public function getInitFamilyDetailsById(Request $request, $id):JsonResponse
    {
        $initfamilydetails = InitialFamilyDetails::with('familymemberdetails')->where('id', $id)->get();
        if(count($initfamilydetails)==0){
            return $this->sendError('Family Details Not Found');
        }
        return $this->sendResponse($initfamilydetails, 'Successfully get all family details.');
    }


    //Delete init family details
    public function deleteInitFamilyDetails($id):JsonResponse
    {
        $initfamilydetails = InitialFamilyDetails::where('family_id', $id)->first();
        if(count($initfamilydetails)==0){
            return $this->sendError('Init Family Details Not Found');
        }
        else{
            $familydetails=FamilyDetails::where('family_id', $id)->get();
            if(count($familydetails)==0){
                $initfamilydetails->delete(); 
                return $this->sendResponse([],'Family Details Not Found , init family details Successfully delete.');
            }
            else{
                foreach($familydetails as $familyd){
                    $familyd->delete();
                }
                $initfamilydetails->delete(); 
            }
        }
        return $this->sendResponse([], 'Successfully delete init family details.');
    }

    //get all city
    public function getAllStates():JsonResponse
    {
        $states = State::all();
        return $this->sendResponse($states, 'Successfully get all states.');
    }

    //get district by state
    public function getDistrictByState($id):jsonResponse
    {
        if($id==7){
            $dist = District::where('state', $id)->get();
        }
        else{
            $dist = District::where('district_name','=','Other District')->get();
        }
        return $this->sendResponse($dist, 'Successfully get all districts.');
    }

    //get city by district
    public function getCityByDistrict($id):jsonResponse
    {
        if($id==125){
            $city = City::where('district', $id)->get();
        }
        else{
            $city = City::where('district', $id)->first();
            if(is_null($city)){
                $dist = new City;
                $dist->district=$id;
                $dist->city_name='Other City';
                $dist->save();
                $city = City::where('district', $id)->get();
            }
        }
        return $this->sendResponse($city, 'Successfully get all cities.');
    }
}