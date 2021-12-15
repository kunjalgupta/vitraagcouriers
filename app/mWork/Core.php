<?php
namespace App\mWork;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class Core
{
    public static function sendFST2SMS($mobile,$template,$awb_number,$city)
    {
      $message="";
      $message = str_replace("{city}",$city,$template);
      $message = str_replace("{awbNo}",$awb_number,$message);
  
       //https://www.fast2sms.com/dev/bulk
        /*$field = array(
            "sender_id" => "FastWP",
            "language" => "english",
            "route" => "qt",
            "numbers" => $mobile,
            "message" => $templateId,
            "variables" => "{#BB#}|{#FF#}",
            "variables_values" => $varsValues
        );*/
        $field = array(
            "route" => "v3",
            "sender_id" => "TXTIND",
            "message" => $message,
            "language" => "english",
            "flash" => 0,
            "numbers" => $mobile
        );
        
        
        $curl = curl_init();
         
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_SSL_VERIFYPEER => 0,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($field),
          CURLOPT_HTTPHEADER => array(
            "authorization: XEQ2A3JM54dki6TbDCpcZIm07VsYneqBFP1jKuNzaU8tHhGlvRgCQLIrwWSzpi6918VxtEZqkcsvhTo2",
            "cache-control: no-cache",
            "accept: */*",
            "content-type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          return $response;
        }
    }

    public static function sendSMS($mobile,$msg)
    {
        $curl = curl_init();
    curl_setopt_array($curl, array(
  CURLOPT_URL => "https://www.fast2sms.com/dev/bulk?authorization=LtdzoaQUlMyRBJmvqn6PAWFjuNDY0SVsEwbGkg1hC27p9r3xKcKeZM1pINg2aAVrw5imok7jPLTlnt4b&sender_id=FSTSMS&message=".urlencode($msg)."&language=english&route=p&numbers=".urlencode($mobile),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_SSL_VERIFYHOST => 0,
  CURLOPT_SSL_VERIFYPEER => 0,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
$send= "cURL Error #:" . $err;
} else {
    $send= $response;
}
        return $send;
    }
    
    public static function validateInput($request,$rules)
    {
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json($validator->errors(),400);
        }
    }
    
    public static function generateOtp()
    {
        try{
        $otp=rand(1000,9999);
        return $otp;
        }catch(Exception $e)
        {
            throw new \Exception("error while generating otp");
        }
    }
    public static function getConstant($key)
    {
        $path="constants.projectConstants.".$key;
        $constant=Config::get($path);
        if(!is_null($constant))
        {
        return $constant;
        }
        else 
            {
                throw new \Exception("Constant not found");
            }
      }
    
    public static function getQuery($key)
    {
        $path="queries.projectQueries.".$key;
        $query=Config::get($path);
        if(!is_null($query))
        {
            return $query;
        }
        else
        {
            throw new \Exception("Query not found");
        }
    }
    
    public static function getLabel($key)
    {
        $path="labels.projectLabels.".$key;
        $label=Config::get($path);
        if(!is_null($label))
        {
            return $label;
        }
        else
        {
            throw new \Exception("Label not found");
        }
    }
}