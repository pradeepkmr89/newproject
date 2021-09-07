<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Validator;

class UserApiController extends Controller
{
    //
   public function signup(Request $request)
    {
       $data =[];
         try {
    
      if(($request->user_phone!='') && ($request->user_otp_code!='') ){
    //match otp
        $exist = User::where('user_phone',$request->user_phone)->where('user_otp_code',$request->user_otp_code)->first();
        if ($exist === null) {
              $response = [
                "success" => false,
                "message" => 'Wrong otp',
            ];
            return response()->json($response, 401);

        }else{

            $exist->user_is_otp_verified = '1';
             $exist->save();
             return response()->json([
                "status" => "true",
                "message" => "Otp match",
                "data" => $exist
            ],200);

        }

      //End match otp            
      }else if($request->user_phone!=''){
   //send otp 
         $validator = Validator::make($request->all(), [
            "user_phone" => "required|digits:10|unique:users", 
        ]);

         if ($validator->fails()) {
            $response = [
                "success" => false,
                "message" => $validator->messages(),
            ];
            return response()->json($response, 401);
        }
     
        $otp = rand(1000,9999);
         $user = new User([
            "user_phone" => $request->user_phone,
            "user_otp_code" => $otp,
            "user_is_otp_verified" => '0',
         ]);
        $user->save(); 
        $data = $user->refresh();
          return response()->json([
                "status" => "true",
                "message" => "Successfully created user!",
                "data" => $data
            ],200);
             //End send otp 
    }
    else if($request->email!=''){
           //Email
         $validator = Validator::make($request->all(), [
            "email" => "required|string|unique:users", 
            "full_name"=>"required|string",
            "user_dob" => "required",
            "user_gender" => "required",
            "location" => "required|string",
            "latitude" => "required",
            "longitude" => "required",
            "image"=> "required|image|mimes:jpeg,png,jpg|max:2048",  
        ]);

         if ($validator->fails()) {
            $response = [
                "success" => false,
                "message" => $validator->messages(),
            ];
            return response()->json($response, 401);
        }
        //upload image
        $imageName = time().'.'.$request->image->extension(); 
        $request->image->storeAs('public/profile/', $imageName);
        //end image
        $user = User::find($request->user_id);
        $user->email = $request->email;
        $user->full_name = $request->full_name;
        $user->user_dob = $request->user_dob;
        $user->user_gender = $request->user_gender;
        $user->location = $request->location;
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;
        $user->image = 'public/profile'.$imageName;
        $user->save();

        return response()->json([
                "status" => "true",
                "message" => "Save successfully",
                "data" => $data
            ],200);


    }else{
        $response = [
                "success" => false,
                "message" => "Empty field",
            ];
            return response()->json($response, 401);
    }
      

        } catch (\Exception $e) {

            return $e->getMessage();
        }

        
    }

    public function login(Request $request)
    {
        $data = [];
        
      if(($request->user_phone!='') && ($request->user_otp_code!='') ){
    //match otp
        $exist = User::where('user_phone',$request->user_phone)->where('user_otp_code',$request->user_otp_code)->first();
        if ($exist === null) {
              $response = [
                "success" => false,
                "message" => 'Wrong otp',
            ];
            return response()->json($response, 401);

        }else{

            $exist->user_is_otp_verified = '1';
             $exist->save();

             $tokenResult = $exist->createToken("Personal Access Token");
            $token = $tokenResult->token;
         
            $token->save();

             return response()->json([
                "status" => "true",
                "message" => "Otp match",
                 "access_token" => $tokenResult->accessToken,
                    "token_type" => "Bearer",
                    "expires_at" => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                "data" => $exist
            ],200);

        }

       }else if($request->user_phone!=''){
   //send otp 
         $validator = Validator::make($request->all(), [
            "user_phone" => "required|digits:10", 
        ]);

         if ($validator->fails()) {
            $response = [
                "success" => false,
                "message" => $validator->messages(),
            ];
            return response()->json($response, 401);
        }
     
        $otp = rand(1000,9999);
        $user = User::where("user_phone", $request->user_phone)->first();

        if($user){

            $user->user_otp_code = $otp;
            $user->user_is_otp_verified = '0';
            $user->save();
              return response()->json([
                "status" => "true",
                "message" => "Send otp",
                "data" => $user
            ],200);

        }else{
             return response()->json([
                "status" => "true",
                "message" => "Mobile number invalid",
                "data" => $data
            ],401);
        }
 
    }

    }

public function get_profile(Request $request){

    if($request->user()){

          return response()->json([
                "status" => "true",
                "message" => "Successfully",
                "data" => $request->user()
            ],200);
       
    }else{
        return response()->json([
                "status" => "false",
                "message" => "Invalid token",
                "data" => []
            ],401);
    }

 
}


//reset_password
public function reset_password(){

}

//change_password
public function change_password(Request $request){


    
}

//Update profile
public function update_profile(Request $request){

        $data = []; 
     $validator = Validator::make($request->all(), [
            "email" => "required|string", 
                "full_name"=>"required|string",
                "user_dob" => "required",
                "user_gender" => "required",
                "location" => "required|string",
                "latitude" => "required",
                "longitude" => "required",
                "image"=> "required|image|mimes:jpeg,png,jpg|max:2048",  
        ]);

         if ($validator->fails()) {
            $response = [
                "success" => false,
                "message" => $validator->messages(),
            ];
            return response()->json($response, 401);
        }
      

      try{

         $user = User::findOrFail(Auth::user()->id);

            if($request->has('full_name')){ 
                $user->full_name = $request->full_name;
            }
             if($request->has('email')){
                $user->email = $request->email;
            }
             if($request->has('latitude')){
             $user->latitude = $request->latitude;
            }
             if($request->has('longitude')){
             $user->longitude = $request->longitude;
             }
             if($request->has('location')){
             $user->location = $request->location;
             }
             if($request->has('user_dob')){
             $user->user_dob = $request->user_dob;
             }

             if($request->has('user_gender')){
             $user->user_gender = $request->user_gender;
             }
              if ($request->image != "") {
                    $imageName = time().'.'.$request->image->extension(); 
                    $request->image->storeAs('public/profile/', $imageName); 
                    $user->image =   $imageName ;
            }

            $user->save();
            $data = $user->refresh();

              $response = [
                "success" => false,
                "message" => 'Update successfully',
                "data" =>$data
            ];
            return response()->json($response, 200);



      }
        catch (ModelNotFoundException $e) {
             $response = [
                "success" => false,
                "message" =>"something went wrong"
            ];
             return response()->json($response);
        }


    
    
}

}
