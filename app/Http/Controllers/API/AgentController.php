<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use File;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class AgentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Get all data
        $users = User::all();
        return $this->sendResponse($users, 'Successfully get all data');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       //store data
       echo 'adding data...';
       $validator = Validator::make($request->all(),[
        'name'=>'required',
        'mobile'=>'required|numeric|digits:10|unique:users,mobile',
        'email'=>'required|email|unique:users,email',
        'state'=>'required',
        'district'=>'required',
        'city'=>'required',
        'role_id'=>'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->role_id = $request->role_id;
        $user->state = $request->state;
        $user->district = $request->district;
        $user->city = $request->city;
        $user->created_at = now();
        $user->timestamps = false; 
        $user->save();
        return $this->sendResponse($user,'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('id', $id)->first();
        if(is_null($user)){
            return $this->sendError('User Not Found',[]);
        }
        return $this->sendResponse($user, 'Successfully get all data');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        echo 'updating data...';
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'mobile'=>'required|numeric|digits:10|unique:users,mobile',
            'email'=>'required|email|unique:users,email',
            'state'=>'required',
            'district'=>'required',
            'city'=>'required',
            'role_id'=>'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        $user = User::where('id', $id)->first();
        if(is_null($user)){
            return $this->sendError('User Not Found');
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->role_id = $request->role_id;
        $user->state = $request->state;
        $user->district = $request->district;
        $user->city = $request->city;
        $user->updated_at = now();
        $user->save();
        return $this->sendResponse($user,'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        if(is_null($user)){
            return $this->sendError('User Not Found');
        }else{
            $user->delete();
        }
        return $this->sendResponse([],'User deleted successfully');
    }
}
