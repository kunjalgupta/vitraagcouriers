<?php

namespace App\Http\Controllers;
use App\Model\Courier;
use App\Model\Sender;
use App\Model\Receiver;
use App\Model\CourierTracking;
use App\Model\User;
use App\Model\Userparent;
use App\Model\AddressDetail;
use App\Model\Dailycourier;
use App\Model\Accountstatement;
use App\mWork\Core;
use App\Http\Requests\CourierRequest;
use App\Http\Requests\CourierTrakingRequest;
use App\Http\Requests\CourierAccountRequest;
use App\Http\Requests\addStatementRequest;
use PDF;
use App\Model\BusinessDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\mWork\JsonResponse;

class AccountController extends Controller
{
    public function index()
    {
        //
    }
    public function courierAccountCron()
    {
        try {
              $role_list = array('20'=>'102','50'=>'103','10'=>'104');
              $today = date('Y-m-d', strtotime(' -1 day'));
              //$today = '2021-06-21';
              foreach ($role_list as $key => $value) {
               
               $user_list = User::select('id','name')->where('role','=',$value)->get();
                 
                 foreach ($user_list as $key1 => $value2) {
                if($value == 105){

                  $book_couriers = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.total_amount) as total_amount'))->where('adding_user_id','=',$value2->id)->whereBetween('couriers.created_at', [$today." 00:00:00", $today." 23:59:59"])->first(); 
                  }else{
                    $book_couriers = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'))->where('adding_user_id','=',$value2->id)->whereBetween('couriers.created_at', [$today." 00:00:00", $today." 23:59:59"])->first(); 
                  }

               // print_r($book_couriers);exit();
                   if($book_couriers->total_courier !=0 && $book_couriers->total_courier !=null){
                    $margin_amount = ($book_couriers->total_amount * $key) / 100;
                    $payable_amount = $book_couriers->total_amount - $margin_amount;
                   $daily_courier  = new Dailycourier();
                   $daily_courier->user_id = $value2->id;
                   $daily_courier->total_courier = $book_couriers->total_courier;
                   $daily_courier->date_of_courier = $today; 
                   $daily_courier->total_amount = $book_couriers->total_amount;
                   $daily_courier->margin_amount = $margin_amount;
                   $daily_courier->payable_amount = $payable_amount;
                   $daily_courier->save();
                  }

                 }
              }


            
        // $ResponseData = array("code" => 100,'message'=>"accountng report show sucessfully.",'status'=>'success','data'=>array());
           // return $ResponseData;
          
       } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
        return 1;
    }

    public function courierAccountReport(CourierAccountRequest $request)
    {

        try {
              $sum_of_total_courier = 0;
              $sum_of_total_amount = 0;
              $sum_of_total_margin = 0;
              $sum_of_total_payable_amount = 0;
              $remaining_balance = 0;
              $balance = 0;
            
       $dates = $this->displayDates($request->from_date, $request->to_date);
  
            $users=array();

            if (!is_null($request->user_id) && $request->user_id!="") {
                $users = User::where('id',$request->user_id)->first();
            }

            $parameters = [];
            if(isset($request->user_role) && $request->user_role == 105){
              
               $couriers = Courier::select('couriers.*','receivers.destination')->leftjoin('users as u','u.id','=','couriers.company_id')->leftjoin('receivers','receivers.id','=','couriers.receiver_id');
               if (!is_null($request->user_id) && $request->user_id!="") {

                    $couriers = $couriers->where('couriers.company_id',$request->user_id);
                }
               if (!is_null($request->from_date) && !is_null($request->to_date) && $request->to_date!='null' && $request->from_date!='null') {
                $couriers = $couriers->whereBetween('couriers.created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"]);
                }
               $couriers = $couriers->get();
               $total_amount = Courier::select(DB::raw('sum(couriers.total_amount) as total_amount'))->leftjoin('users as u','u.id','=','couriers.company_id')->leftjoin('receivers','receivers.id','=','couriers.receiver_id');
                if (!is_null($request->user_id) && $request->user_id!="") {

                    $total_amount = $total_amount->where('couriers.company_id',$request->user_id);
                }

 
               $total_amount = $total_amount->get();
               
               $resultSet = array();
               $i=0;
               foreach ($couriers as $key => $value) {
                   $resultSet[$i]['created_at'] = $value->created_at->format('d-m-Y h:i:s');
                   $resultSet[$i]['destination'] = $value->destination;
                   $resultSet[$i]['awb_number'] = $value->awb_number;
                   $resultSet[$i]['courier_type'] =  ucfirst(Core::getConstant($value->courier_type));
                   $resultSet[$i]['total_amount'] =  $value->total_amount;
                   $i++;
               }
               
                $ResponseData = array("code" => 100,'message'=>"accountng report show sucessfully.",'status'=>'success','data'=>$resultSet,'total'=>$total_amount);
            }
            else if(isset($users->role) && $users->role == 104)
            {
                $user_parent = Userparent::where('parent_id','=',$request->user_id)->pluck('user_id')->toArray();
                $frenchiser_parent = Userparent::whereIn('parent_id',$user_parent)->pluck('user_id')->toArray();
               
                $user_main = array($request->user_id);
                $array_murge1 = array_merge($user_main,$user_parent);
                if($frenchiser_parent){
                $array_murge2 = array_merge($array_murge1,$frenchiser_parent);
                }else{
                    $array_murge2 = $array_murge1;
                }
                $array_murge = array_unique($array_murge2);
                

               $margin_persantage = config('app.'.$users->role);
                  $i=0;
                   $daily_courier = array();
                  foreach ($dates as $key => $value) {
                $book_couriers = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'))->whereIn('couriers.adding_user_id',$array_murge)->whereBetween('couriers.created_at', [$value." 00:00:00", $value." 23:59:59"])->first(); 
                    
                    if($book_couriers->total_courier !=0 && $book_couriers->total_courier !=null){

                       $margin_amount = ($book_couriers->total_amount * $margin_persantage) / 100;

                       $payable_amount = $book_couriers->total_amount - $margin_amount;
                    
                       $daily_courier[$i]['total_courier'] = $book_couriers->total_courier;
                       $daily_courier[$i]['date_of_courier'] = $value; 
                       $daily_courier[$i]['total_amount'] = $book_couriers->total_amount;
                       $daily_courier[$i]['margin_amount'] = $margin_amount;
                       $daily_courier[$i]['payable_amount'] = $payable_amount;

                       $sum_of_total_courier = $book_couriers->total_courier + $sum_of_total_courier;
                       $sum_of_total_amount = $book_couriers->total_amount + $sum_of_total_amount;
                       $sum_of_total_margin = $margin_amount + $sum_of_total_margin;
                      // $sum_of_total_payable_amount = $payable_amount + $sum_of_total_payable_amount;
                       $sum_of_total_payable_amount = $sum_of_total_margin;

                        $i++;
                      
                   }
         
                   
                }
                 $resultSet[0] = array(
                                    'total_courier'=>$sum_of_total_courier,
                                    'total_amount'=>$sum_of_total_amount,
                                    'Totalmargin_amount'=>$sum_of_total_margin,
                                    'total_payable_amount'=>$sum_of_total_payable_amount,
                                );
               $ResponseData = array("code" => 100,'message'=>"accountng report show sucessfully.",'status'=>'success','data'=>$daily_courier,'total'=>$resultSet);
            }
            else if(isset($users->role) && $users->role == 103 || $users->role == 102)
            {   

               
                  $user_parent = Userparent::where('parent_id','=',$users->id)->pluck('user_id')->toArray();
                  $user_all =array();
                 // print_r($user_parent);exit();
                  if($user_parent){
                   $akk =  array($users->id);
                     $user_all = array_merge($user_parent,$akk);
                 }else{
                    $user_all =array($users->id);
                 }
                  $margin_persantage = config('app.'.$users->role);
                  $i=0;
               //  print_r($user_all);exit();
                   $daily_courier = array();
                   $totalbalance = Accountstatement::where('user_id',$users->id)->sum('amount');
                   $couriers_amount = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'))->whereIn('couriers.adding_user_id',$user_all)->first();
                    $margin_amount_all = ($couriers_amount->total_amount * $users->percentage) / 100;
                    $payable_amount_all = $couriers_amount->total_amount - $margin_amount_all;
                    $sum_of_totalbalance = $totalbalance - $payable_amount_all;
                     $balance = 0;
                  foreach ($dates as $key => $value) {
                $book_couriers = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'))->whereIn('couriers.adding_user_id',$user_all)->whereBetween('couriers.created_at', [$value." 00:00:00", $value." 23:59:59"])->first(); 
                    
                    if($book_couriers->total_courier !=0 && $book_couriers->total_courier !=null){
                         if($users->franchise_role == 'prepaid'){
                        $margin_amount = ($book_couriers->total_amount * $users->percentage) / 100;
                     }else{
                        $margin_amount = ($book_couriers->total_amount * $margin_persantage) / 100;
                     }
                      
                       $payable_amount = $book_couriers->total_amount - $margin_amount;
                    
                       $daily_courier[$i]['total_courier'] = $book_couriers->total_courier;
                       $daily_courier[$i]['date_of_courier'] = $value; 
                       $daily_courier[$i]['total_amount'] = $book_couriers->total_amount;
                       $daily_courier[$i]['margin_amount'] = $margin_amount;
                       $daily_courier[$i]['payable_amount'] = $payable_amount;

                       $sum_of_total_courier = $book_couriers->total_courier + $sum_of_total_courier;
                       $sum_of_total_amount = $book_couriers->total_amount + $sum_of_total_amount;
                       $sum_of_total_margin = $margin_amount + $sum_of_total_margin;
                       $sum_of_total_payable_amount = $payable_amount + $sum_of_total_payable_amount;
                      
                       //$sum_of_totalbalance = $totalbalance - $payable_amount;
                      

                        $i++;
                      
                   }
         
                   
                }
                
                 $resultSet[0] = array(
                                    'total_courier'=>$sum_of_total_courier,
                                    'total_amount'=>$sum_of_total_amount,
                                    'Totalmargin_amount'=>$sum_of_total_margin,
                                    'total_payable_amount'=>$sum_of_total_payable_amount,
                                    'remaining_balance'=>$sum_of_totalbalance,
                                    'balance'=>$balance,
                                );

                $ResponseData = array("code" => 100,'message'=>"accountng report show sucessfully.",'status'=>'success','data'=>$daily_courier,'total'=>$resultSet);
             //}
         }
            return $ResponseData;
          
       } catch (\Exception $e) {
           return JsonResponse::sendErrorRes();
        }
        return $resultSet;
    }
    public function downloadInvoice(CourierAccountRequest $request)
    {
        try{
              $sum_of_total_courier = 0;
              $sum_of_total_amount = 0;
              $sum_of_total_margin = 0;
              $sum_of_total_payable_amount = 0;
            
              $dates = $this->displayDates($request->from_date, $request->to_date);
  
            $users=array();

            if (!is_null($request->user_id) && $request->user_id!="") {
                $users = User::where('id',$request->user_id)->first();
            }
            
            $parameters = [];
            if(isset($request->user_role) && $request->user_role == 105){
              
               $couriers = Courier::select('couriers.*','receivers.destination')->leftjoin('users as u','u.id','=','couriers.company_id')->leftjoin('receivers','receivers.id','=','couriers.receiver_id');
               if (!is_null($request->user_id) && $request->user_id!="") {

                    $couriers = $couriers->where('couriers.company_id',$request->user_id);
                }
                if (!is_null($request->from_date) && $request->to_date!="") {

                    $couriers = $couriers->whereBetween('couriers.created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"]);
                }
               $couriers = $couriers->get();
               $total_amount = Courier::select(DB::raw('sum(couriers.total_amount) as total_amount'))->leftjoin('users as u','u.id','=','couriers.company_id')->leftjoin('receivers','receivers.id','=','couriers.receiver_id');
                if (!is_null($request->user_id) && $request->user_id!="") {

                    $total_amount = $total_amount->where('couriers.company_id',$request->user_id);
                }
                if (!is_null($request->from_date) && $request->to_date!="") {

                    $total_amount = $total_amount->whereBetween('couriers.created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"]);
                }
               $total_amount = $total_amount->first();
               $resultSet = array();
               $i=0;
               foreach ($couriers as $key => $value) {
                   $resultSet[$i]['created_at'] = $value->created_at->format('d-m-Y h:i:s');
                   $resultSet[$i]['destination'] = $value->destination;
                   $resultSet[$i]['courier_type'] =  ucfirst(Core::getConstant($value->courier_type));
                   $resultSet[$i]['total_amount'] =  $value->total_amount;
                   $i++;
               }
               
                 if($resultSet){
                $taxRate = 18;
                $gst_cal = $total_amount->total_amount*$taxRate/118;
                $gst_cal = number_format($gst_cal, 2, '.', '');
                $net_value = $total_amount->total_amount - $gst_cal;
                $gst_val =  number_format($gst_cal/2, 2, '.', '');
                $tex_value  = array("net_value"=>$net_value,"sgst"=>$gst_val,"cgst"=>$gst_val,"total_value"=>$total_amount->total_amount);
                // $total_amount['gst'] = $gst;
                $user = User::where('id',$request->user_id)->first();
                $pdfData = $this->prepareinvoicePdfData($resultSet, $tex_value, $user,$request);
                $pdf = PDF::loadView('companyinvoice', $pdfData)->setPaper('A4');
                return $pdf->download('invoice.pdf');
              
    
       
                }else{
                 echo "error_log";exit();
                }
            }
            else if(isset($users->role) && $users->role == 104)
            {    
                 $user_parent = Userparent::where('parent_id','=',$request->user_id)->pluck('user_id')->toArray();
                $frenchiser_parent = Userparent::whereIn('parent_id',$user_parent)->pluck('user_id')->toArray();
               
                $user_main = array($request->user_id);
                $array_murge1 = array_merge($user_main,$user_parent);
                if($frenchiser_parent){
                $array_murge2 = array_merge($array_murge1,$frenchiser_parent);
                }else{
                    $array_murge2 = $array_murge1;
                }
                $array_murge = array_unique($array_murge2);
                

               $margin_persantage = config('app.'.$users->role);
                  $i=0;
                   $daily_courier = array();
                  foreach ($dates as $key => $value) {
                $book_couriers = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'))->whereIn('couriers.adding_user_id',$array_murge)->whereBetween('couriers.created_at', [$value." 00:00:00", $value." 23:59:59"])->first(); 
                    
                    if($book_couriers->total_courier !=0 && $book_couriers->total_courier !=null){

                       $margin_amount = ($book_couriers->total_amount * $margin_persantage) / 100;

                       $payable_amount = $book_couriers->total_amount - $margin_amount;
                    
                       $daily_courier[$i]['total_courier'] = $book_couriers->total_courier;
                       $daily_courier[$i]['date_of_courier'] = $value; 
                       $daily_courier[$i]['total_amount'] = $book_couriers->total_amount;
                       $daily_courier[$i]['margin_amount'] = $margin_amount;
                       $daily_courier[$i]['payable_amount'] = $payable_amount;

                       $sum_of_total_courier = $book_couriers->total_courier + $sum_of_total_courier;
                       $sum_of_total_amount = $book_couriers->total_amount + $sum_of_total_amount;
                       $sum_of_total_margin = $margin_amount + $sum_of_total_margin;
                      // $sum_of_total_payable_amount = $payable_amount + $sum_of_total_payable_amount;
                       $sum_of_total_payable_amount = $sum_of_total_margin;

                        $i++;
                      
                   }
         
                   
                }
                 $resultSet[0] = array(
                                    'total_courier'=>$sum_of_total_courier,
                                    'total_amount'=>$sum_of_total_amount,
                                    'Totalmargin_amount'=>$sum_of_total_margin,
                                    'total_payable_amount'=>$sum_of_total_payable_amount,
                                );

        

            if($daily_courier){

                $user = User::where('id',$request->user_id)->first();
                $pdfData = $this->prepareinvoicePdfData($daily_courier, $resultSet, $user,$request);
                $pdf = PDF::loadView('frenchiserinvoice', $pdfData)->setPaper('A4');
                return $pdf->download('invoice.pdf');
              
    
       
                }else{
                 echo "error_log";exit();
                }
            }
            else
            {
            
             $user_parent = Userparent::where('parent_id','=',$users->id)->pluck('user_id')->toArray();
                  $user_all =array();
                 // print_r($user_parent);exit();
                  if($user_parent){
                   $akk =  array($users->id);
                     $user_all = array_merge($user_parent,$akk);
                 }else{
                    $user_all =array($users->id);
                 }
                  $margin_persantage = config('app.'.$users->role);
                  $i=0;
               
                   $daily_courier = array();

                   $totalbalance = Accountstatement::where('user_id',$users->id)->sum('amount');
                   $couriers_amount = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'))->whereIn('couriers.adding_user_id',$user_all)->first();
                    $margin_amount_all = ($couriers_amount->total_amount * $users->percentage) / 100;
                    $payable_amount_all = $couriers_amount->total_amount - $margin_amount_all;
                    $sum_of_totalbalance = $totalbalance - $payable_amount_all;
                     $balance = 0;
                     
                  foreach ($dates as $key => $value) {
                     $book_couriers = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'))->whereIn('couriers.adding_user_id',$user_all)->whereBetween('couriers.created_at', [$value." 00:00:00", $value." 23:59:59"])->first(); 
                    
                    if($book_couriers->total_courier !=0 && $book_couriers->total_courier !=null){
                        
                      if($users->franchise_role == 'prepaid'){
                        $margin_amount = ($book_couriers->total_amount * $users->percentage) / 100;
                     }else{
                        $margin_amount = ($book_couriers->total_amount * $margin_persantage) / 100;
                     }
                       
                       $payable_amount = $book_couriers->total_amount - $margin_amount;

                    
                       $daily_courier[$i]['total_courier'] = $book_couriers->total_courier;
                       $daily_courier[$i]['date_of_courier'] = $value; 
                       $daily_courier[$i]['total_amount'] = $book_couriers->total_amount;
                       $daily_courier[$i]['margin_amount'] = $margin_amount;
                       $daily_courier[$i]['payable_amount'] = $payable_amount;

                       $sum_of_total_courier = $book_couriers->total_courier + $sum_of_total_courier;
                       $sum_of_total_amount = $book_couriers->total_amount + $sum_of_total_amount;
                       $sum_of_total_margin = $margin_amount + $sum_of_total_margin;
                       $sum_of_total_payable_amount = $payable_amount + $sum_of_total_payable_amount;
                      // $sum_of_totalbalance = $totalbalance - $payable_amount;

                        $i++;
                      
                   }
         
                   
                }
               
                
                 $resultSet[0] = array(
                                    'total_courier'=>$sum_of_total_courier,
                                    'total_amount'=>$sum_of_total_amount,
                                    'Totalmargin_amount'=>$sum_of_total_margin,
                                    'total_payable_amount'=>$sum_of_total_payable_amount,
                                    'remaining_balance'=>$sum_of_totalbalance,
                                    'balance'=>$balance,
                                );

        

            if($daily_courier){

                $user = User::where('id',$request->user_id)->first();
                $pdfData = $this->prepareinvoicePdfData($daily_courier, $resultSet, $user,$request);
                $pdf = PDF::loadView('frenchiserinvoice', $pdfData)->setPaper('A4');
                return $pdf->download('invoice.pdf');
              
    
       
            }else{
             echo "error_log";exit();
            }
          }
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
        return 1;
    }
    public function prepareinvoicePdfData($resultSet, $total_amount, $user,$request)
    {
        try {

         
            $businessDetail = BusinessDetail::find($user->business_dtls_id);
            $addressdetails = AddressDetail::find($user->office_address_id);
            
            $data = [
                'start_date' => $request->from_date,
                'end_date' => $request->to_date,
                'franchise_mobile' => $addressdetails->mobile,
                'franchise_address' => $addressdetails->address,
                'franchise_pincode' => $addressdetails->pincode,
                'gst_number' => $businessDetail->gst_number,
                'resultSet' => $resultSet,
                'total_amount' => $total_amount,
              
              
                'franchise_name' => (isset($businessDetail->firm_name))?$businessDetail->firm_name:""
            ];

            return $data;
        } catch (\Exception $e) {
           return JsonResponse::sendErrorRes();
        }
    }
    public function addAccountstatement(addStatementRequest $request)
    {
        try {


            $Accountstatement = new Accountstatement();
            $Accountstatement->user_id = $request->user_id;
            $Accountstatement->amount = $request->amount;
            $Accountstatement->payment_type = $request->payment_type;
            $Accountstatement->status = 'credit';
            $Accountstatement->comment = $request->comment;
            $Accountstatement->save();

            $ResponseData = array("code" => 100,'message'=>"add Statement sucessfully.",'status'=>'success','data'=>$Accountstatement);
             return  $this->responseFun($ResponseData);

         } catch (\Exception $e) {
           return JsonResponse::sendErrorRes();
        }


    }
   
    
    public function getAccountstatementreport(CourierAccountRequest $request)
    {
        $user_list = User::where('role','=',$request->user_role)->pluck('id')->toArray();
        $i=0;
        $remaining_balance=0;
         $margin_persantage = config('app.'.$request->user_role);
        foreach ($user_list as $key => $value) {

            if($request->user_role == 104){
                //103 and 102
                $user_parent = Userparent::where('parent_id','=',$value)->pluck('user_id')->toArray();
                $frenchiser_parent = Userparent::whereIn('parent_id',$user_parent)->pluck('user_id')->toArray();
                $user_main = array($value);
                $array_murge1 = array_merge($user_main,$user_parent);
                if($frenchiser_parent){
                $array_murge2 = array_merge($array_murge1,$frenchiser_parent);
                }else{
                    $array_murge2 = $array_murge1;
                }
                $user_all = array_unique($array_murge2);

            }else if($request->user_role == 103 || $request->user_role == 102){

                 $user_parent = Userparent::where('parent_id','=',$value)->pluck('user_id')->toArray();
                  $user_all =array();
                
                  if($user_parent){
                   $akk =  array($value);
                     $user_all = array_merge($user_parent,$akk);
                 }else{
                    $user_all = array($value);
                 }

            }
            if($request->user_role == 105){
                $book_couriers = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.total_amount) as total_amount'));
            }else{
                $book_couriers = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'));
            }
             
             if($request->user_role == 105){
                 $book_couriers = $book_couriers->where('couriers.company_id',$value);
              }else{
                $book_couriers = $book_couriers->whereIn('couriers.adding_user_id',$user_all);
              }
              if(isset($request->from_date) && $request->from_date!="" && isset($request->to_date) && $request->to_date!=""){
             $book_couriers = $book_couriers->whereBetween('couriers.created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"]);
              }

              $book_couriers = $book_couriers->first(); 
                
             $paid_amount = Accountstatement::select(DB::raw('sum(account_statement.amount) as total_paid_amount'),'account_statement.user_id')->where('account_statement.user_id',$value);
                 if(isset($request->from_date) && $request->from_date!="" && isset($request->to_date) && $request->to_date!=""){
                $paid_amount = $paid_amount->whereBetween('account_statement.created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"]);
                }   

            $paid_amount = $paid_amount->groupby('account_statement.user_id')->first();
            $user = User::select('users.id','users.office_address_id','users.business_dtls_id','bd.firm_name as name')->leftjoin('address_details as office','office.id','=','users.office_address_id')->leftjoin('business_details as bd','bd.id','=','users.business_dtls_id')->where('users.id',$value)->first();

            $final_array[$i]['name'] = $user->name;
            $final_array[$i]['user_id'] = $user->id;
            $final_array[$i]['total_courier'] = $book_couriers->total_courier;
            $final_array[$i]['total_amount'] = $book_couriers->total_amount;
            $final_array[$i]['total_paid_amount'] = (isset($paid_amount->total_paid_amount))?$paid_amount->total_paid_amount:0;
            if($request->user_role == 105){

                   $final_array[$i]['Totalmargin_amount'] =$book_couriers->total_amount;
                   $final_array[$i]['total_payable_amount'] = $book_couriers->total_amount;
                   
                   $final_array[$i]['outstanding_amount'] = (isset($paid_amount->total_paid_amount))?$book_couriers->total_amount - $paid_amount->total_paid_amount :0;

             }else if($request->user_role == 104){
                       $margin_amount = ($book_couriers->total_amount * $margin_persantage) / 100;
                       $payable_amount = $book_couriers->total_amount - $margin_amount;

                   $final_array[$i]['Totalmargin_amount'] =$margin_amount;
                   $final_array[$i]['total_payable_amount'] = $margin_amount;
                  
                   $final_array[$i]['outstanding_amount'] = (isset($paid_amount->total_paid_amount))?$margin_amount - $paid_amount->total_paid_amount :0;
             }else if($request->user_role == 103){

                 $user_persentage = User::where('id','=',$value)->where('franchise_role','=','prepaid')->first();
                 if(isset($user_persentage)){

                    $margin_amount = ($book_couriers->total_amount * $user_persentage->percentage) / 100;
                    $totalbalance = Accountstatement::where('user_id',$value)->sum('amount');
                   $couriers_amount = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'))->whereIn('couriers.adding_user_id',$user_all)->first();
                    $margin_amount_all = ($couriers_amount->total_amount * $user_persentage->percentage) / 100;
                    $payable_amount_all = $couriers_amount->total_amount - $margin_amount_all;
                    $remaining_balance = $totalbalance - $payable_amount_all;

                 }else{
                    $margin_amount = ($book_couriers->total_amount * $margin_persantage) / 100;
                 }
                
                $payable_amount = $book_couriers->total_amount - $margin_amount;

                  

                   $final_array[$i]['remaining_balance'] =$remaining_balance;
                   $final_array[$i]['Totalmargin_amount'] =$margin_amount;
                   $final_array[$i]['total_payable_amount'] = $payable_amount;
                  
                   $final_array[$i]['outstanding_amount'] = (isset($paid_amount->total_paid_amount))?$payable_amount - $paid_amount->total_paid_amount :0;


             }else if($request->user_role == 102){
                $margin_amount = ($book_couriers->total_amount * $margin_persantage) / 100;
                $payable_amount = $book_couriers->total_amount - $margin_amount;

                  $final_array[$i]['Totalmargin_amount'] =$margin_amount;
                   $final_array[$i]['total_payable_amount'] = $payable_amount;
                  
                   $final_array[$i]['outstanding_amount'] = (isset($paid_amount->total_paid_amount))?$payable_amount - $paid_amount->total_paid_amount :0;


             }
           
           
            

            $i++;
                    
            
        }

        $ResponseData = array("code" => 100,'message'=>"Account statement report.",'status'=>'success','data'=>$final_array);
             return  $this->responseFun($ResponseData); 
    }

}
