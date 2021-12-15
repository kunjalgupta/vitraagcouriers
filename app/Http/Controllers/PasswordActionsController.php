<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\PasswordResets;
use App\Model\User;
use Illuminate\Support\Str;
use Hash;
use Carbon\Carbon;
use Mail;
use App\Mail\ForgotPassword;

class PasswordActionsController extends Controller
{
    public function sendForgotPassword($email)
    {
        if(User::where('email',$email)->count()>0)
        {
            $token = Str::random(32);
            //PasswordResets::create(['email'=>$email,'token'=>Hash::make($token),'crt_dt'=>Carbon::now()->toDateTimeString()]);   
            $url=$this->generateLink($email,$token);
            $user=User::where('email',$email)->first();
            $this->sendEmail($user,$url);
            return response()->json(['message'=>'email sent successfully'],201);
        }        
    }
    public function generateLink($email,$token)
    {
        return url("ForgotPassword?e={$email}&t={$token}");
    }
    public function sendEmail($user,$url)
    {
        Mail::send(new ForgotPassword($user,$url));
    }
    public function validateForgotPassword(Request $request)
    {
        $flag=false;
        $tokenData=PasswordResets::where('email',($request['email']))->first();
        if(!is_null($tokenData))
        {
            if(Hash::check($request->token,$tokenData->token))
            {
                $time = Carbon::now()->diffInMinutes($tokenData->crt_dt);
                if($time<60)
                {
                    $flag=true;
                }
            }
        }
        return response()->json(['flag'=>$flag],200);
    }
    public function resetForgotPassword(Request $request)
    {
        $flag=false;
        $tokenData=PasswordResets::where('token',$request->token)->first();
        
        if($tokenData->email===$request->email)
        {
            $user=User::where('email',$tokenData->email)->first();
            $user->password=$request->password;
            $flag=$user->update();
        }

        if($flag)
        {
            return response()->json(['message'=>'Password successfully updated'],200);            
        }else{
            return response()->json(['message'=>'Password Not updated'],400);            
        }
    }
}
