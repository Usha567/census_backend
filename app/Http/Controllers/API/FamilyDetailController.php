<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\FamilyDetails;
use Validator;
use Illuminate\Validation\Rule;
use App\Rules\UniqueMobileAcrossFamilies;

class FamilyDetailController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Add family details
        echo 'adding...';
        $validator = Validator::make($request->all(),[
            'family_id'=>'required',
            'father_id'=>'sometimes',
            'family_member_id'=>'required',
            'name'=>'required',
            'mother_name'=>'sometimes',
            'father_name'=>'sometimes',
            'father_surname'=>'sometimes',
            'age'=>'required',
            'dob'=>'required|date_format:Y-m-d',
            'mobile_number'=>[
                'required',
                'numeric',
                'digits:10',
                new UniqueMobileAcrossFamilies($request->family_id),
            ],
            'relation'=>'required',
            'qualification'=>'sometimes',
            'marriage_type'=>'sometimes',
            'marital_status'=>'sometimes',
            'marriage_stage'=>'sometimes',
            'blood_group'=>'sometimes',
            'total_kids'=>'sometimes',
            'sons'=>'sometimes',
            'daughters'=>'sometimes',
            'occupation'=>'sometimes',
            'self_image'=>'sometimes|image|mimes:jpeg,png,jpg,gif',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }

        #check same family id & memberid -this is for husband
        $familydetails = new FamilyDetails;              
        if($request->family_member_id==1){
            $checkdetails = FamilyDetails::where('family_id', $request->family_id)
                ->where('family_member_id', $request->family_member_id)
                ->first();
            if(!is_null($checkdetails)){
                echo "You are a son , your father's entry is already done..";
                $familydetails->father_id= $request->father_id;
            }
        }
        else if($request->family_member_id==2){
            #check same family id & memberid -this is for wife    
            $checkdetails = FamilyDetails::where('family_id', $request->family_id)
                ->where('family_member_id', $request->family_member_id)
                ->first();   
            if(!is_null($checkdetails)){
                return $this->sendResponse($checkdetails, "You are a wife , your's entry is already done..");
            }    
        }            
        $familydetails->family_id = $request->family_id;
        // $familydetails->father_id = $request->father_id;
        $familydetails->family_member_id = $request->family_member_id;
        $familydetails->name = $request->name;
        $familydetails->mother_name = $request->mother_name;
        $familydetails->father_name = $request->father_name;
        $familydetails->father_surname = $request->father_surname;
        $familydetails->age = $request->age;
        $familydetails->dob = $request->dob;
        $familydetails->mobile_number = $request->mobile_number;
        $familydetails->relation = $request->relation;
        $familydetails->qualification = $request->qualification;
        $familydetails->marriage_type = $request->marriage_type;
        $familydetails->marital_status = $request->marital_status;
        $familydetails->marriage_stage = $request->marriage_stage;
        $familydetails->blood_group = $request->blood_group;
        $familydetails->total_kids = $request->total_kids;
        $familydetails->sons = $request->sons;
        $familydetails->daughters = $request->daughters;
        $familydetails->occupation = $request->occupation;
        if($request->hasfile('self_image')){
            $img = $request->file('self_image');
            $imgName = time().'_'.uniqid().'_'.$img ->getClientOriginalName();
            $saveImage =  $img->move(public_path('uploads/family_member_image'), $imgName);
            $familydetails->self_image =  $imgName;
        }
        $familydetails->created_at = now();
        $familydetails->timestamps = false; 
        $familydetails->save();
        return $this->sendResponse($familydetails,'Family member details added successfully');
    }

    /** 
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //show data
    }

    public function getFamilyData(Request $request, $id)
    {
        $familydetails = FamilyDetails::where('family_id', $id)
            ->where('family_member_id', $request->family_member_id)
            ->first();
        if(is_null($familydetails)){
            return $this->sendResponse([],'No Family details found for this family & member id.');
        }    
        return $this->sendResponse($familydetails,'Get the family member details successfully');
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
        //Add family details
        echo 'updatng...';
        $validator = Validator::make($request->all(),[
            'father_id'=>'sometimes',
            'family_member_id'=>'required',
            'name'=>'required',
            'mother_name'=>'sometimes',
            'father_name'=>'sometimes',
            'father_surname'=>'sometimes',
            'age'=>'required',
            'dob'=>'required|date_format:Y-m-d',
            'mobile_number'=>[
                'required',
                'numeric',
                'digits:10',
                new UniqueMobileAcrossFamilies($id),
            ],
            'relation'=>'required',
            'qualification'=>'sometimes',
            'marriage_type'=>'sometimes',
            'marital_status'=>'sometimes',
            'marriage_stage'=>'sometimes',
            'blood_group'=>'sometimes',
            'total_kids'=>'sometimes',
            'sons'=>'sometimes',
            'daughters'=>'sometimes',
            'occupation'=>'sometimes',
            'self_image'=>'sometimes|image|mimes:jpeg,png,jpg,gif',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }

        $familydetails = FamilyDetails::where('family_id', $id)
            ->where('family_member_id', $request->family_member_id)
            ->first();
        if(is_null($familydetails)){
            return $this->sendResponse([],'No Family details found for this family & member id.');
        }               
        $familydetails->family_id = $id;
        $familydetails->father_id = $request->father_id;
        $familydetails->family_member_id = $request->family_member_id;
        $familydetails->name = $request->name;
        $familydetails->mother_name = $request->mother_name;
        $familydetails->father_name = $request->father_name;
        $familydetails->father_surname = $request->father_surname;
        $familydetails->age = $request->age;
        $familydetails->dob = $request->dob;
        $familydetails->mobile_number = $request->mobile_number;
        $familydetails->relation = $request->relation;
        $familydetails->qualification = $request->qualification;
        $familydetails->marriage_type = $request->marriage_type;
        $familydetails->marital_status = $request->marital_status;
        $familydetails->marriage_stage = $request->marriage_stage;
        $familydetails->blood_group = $request->blood_group;
        $familydetails->total_kids = $request->total_kids;
        $familydetails->sons = $request->sons;
        $familydetails->daughters = $request->daughters;
        $familydetails->occupation = $request->occupation;
        if($request->hasfile('self_image')){
            $img = $request->file('self_image');
            $imgName = time().'_'.uniqid().'_'.$img ->getClientOriginalName();
            $saveImage =  $img->move(public_path('uploads/family_member_image'), $imgName);
            $familydetails->self_image =  $imgName;
        }
        $familydetails->updated_at = now();
        $familydetails->save();
        return $this->sendResponse($familydetails,'Family member details updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      //
    }

    public function deleteChildMember(Request $request, $id)
    {
        //Delete the child member details
        if($request->family_member_id==3){
            $familydetails = FamilyDetails::where('family_id', $id)
                ->where('family_member_id', $request->family_member_id)
                ->first();
            if(is_null($familydetails)){
                return $this->sendResponse([],'No child member  details found for this family & member id.');
            }
            else{
                $familydetails->delete(); 
            }
            return $this->sendResponse([],'Successfully delete child member details.');  
        }     
    }
}
