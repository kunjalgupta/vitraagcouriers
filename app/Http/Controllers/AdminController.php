<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\mWork\Core;
use App\mWork\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Model\Actionlog;
use App\Model\Sender;
use App\Http\Requests\SenderRequest;

class AdminController extends Controller
{
    public function getCourierSalesCount($year)
    {
        $data = [];
        $months = [
            '1' => 'JANUARY',
            '2' => 'FEBRUARY',
            '3' => 'MARCH',
            '4' => 'APRIL',
            '5' => 'MAY',
            '6' => 'JUNE',
            '7' => 'JULY',
            '8' => 'AUGUST',
            '9' => 'SEPTEMBER',
            '10' => 'OCTOBER',
            '11' => 'NOVEMBER',
            '12' => 'DECEMBER'
        ];
        try {
            if (!auth()->user()->isCompany()) {
                return response()->json(['message' => 'Action Not Authorized', 'status' => 0, 'data' => []], 401);
            }
            $parameters = [$year];
            $query = Core::getQuery('COURIER_SALES_COUNT');
            $resultSet = DB::select($query, $parameters);

            for ($i = 1; $i <= 12; $i++) {
                $flag = false;
                foreach ($resultSet as $result) {
                    if ($result->month == $i) {
                        $data[$i] = [
                            'year' => $result->year,
                            'month' => $months[$result->month],
                            'courier_sales_count' => $result->courier_sales_count
                        ];

                        $flag = true;
                        break;
                    }
                }
                if (!$flag) {
                    $data[$i] = [
                        'year' => $year,
                        'month' => $months[$i],
                        'courier_sales_count' => 0
                    ];
                }
            }
        } catch (\Exception $e) {
            dd($e);
            return JsonResponse::sendErrorRes();
        }
        return $data;
    }

    public function getDashboarCounts()
    {
        $data = [];
        try {
            if (!auth()->user()->isCompany()) {
                return response()->json(['message' => 'Action Not Authorized', 'status' => 0, 'data' => []], 401);
            }
            $query = Core::getQuery('USER_COUNT');

            $parameters1 = [102];
            $miniDealer = DB::select($query, $parameters1);
            $data['mini_dealer_count'] = $miniDealer[0]->count;

            $parameters2 = [103];
            $franchise = DB::select($query, $parameters2);
            $data['franchise_count'] = $franchise[0]->count;

            $parameters3 = [104];
            $hub = DB::select($query, $parameters3);
            $data['hub_count'] = $hub[0]->count;

            $query = Core::getQuery('TOTAL_COURIER_COUNT');
            $courier = DB::select($query, []);
            $data['courier_count'] = $courier[0]->count;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
        return $data;
    }

    public function viewCourierDetails($id)
    {
        try {
            $parameters = [$id];
            $query = Core::getQuery('COURIER_REPORT_VIEW');
            $resultSet = DB::select($query, $parameters);
            return $resultSet;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function sendsms()
    {
        try {
           $msgToSender = Core::sendFST2SMS(9173913952, 42371, 123);
            return $msgToSender;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function ActionlogList()
    {
        try {
             $Actionlog = Actionlog::select('action_logs.*','users.name as user_name',DB::raw('DATE_FORMAT(action_logs.created_at, "%Y-%m-%d %H:%i:%s") as created_date'))->leftjoin('users','users.id','=','action_logs.user_id')->get();

             $ResponseData = array("code" => 100,'message'=>"Actionlog List get sucessfully.",'status'=>'success','data'=>$Actionlog);
             return  $this->responseFun($ResponseData);
               } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
     public function sendFST2SMStosender(SenderRequest $request)
    {
      $message="";
      
      $Sender = Sender::select('mobile')->distinct()->offset($request->start_number)->limit($request->end_number)->get()->toArray();
//echo "<pre>";
//print_r($Sender);exit();
       

        foreach ($Sender as $key => $value) {
        
        $field = array(
            "route" => "v3",
            "sender_id" => "TXTIND",
            "message" => $request->smstosender,
            "language" => "english",
            "flash" => 0,
           // "numbers" =>8460006313
            "numbers" => $value['mobile']
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
               $user = auth()->user();
        //exit();
        if ($err) {
         // echo "cURL Error #:" . $err;
          $actionlog=array();
                        $actionlog=array(
                          'user_id'=>$user->id,
                          'action_type'=>'send sms to sender',
                          'target_id'=>$user->id,
                          'target_type'=>$value['mobile'].' sender bulk sms',
                          'new_data'=>$request->all(),
                          'note'=>"cURL Error #:" . $err
                          );
         $result_log= $this->Actionlogs($actionlog);
        } 
        //else {



         // return $response;
        //}
        //exit();
      }
               $user = auth()->user();
                 $actionlog=array();
                 $actionlog=array(
                         'user_id'=>$user->id,
                         'action_type'=>'send sms to sender',
                          'target_id'=>$user->id,
                          'target_type'=>$request->start_number.' to '.$request->end_number.'sender bulk sms',
                          'new_data'=>$request->all(),
                          'note'=>$request->start_number.' to '.$request->end_number.'sender bulk sms'
                          );
                 $result_log= $this->Actionlogs($actionlog);
      $ResponseData = array("code" => 100,'message'=>"SMS sent successfully.",'status'=>'success','data'=>$response);
          return  $this->responseFun($ResponseData);
    }
}
