<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserProfile;
use App\Models\UserBankDetails;
use App\Models\ShippingAddress;
use App\Models\CompanyInfo;
use App\Models\CreditPoints;
use App\Models\ProductList;
use App\Models\Product;
use App\Models\Questionnaire;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Ichtrojan\Otp\Otp;
use Twilio\Rest\Client;
use App\Notifications\ResetPasswordVerificationNotification;
use App\Notifications\RegEmailVerificationNotification;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\DB;
use File;
use Illuminate\Http\JsonResponse;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Token;
use App\Models\TicketSupport;
use App\Models\CutomerSupport;
use App\Models\SupplierProductsMapping;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Mail;
use App\Mail\UserApprovalMail;
use App\Models\UserRoleMapping;
use App\Models\ContactUs;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
  
  
    public function register(Request $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        $role_ids = [];
        if(!is_null($user)){
            $userExistanceRole = UserRoleMapping::where('user_id', $user->id)->get();
            foreach($userExistanceRole as $userrole){
                array_push($role_ids, $userrole->role_id);
            }
            if(count($role_ids)>1){
            }
            else{
                if(in_array($request->role_ids, $role_ids) && count($role_ids)==1){
                    $rolename= $request->role_ids ==2?'Supplier':'Customer';
                    $success['status'] = false;
                    $success['roleyexit'] = true;
                    $success['rolename'] = $rolename=='Supplier'?'Customer':'Supplier';
                    $success['userid'] = $user->id;
                    return $this->sendError($success,'You have been already register as '.$rolename);
                }
                else{
                    $rolename= $request->role_ids ==2?'Customer':'Supplier';
                    $success['status'] = false;
                    $success['roleyexit'] = false;
                    $success['rolename'] = $rolename=='Customer'?'Supplier':'Customer';
                    $success['userid'] = $user->id;
                    return $this->sendError($success,'You have register as '.$rolename);
                }
            }
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'role_ids'  => 'required',
            'phone_number'=>'required|numeric|unique:users,phone_number'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        
        $role_ids = explode(',', $request->role_ids); //string to aray explode , implode-arry to string
        $role_ids=(array)$role_ids;

        $user = new User;
        $user->name = $request->name;
        $user->email =  $request->email; 
        $user->username = $request->username;
        $user->password_txt = $request->password;
        $user->password = bcrypt($request->password);
        $user->phone_number =  $request->phone_number;
        $user->save();
        $userprofile = new UserProfile;
        $userbankdetails = new UserBankDetails;
        $shippingaddress = new ShippingAddress;
        $companyinfo = new CompanyInfo;

        $userprofile->user_id = $user->id;
        $userprofile->approval_status = 'Disapproved';
        $userbankdetails->user_id = $user->id;
        $shippingaddress->user_id = $user->id;
        
        $userprofile->save();
        $userbankdetails->save();
        $shippingaddress->save();

        $creditpoint = new CreditPoints;
        $custsupport = new CutomerSupport;

        foreach($role_ids as $role){
            $userrolemap = new UserRoleMapping;
            $role = (int)$role;
            $userrolemap->user_id = $user->id;
            $userrolemap->role_id =  $role;
            $userrolemap->approval_status = 'Disapproved';
            $userrolemap->password = bcrypt($request->password);
            $userrolemap->password_txt = $request->password;
            if($role == 2){
                $tickets='WSES-';
                $userrolemap->ref_id = $tickets.random_int(100000, 999999);
                $companyinfo->user_id = $user->id;
                $companyinfo->save();
                $creditpoint->user_id = $user->id;
                $creditpoint->save();
                $custsupport->user_id = $user->id;
                $custsupport->save();
            }
            elseif($role == 3){
                $ticketss='WSEC-';
                $userrolemap->err_rate = 1;
                $userrolemap->ref_id = $ticketss.random_int(100000, 999999);
            } 
            $userrolemap->save();
        }
        return $this->sendResponse($user, 'Registration is Successfully Done.');
    }

    public function otpRegister(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required',
            'otp' => 'required|numeric',
            'role_id' =>'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation error', $validator->errors());
        }
        $this->otp = new Otp;
        $otp2 = $this->otp->validate($request->email, $request->otp);
        if(!$otp2->status){
           return response()->json($otp2, 401);
        }
        $userrolemap = new UserRoleMapping;
        $userrolemap->user_id =$request->user_id;
        $userrolemap->role_id =  $request->role_id;
        $userrolemap->approval_status = 'Disapproved';
        $userrolemap->password = bcrypt($request->password);
        $userrolemap->password_txt = $request->password;

        $userbankdetails = new UserBankDetails;
        $shippingaddress = new ShippingAddress;
        $companyinfo = new CompanyInfo;
        $userbankdetails->user_id = $request->user_id;
        $shippingaddress->user_id =$request->user_id;
        $userbankdetails->save();
        $shippingaddress->save();
        $creditpoint = new CreditPoints;
        $custsupport = new CutomerSupport;


        if($request->role_id == 2){
            $tickets='WSES-';
            $userrolemap->ref_id = $tickets.random_int(100000, 999999);
            $companyinfo->user_id =$request->user_id;
            $companyinfo->save();
            $creditpoint->user_id = $request->user_id;
            $creditpoint->save();
            $custsupport->user_id = $request->user_id;
            $custsupport->save();
        }
        elseif($request->role_id == 3){
            $ticketss='WSEC-';
            $userrolemap->ref_id = $ticketss.random_int(100000, 999999);
        } 
        $userrolemap->save();
        return $this->sendResponse($userrolemap, 'Registration is Successfully Done.');
    }

    public function login(Request $request): JsonResponse
    {

         // Validate the role_id
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Attempt to find the user by email or username
        $user = User::where('email', $request->usernameoremail)
                    ->orWhere('username', $request->usernameoremail)
                    ->first();

        if (!$user) {
            return $this->sendError('Login credentials are invalid.', ['error' => 'Unauthorised']);
        }

        // Retrieve the user_role_mapping record for the given role_id
        $userRoleMapping = UserRoleMapping::where([
            ['user_id', $user->id],
            ['role_id', $request->role_id]
        ])->first();

        if (!$userRoleMapping) {
            return $this->sendError('Login credentials are invalid.', ['error' => 'Unauthorised']);
        }

        // Verify the password from the user_role_mapping table
        if (!Hash::check($request->password, $userRoleMapping->password)) {
            return $this->sendError('Login credentials are invalid.', ['error' => 'Unauthorised']);
        }

        // Generate a new JWT token for the user
        try {
            if (!$token = JWTAuth::fromUser($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        // Return the response with the token and user details
        return response()->json([
            'user' => $user,
            'success' => true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'usertype' => UserRole::where("id", $request->role_id)->get('type'),
            'approval_status' => $userRoleMapping->approval_status,
            'message' => 'User login successfully.'
        ]);

        //old login 
        // $validator = Validator::make($request->all(),[
        //     'role_id'=>'required|numeric'
        // ]);
        // if($validator->fails()){
        //     return $this->sendError('Validation Error.', $validator->errors());
        // }
    
        // if(JWTAuth::attempt(['email' => $request->usernameoremail, 'password' => $request->password])){
        //     $credentials = JWTAuth::attempt(['email' => $request->usernameoremail, 'password' => $request->password]);
        //     $user = JWTAuth::user();
        // }else if(JWTAuth::attempt(['username' => $request->usernameoremail, 'password' => $request->password])){
        //     $credentials = JWTAuth::attempt(['username' => $request->usernameoremail, 'password' => $request->password]);
        //     $user = JWTAuth::user();
        // }
        // else{
        //     return $this->sendError('Login credentials are invalid.', ['error' => 'Unauthorised']);
        // }
        // $role_id = $request->role_id;
        // $role_ids =[];
        // // $userrolemaps = UserRoleMapping::where('user_id', $user->id)->get();
        // $userrolemaps = UserRoleMapping::where([['user_id', $user->id],['role_id', $role_id]])->first();
        // if(is_null($userrolemaps)){
        //     return $this->sendError('Login credentials are invalid.', ['error' => 'Unauthorised']);
        // }
        // // foreach($userrolemaps as $userrole){
        // //     array_push($role_ids, $userrole->role_id);
        // // }
        // // if(!in_array($role_id , $role_ids)){
        // //     return $this->sendError('Login credentials are invalid.', ['error' => 'Unauthorised']);
        // // }
        // try {
        //     if (!$token = $credentials) {
        //         return response()->json([
        //          'success' => false,
        //          'message' => 'Login credentials are invalid.',
        //         ], 400);
        //     }
        // } catch (JWTException $e) {
        //     return $credentials;
        //     return response()->json([
        //          'success' => false,
        //          'message' => 'Could not create token.',
        //         ], 500);
        // }
        // return response()->json([
        //     'user' => $user,
        //     'success' => true,
        //     'token' => $token,
        //     'token_type' => 'bearer',
        //     'expires_in' => auth()->factory()->getTTL() * 1,
        //     'usertype' =>  UserRole::where("id",$role_id)->get('type'),
        //     'approval_status'=>$userrolemaps->approval_status,
        //     'message' => 'User login Successfully.'
        // ]);
    }
    
    public function userRole(Request $request): JsonResponse
    {
        $roles = UserRole::all();
        return Response::json($roles, 200);
    }
    public function sendotp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        //By twilio 
        // $otp = mt_rand(100000,999999);
        // echo 'token-'. env('TWILIO_PHONE_NUMBER');
        // $twilioSid = env('TWILIO_SID');
        // $twilioToken = env('TWILIO_AUTH_TOKEN');
        // $twilioPhoneNumber = env('TWILIO_PHONE_NUMBER');

        // $client = new Client($twilioSid, $twilioToken);
        // $message = $client->message->create(
        //     $request->email,
        //     [
        //         'from'=> $twilioPhoneNumber,
        //         'body'=>'Your otp for password reset it:'. $otp,
        //     ]
        // );
        // return response()->json(['message'=> 'OTP sent successfully.']);

        //By Email
        $user = User::where('email', '=', $request->email)->first();
        if($request->flag){
            $user->notify(new RegEmailVerificationNotification($request->rolename));
        }else{
            $user->notify(new ResetPasswordVerificationNotification());
        }
        $success['success'] = true;
        return response()->json($success, 200);
    }
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|max:6',
            'password' => 'required',
            'role_id'=>'required|numeric'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $this->otp = new Otp;
        $otp2 = $this->otp->validate($request->email, $request->otp);
        if (!$otp2->status) {
            return response()->json($otp2, 401);
        }
        // $user = User::where('email', $request->email)->first();
        // $user->update(['password_txt' => $request->password]);
        // $user->update(['password' => bcrypt($request->password)]);

        // //update password in role_map table
        // $userrolemap = UserRoleMapping::where([['user_id', $user->id],['role_id', $request->role_id]])->first();
        // $userrolemap->update(['password_txt'=>$request->password]);
        // $userrolemap->update(['password'=>bcrypt($request->password)]);
        // $success['success'] = true;
        // return response()->json($success, 200);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError('User not found.', ['error' => 'User not found']);
        }
    
        $user->update([
            'password_txt' => $request->password,
            'password' => bcrypt($request->password)
        ]);
    
        // Update password in user_role_mapping table
      
        $userRoleMapping = UserRoleMapping::where([
            ['user_id', $user->id],
            ['role_id', $request->role_id]
        ])->first();


    
        if (!$userRoleMapping) {
            return $this->sendError('Role mapping not found.', ['error' => 'Role mapping not found']);
        }
    
        $userRoleMapping->password_txt = $request->password;
        $userRoleMapping->password = bcrypt($request->password);
        $userRoleMapping->save();
        // $userRoleMapping->update([
        //     'password_txt' => $request->password,
        //     'password' => bcrypt($request->password)
        // ]);
    
        return response()->json(['success' => true], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $forever = true;
        JWTAuth::parseToken()->invalidate( $forever );
        return response()->json('Successfully logged out',200);
    }

    public function getUserProfile($user_id, $role_id): JsonResponse
    {
        $user = User::with(['userprofile.userbankdetails.usershippingaddress.userrolemap'=> function($query) use ($role_id){
            $query->where('role_id', $role_id);
        }])->where("id", $user_id)->get();
        if ($user->isEmpty()) {
            return response()->json('User Not Found Please Register User...', 404);
        } 
        else {
            $data = compact('user');
            return response()->json($data, 200);
        }
    }

    public function updateUserProfile(Request $request, $user_id): JsonResponse
    {
        // 'required|numeric|unique:users,phone_number,'.$user_id.',id',
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required|unique:users,username,'.$user_id.',id',
            'email' => 'required|email|unique:users,email,'.$user_id.',id',
            // 'profile_picture'=>'image|max:2048',
            'gender' => 'required|in:M,F,O',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'mobile' => 'required|numeric|unique:userprofiles,mobile,'.$user_id.',user_id',
            'address' => 'required',
            'zipcode' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user = User::find($user_id);        
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $userprofile = UserProfile::where('user_id','=',$user_id)->first();
        if($request->hasfile('profile_picture'))
        {
            $destination = 'uploads/profiles_pictures/'.$userprofile->profile_picture;
            if(File::exists($destination))
            {
                File::delete($destination);
            }
            $file = $request->file('profile_picture');
            $filename = $file->getClientOriginalName();
            $file->move('uploads/profiles_pictures/', $filename);
            $userprofile->profile_picture = $filename;
        }
        $userprofile->mobile = $request->mobile;
        $userprofile->gender = $request->gender;
        $userprofile->date_of_birth = $request->date_of_birth;
        $userprofile->address = $request->address;
        $userprofile->zipcode = $request->zipcode;
        $userprofile->city = $request->city;
        $userprofile->state = $request->state;
        $userprofile->country = $request->country;
        $user->save();
        $userprofile->save();
        // $success['user'] = $user;
        $success['success'] = true;
        return $this->sendResponse($success, 'Profile updated successfully.');
    }

    public function getquestions(Request $request): JsonResponse
    {
        $parent_ques_id =  $request->parent_ques_id;
        $user_id = $request->user_id;
        if($request->flag == 'getsubmittedques'){
            $questions = Questionnaire::where('customer_id', $user_id)
            ->get();
        }
        else{
            $questions = Questionnaire::where('parent_ques_id', $parent_ques_id)
            ->where('customer_id', $user_id)
            ->get();
        }
        return $this->sendResponse($questions, 'Successfully get all the questions.');  
    }

    public function addProduct(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'=>'required',
            'category' => 'required',
            'subcategory' => 'required',
            'product' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $product=$request->product;
        $user_id =$request->user_id;
        $recordlist=SupplierProductsMapping::where("supplier_id", $user_id)->get();
        $listData='';
        $prodnamelist=[];
        $ids =[];
        foreach($recordlist as $record)
        {   
            if(in_array($record->product_id, $request->product)){
                $id= $record->product_id;
                if(!(in_array($id ,$ids))){
                    array_push($ids, $id);
                }
                $prod_name = ProductList::whereIn('id',$ids)->get();
                foreach($prod_name as $p){
                    $name = $p->name; 
                    if(!(in_array( $name ,$prodnamelist ))){
                        array_push($prodnamelist,  $p->name);
                    }  
                }
                $prodslist= implode(',',$prodnamelist);
                 $listData =  $prodslist." "."Already Exist.";
            }
        }
        if ($listData !='') {
            return $this->sendError($listData);
        }
        $product = collect($request->product);
        for($i=0;$i<$product->count();$i++)
        {
            $suppliermapping = new SupplierProductsMapping;
            $suppliermapping->supplier_id = $request->user_id;
            $suppliermapping->category = $request->category;
            $suppliermapping->subcategory = $request->subcategory;
            $suppliermapping->product_id = $product[$i];
            $suppliermapping->save();
        }
        $success['success'] = true;
        return $this->sendResponse($success, 'Product added successfully.');
    }
    
    public function updateUserBankDetails(Request $request, $user_id):JsonResponse
    {
   
        $validator = Validator::make($request->all(),[
          'ifsc_code'=> 'required|numeric',
          'account_type' =>'required',
          'account_number' =>'required|numeric',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        $userbankdetail = UserBankDetails::where('user_id','=',$user_id)->first();
        $userbankdetail->ifsc_code = $request->ifsc_code;
        $userbankdetail->account_type = $request->account_type;
        $userbankdetail->account_number = $request->account_number;
        $userbankdetail->save();
        $success['userbankdetail'] = $userbankdetail;
        $success['success'] = true;
        return $this->sendResponse($success, 'Bank Details Updated successfully.');
    }

    public function updateUserShippingAddress(Request $request, $user_id):JsonResponse
    {
        $validator = Validator::make($request->all(),[
           'address'=> 'required',
           'zipcode'=>'required|numeric',
           'city'=>'required',
           'state'=>'required',
           'country'=>'required'  
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        $shippingaddr = ShippingAddress::where('user_id','=',$user_id)->first();
        $shippingaddr->address = $request->address;
        $shippingaddr->zipcode = $request->zipcode;
        $shippingaddr->city = $request->city; 
        $shippingaddr->state = $request->state;
        $shippingaddr->country = $request->country;
        $shippingaddr->save();
        $success['shippingaddr'] = $shippingaddr;
        $success['success']=true;
        return $this->sendResponse($success, 'ShippingAddress Updated Successfully.');
    }

        public function getCompanyInfo($user_id): JsonResponse
        {
            $user = DB::TABLE('users')
                ->where('users.id', $user_id)
                ->JOIN('company_infos', 'users.id', '=', 'company_infos.user_id')
                ->JOIN('customer_support', 'users.id', '=', 'customer_support.user_id')
                ->get();
            if ($user->isEmpty()) {

                return response()->json('cannot get data...', 401);
            } 
            else {
                $data = compact('user');
                return response()->json($data, 200);
            }
            
        }
    public function updateCompanyInfo(Request $request, $user_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'companyname' => 'sometimes',
            'contactpersonname' => 'sometimes|max:100',
            'companyaddr' => 'sometimes',
            'companyemail' => 'sometimes',
            'companyphone' => 'sometimes',
            'businessname' => 'sometimes',
            'businesstype' => 'sometimes',
            'businessregnum' => 'sometimes',
            'taxidentifynum' => 'sometimes',
            'contactname' => 'sometimes',
            'contactemail' => 'sometimes',
            'contactmobile' => 'sometimes',
            'businessregcertificate' => 'sometimes',
            'license' => 'sometimes',
            'financialstability' => 'sometimes',
            'bankletter' => 'sometimes',
            'businessperformancemetric' => 'sometimes',
            'businessref' => 'sometimes'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $companyinfo = CompanyInfo::where('user_id','=',$user_id)->first();
        $companyinfo->companyName = $request->companyname;
        $companyinfo->contactpersonname = $request->contactpersonname;
        $companyinfo->companyaddr = $request->companyaddr;
        $companyinfo->companyemail = $request->companyemail;
        $companyinfo->companyphone = $request->companyphone;
        $companyinfo->businessname = $request->businessname;
        $companyinfo->businesstype = $request->businesstype;
        $companyinfo->businessregnum = $request->businessregnum;
        $companyinfo->taxIdentifynum = $request->taxidentifynum;
        $companyinfo->contactname = $request->contactname;
        $companyinfo->contactemail = $request->contactemail;
        $companyinfo->contactmobile = $request->contactmobile;
        $companyinfo->businessperform = $request->businessperformancemetric;

        if($request->hasfile('businessregcertificate')){
            $destination = 'businessRegCertificate/'. $companyinfo->businessregcertificate;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('businessregcertificate');
            $filename = 'businessreg'.'_'.$file->getClientOriginalName();
            $file->move('businessRegCertificate/', $filename);
            $companyinfo->businessregcertificate =  $filename;
        }
        if($request->hasfile('license')){
            $destination = 'licenseCertificate/'. $companyinfo->licensecertificate;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('license');
            $filename = 'license'.'_'.$file->getClientOriginalName();
            $file->move('licenseCertificate/', $filename);
            $companyinfo->licensecertificate =  $filename;
        }

        if($request->hasfile('financialstability')){
            $destination = 'financialStability/'. $companyinfo->financialstability;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('financialstability');
            $filename = 'financial'.'_'.$file->getClientOriginalName();
            $file->move('financialStability/', $filename);
            $companyinfo->financialstability =  $filename;
        }

        if($request->hasfile('bankletter')){
            $destination = 'bankLetter/'. $companyinfo->bankletter;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('bankletter');
            $filename = 'bankletter'.'_'.$file->getClientOriginalName();
            $file->move('bankLetter/', $filename);
            $companyinfo->bankletter =  $filename;
        }

        if($request->hasfile('businessref')){
            $destination = 'businessRef/'. $companyinfo->businessref;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('businessref');
            $filename = 'businessref'.'_'.$file->getClientOriginalName();
            $file->move('businessRef/', $filename);
            $companyinfo->businessref =  $filename;
        }

        $companyinfo->save();
        $success['companyinfo'] = $companyinfo;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Supplier Company Information Updated');
    }
    public function updateQltyStandard(Request $request, $user_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qltystandard' => 'sometimes',
            'qltycertificate' => 'sometimes|max:100',
            'financialstatement' => 'sometimes',
            'qltystandrdfile' => 'sometimes',
            'qltycertificatefile' => 'sometimes',
            'qltyfinancialfile' => 'sometimes',
            'refundpolicy'=> 'sometimes'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $companyinfo = CompanyInfo::where('user_id','=',$user_id)->first();
        $companyinfo->qltystandard = $request->qltystandard;
        $companyinfo->qltycertificate = $request->certificate;
        $companyinfo->financialstatement = $request->financialstatement;
        $companyinfo->refundpolicy = $request->refundpolicy=='true'?'agreed':NULL;
        

        $companyinfo->qltystandrdfile = $request->qltystandrdfile;
        $companyinfo->qltycertificatefile = $request->qltycertificate;
        $companyinfo->qltyfinancialfile = $request->qltyfinancialfile;
     

        if($request->hasfile('qltystandrdfile')){
            $destination = 'qltyStandrdfile/'. $companyinfo->qltystandrdfile;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('qltystandrdfile');
            $filename = 'qltystandrd'.'_'.$file->getClientOriginalName();
            $file->move('qltyStandrdfile/', $filename);
            $companyinfo->qltystandrdfile =  $filename;
        }
        if($request->hasfile('qltycertificate')){
            $destination = 'qltyCertificatefile/'. $companyinfo->qltycertificatefile;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('qltycertificate');
            $filename = 'qltycertificate'.'_'.$file->getClientOriginalName();
            $file->move('qltyCertificatefile/', $filename);
            $companyinfo->qltycertificatefile =  $filename;
        }

        if($request->hasfile('qltyfinancialfile')){
            $destination = 'qltyFinancialfile/'. $companyinfo->qltyfinancialfile;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('qltyfinancialfile');
            $filename = 'qltyfinancial'.'_'.$file->getClientOriginalName();
            $file->move('qltyFinancialfile/', $filename);
            $companyinfo->qltyfinancialfile =  $filename;
        }

        $companyinfo->save();
        $user = UserProfile::where('user_id', $user_id)->first();
        $userrolemap = UserRoleMapping::where([['user_id', $user_id],['role_id', 2]])->first();
        if($companyinfo->aggreement === 'agreed' && $companyinfo->termandcondition === 'agreed' && $companyinfo->refundpolicy === 'agreed'){
            $user->approval_status = 'Approved';
            $userrolemap->approval_status = 'Approved';
        }
        else{
            $user->approval_status = 'Disapproved';
            $userrolemap->approval_status = 'Disapproved';
        }
        $user->save();
        $userrolemap->save();
        $success['companyinfo'] = $companyinfo;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Supplier Quality Standard Updated');
    }
    
    public function updateSuppExperience(Request $request, $user_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'aboutbusiness' => 'sometimes',
            'custserved' => 'sometimes|max:100',
            'testimonials' => 'sometimes',
            'suppagreement' => 'sometimes',
            'termandcondition' => 'sometimes'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $companyinfo = CompanyInfo::where('user_id','=',$user_id)->first();
        $companyinfo->aboutbusiness = $request->aboutbusiness;
        $companyinfo->customerserved = $request->custserved;
        $companyinfo->testimonialsref = $request->testimonials;
        if($request->suppagreement){
            $companyinfo->aggreement = $request->suppagreement=='true'?'agreed':NULL;
        }
        if($request->termandcondition){
            $companyinfo->termandcondition = $request->termandcondition=='true'?'agreed':NULL;
        }
        $companyinfo->save();
        $user = UserProfile::where('user_id', $user_id)->first();
        $userrolemap = UserRoleMapping::where([['user_id', $user_id],['role_id', 2]])->first();
        if($companyinfo->aggreement === 'agreed' && $companyinfo->termandcondition === 'agreed' && $companyinfo->refundpolicy === 'agreed'){
            $user->approval_status = 'Approved';
            $userrolemap->approval_status = 'Approved';
        }
        else{
            $user->approval_status = 'Disapproved';
            $userrolemap->approval_status = 'Disapproved';
        }
        $user->save();
        $userrolemap->save();
        $success['companyinfo'] = $companyinfo;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Supplier Experience Updated');
    }

    public function customerSupport(Request $request, $user_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supporthr' => 'sometimes',
            'supportaddr' => 'sometimes',
            'mobile' => 'sometimes',
            'email' => 'sometimes',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $custsupport = CutomerSupport::where('user_id','=',$user_id)->first();
        $custsupport->phoneno = $request->mobile;
        $custsupport->supporthr = $request->supporthr;
        $custsupport->email = $request->email;
        $custsupport->supportaddr = $request->supportaddr;

        $custsupport->save();
        $success['customersupport'] = $custsupport;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Customer Support Updated');
    }

    //contact-us
    public function contactUs(Request $request) :JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'mobile'=>'sometimes',
            'companyname'=>'sometimes',
            'email'=>'required',
            'message'=>'required'
        ]);

        if($validator->fails()){
            return sendError('Validation Error.', $validator->errors());
        }

        $contactus = new ContactUs();
        $contactus->name = $request->name;
        $contactus->mobile = $request->mobile;
        $contactus->company_name = $request->companyname;
        $contactus->email = $request->email;
        $contactus->message = $request->message;
        $contactus->save();
        
        $success['data'] =  $contactus;
        $success['status'] = true;
        return $this->sendResponse( $success, 'Your details successfully submitted.');
    }

    
    public function getcustomerSupport(Request $request, $user_id): JsonResponse
    {
       
        $custsupport = CutomerSupport::where('user_id','=',$user_id)->first();
        if(is_null($custsupport)){
            return $this->sendError('Customer not found.');
        }
        $success['customersupport'] = $custsupport;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Customer Support Updated');
    }

    public function CustomerList(Request $request)
    {  
        DB::enableQueryLog();
        $customername = $request->customername;
        $customeremail = $request->customeremail;

        $result = DB::table('userprofiles')
        ->select(
            'userprofiles.*','users.*','users.created_at as registereddate',
            'user_role_mapping.*'
            )
        ->join('users', 'userprofiles.user_id', '=', 'users.id')
        ->join('user_role_mapping', 'userprofiles.user_id', '=', 'user_role_mapping.user_id')
        ->where('user_role_mapping.role_id',3);
        // $result = UserProfile::withWhereHas('customerlist.userrolemap', function (Builder $query) 
        //  use ($customername,$customeremail) {
        //     $query->where("role_id", 3);
        //     // where('name', 'like', "%{$customername}%")
        //     // ->where('email', 'like', "%{$customeremail}%");
        // });

        if($request->customername!=""){
            $result = $result->where('name', 'like', '%' . $request->customername . '%');
        }
        if($request->customeremail!=""){
            $result = $result->where('email', 'like', '%' . $request->customeremail . '%');
        }
        if($request->phoneNo!=""){
            $result = $result->where('phone_number', 'like', '%' . $request->phoneNo . '%');
        }
        $result=$result->get();
        $query = DB::getQueryLog();
        $query = end($query);
        return $this->sendResponse($result,  'Successfully Fetch...');
    } 

    public function SupplierList(Request $request)
    {  
        DB::enableQueryLog();
        $suppliername = $request->suppliername;
        $supplieremail = $request->supplieremail;

        if($request->flag){
        //     $result = UserProfile::withWhereHas('supplierlist', function (Builder $query) 
        //     use ($suppliername,$supplieremail) {
        //        $query->where("role_id", 2)->
        //        where('name', 'like', "%{$suppliername}%")->where('email', 'like', "%{$supplieremail}%");
        //    });

           $result = DB::table('userprofiles')
           ->select(
               'userprofiles.*','users.*','users.created_at as registereddate',
               'user_role_mapping.*', 'user_role_mapping.approval_status as approvalstatus' ,
               )
           ->join('users', 'userprofiles.user_id', '=', 'users.id')
           ->join('user_role_mapping', 'userprofiles.user_id', '=', 'user_role_mapping.user_id')
           ->where('user_role_mapping.role_id',2);


            $suppprod = SupplierProductsMapping::with('usersdata.userprofile','usersdata.orderrating')
            ->where('product_id',$prod_id);
            $suppprod=$suppprod->get();

        }
        else{
            $result = DB::table('userprofiles')
            ->select(
                'userprofiles.*','users.*','users.created_at as registereddate',
                'user_role_mapping.*', 'user_role_mapping.approval_status as approvalstatus',
                )
            ->join('users', 'userprofiles.user_id', '=', 'users.id')
            ->join('user_role_mapping', 'userprofiles.user_id', '=', 'user_role_mapping.user_id')
            ->where('user_role_mapping.role_id',2);
            //     $result = UserProfile::withWhereHas('supplierlist', function (Builder $query) 
            //     use ($suppliername,$supplieremail) {
            //        $query->where("role_id", 2)->
            //        where('name', 'like', "%{$suppliername}%")->where('email', 'like', "%{$supplieremail}%");
            //    });
        }
        if($request->suppliername!=""){
            $result = $result->where('name', 'like', '%' . $request->suppliername . '%');
        }
        if($request->supplieremail!=""){
            $result = $result->where('email', 'like', '%' . $request->supplieremail . '%');
        }
        if($request->phoneno!=""){
            $result = $result->where('phone_number', 'like', '%' . $request->phoneno . '%');
        }
        $result=$result->get();
        $query = DB::getQueryLog();
        $query = end($query);
        return $this->sendResponse($result,  'Successfully Fetch...');
    } 

    public function SearchSupplier(Request $request)
    {  
        DB::enableQueryLog();
        $prod_id = $request->prod_id;
        $user_id = $request->user_id;
        $finaldata = [];
        $result = SupplierProductsMapping::with('usersdata.userprofile','usersdata.orderrating', 'usersdata.deliveredorder')
        ->where('product_id',$prod_id)
        ->whereNotIn('supplier_id', [$user_id]);
        $result=$result->get();
       
        foreach($result as $r){
            $id=$r->supplier_id;
            $product_info = Product::where([['product_id','=',$prod_id],['user_id','=',$id]])->get();
            foreach($r->usersdata as $user){

                $userrolemap = UserRoleMapping::where([['user_id', $id], ['role_id', 2]])->first();
                if($userrolemap){
                    $approval_status = $userrolemap->approval_status;
                    if($approval_status !== 'Disapproved'){
                        $r['product_info'] = $product_info;
                        array_push($finaldata, $r);
                    }
                }
                // $approval_status = $user->userprofile->approval_status;
                // if($approval_status !== 'Disapproved'){
                //     $r['product_info'] = $product_info;
                //     array_push($finaldata, $r);
                // }
            }
        }
        $query = DB::getQueryLog();
        $query = end($query);
        return $this->sendResponse($finaldata,  'Successfully Fetch Supplier...');
    }

    public function SupplierApproval(Request $request, $user_id):JsonResponse
    {
        $user = UserProfile::where('user_id',$user_id)->first();
        echo  $request->role_id;
        $userrole = UserRoleMapping::where([['user_id', $user_id], ['role_id', $request->role_id]])->first();
        // if($user){
        //     if($user->approval_status=== 'Approved'){
        //         $user->approval_status = 'Disapproved';
        //     }
        //     else{
        //         $user->approval_status = 'Approved';
        //     }
        //     $user->save();
        // }
        if($userrole){
            if($userrole->approval_status=== 'Approved'){
                $userrole->approval_status = 'Disapproved';
            }
            else{
                $userrole->approval_status = 'Approved';
            }
            $userrole->save();
        }
        $userdata = User::where('id', $user_id)->first();
        Mail::to($userdata->email)->send(new UserApprovalMail($user->user_id, $userdata->name,$userrole->approval_status));
        return $this->sendResponse([], 'Supplier approved...');
    }

    public function  deleteCustomer(Request $request, $id, $role_id)
    {
        $user = User::where("id", $id)->first();
        $users  = UserRoleMapping::where('user_id', $id)->get();
        if(count($users)>1){
            $userrole = UserRoleMapping::where([['user_id', $id],['role_id',$role_id]])->first();
            if ($userrole) {
                $userrole->delete();
            }
        }
        else{
            $userrole = UserRoleMapping::where([['user_id', $id],['role_id',$role_id]])->first();
            if($userrole) {
                $userrole->delete();
            }
            $user->delete();
        }
        return $this->sendResponse([], 'User deleted.');
    }
  
    public function ticketSupport(Request $request){
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|numeric',
            'suplier_id' => 'required|numeric',
            'order_id' => 'required|numeric',
            'complainBy' => 'required',
            'comment'  => 'required',
            'commentType' => 'required',
            'commentCount' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        } 
        $ticket = new TicketSupport;
        $tickets='WSET-';
        $ticket->ticketNumber = $tickets.random_int(100000, 999999);
        $ticket->customer_id = $request->customer_id;
        $ticket->suplier_id = $request->suplier_id;
        $ticket->order_id = $request->order_id;
        $ticket->complainBy = $request->complainBy;
        $ticket->comment = $request->comment;
        $ticket->commentType = $request->commentType;
        $ticket->commentCount = $request->commentCount;
        $ticket->save();
        return $this->sendResponse($ticket, 'Registration is Successfully Done.');
    }
}
