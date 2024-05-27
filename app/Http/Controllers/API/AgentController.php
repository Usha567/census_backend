<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;

class AgentController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Add user
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'mobile'=>'required',
            'email'=>'required',
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
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
