<?php

namespace App\Http\Controllers;
use App\Model\Courier;
use App\Model\Sender;
use App\Model\Receiver;
use App\Model\CourierTracking;
use App\Model\User;
use App\Model\AddressDetail;
use App\Model\Userparent;
use App\Model\Statepricelist;
use App\Model\Specialpincodeprice;
use App\Model\Pincode;
use App\mWork\Core;
use App\Http\Requests\CourierRequest;
use App\Http\Requests\addCourierRequest;
use App\Http\Requests\CourierTrakingRequest;
use App\Http\Requests\PodAddRequest;
use PDF;
use App\Model\BusinessDetail;
use App\mWork\CommonService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\mWork\JsonResponse;

class CourierController extends Controller
{   
    protected $commonService;
    public function __construct(CommonService $commonService)
    {
        if (is_null(auth()->user()) || !auth()->user()->isCompany()) {
            return response()->json(['message' => 'Action Not Authorized', 'status' => 0, 'data' => []], 401);
        }

        $this->commonService = $commonService;
    }
    public function index()
    {
        //
    }

    public function store(CourierRequest $request)
    {
        /*if (!auth()->user()->isFranchise() && !auth()->user()->isMiniDealer()) {
            return response()->json(['message' => 'Action Not Authorized'], 401);
        }*/
        //$user = auth()->user();
        //if(isset($request['courier']['adding_user_id']) && $request['courier']['adding_user_id']!=null){
        $user = User::where('id',$request['courier']['adding_user_id'])->first();
   // }

        DB::beginTransaction();
        try {

            $sender = Sender::create($request['sender']);
            $receiver = Receiver::create($request['receiver']);
            $courier = $request['courier'];
             $courier_check = Courier::where('awb_number','=',$request['courier']['awb_number'])->first();
            if($courier_check){

                  return response()->json(['message' => 'Awb Number is already exists.'], 401);
            }
            if (!is_null($sender) && !is_null($receiver)) {
                $courier['sender_id'] = $sender->id;
                $courier['receiver_id'] = $receiver->id;
               // if(isset($request['courier']['adding_user_id']) && $request['courier']['adding_user_id']!=null){
                $courier['adding_user_id'] = $user->id;
            //}
        }
            $courier = Courier::create($courier);
            DB::commit();

           

             if(isset($request['courier']['awb_number']) && $request['courier']['awb_number']!=""){

            
              // $courier['awb_number'] = Core::getLabel('AWB_NUMBER_PREFIX') . $request['courier']['awb_number'];
               $courier['awb_number'] =  $request['courier']['awb_number'];
             }else{
               $courier['awb_number'] = Core::getLabel('AWB_NUMBER_PREFIX') . $courier->id;
            }
            $Pdf_url = "";
            $courier['pdf_url'] = $Pdf_url;
            $courier->update();
            $booking_location ="";
            if($user->office_address_id!="" && $user->office_address_id!=0){
             $addressdetails = AddressDetail::where('id',$user->office_address_id)->first();
             $booking_location= $addressdetails->area;
            }else{
              $booking_location = config('app.MAIN_BRANCH_LOCATION');
            }

            
            $Couriertracking= new CourierTracking();
            $Couriertracking->user_id = $user->id;
            $Couriertracking->courier_id = $courier->id;
            $Couriertracking->courier_location = $booking_location;
            $Couriertracking->status = "booked";
            $Couriertracking->save();

            $user = auth()->user();
                 $actionlog=array();
                 $actionlog=array(
                          'user_id'=>$user->id,
                          'action_type'=>'Create Courier '.$courier->awb_number,
                          'target_id'=>$courier->id,
                          'target_type'=>Courier::class,
                          'new_data'=>$request->all(),
                          'note'=>'Create Courier.'
                          );
                // $result_log= $this->Actionlogs($actionlog);

            $this->sendCourierSMS($sender->mobile, $receiver->mobile, $courier->awb_number, $sender->origin, $receiver->destination);
            return response()->json([
                "code" => 100,
                'message' => 'courier booked successfully',
                'status' => 1,
                'data' => [
                    'pdf_url' => config('app.url')."/api/invoice/".$courier->id,
                    'courier_id' => $courier->awb_number
                ]
            ], 201);
       } catch (\Exception $e) {
            
           DB::rollBack();
           return JsonResponse::sendErrorRes();
       }
    }

   
   public function addCourier(addCourierRequest $request)
   {
        
        $user = User::where('id',$request['courier']['adding_user_id'])->first();
   

        DB::beginTransaction();
      ///  try {

            $sender = Sender::create($request['sender']);
            $receiver = Receiver::create($request['receiver']);
            $courier = $request['courier'];

             $courier_check = Courier::where('awb_number','=',$request['courier']['awb_number'])->first();

            if($courier_check){

                  return response()->json(['message' => 'Awb Number is already exists.'], 401);
            }
            if (!is_null($sender) && !is_null($receiver)) {
                $courier['sender_id'] = $sender->id;
                $courier['receiver_id'] = $receiver->id;
               // if(isset($request['courier']['adding_user_id']) && $request['courier']['adding_user_id']!=null){
                $courier['adding_user_id'] = $user->id;
            //}
        }
              // print_r($courier['pincode']);exit();
           $data = array("pincode"=>$courier['pincode'],"state"=>7,"courier_type"=>109);
            $getrate_value = $this->getRateforshopi($data);
//print_r($getrate_value);exit();
            if(!empty($getrate_value)){
            $amount = $courier['weight'] * $getrate_value[0]->rate;
            $total_amount = $courier['weight'] * $getrate_value[0]->rate;
           }else{
            $amount = $courier['weight'] * 30;
            $total_amount = $courier['weight'] * 30;
           }
            $courier_array = array(
                                  'amount'=>$amount,
                                  'total_amount'=>$total_amount,
                                  'company_id'=> $courier['adding_user_id'],
                                  'courier_type'=>109);
            $main_courier = array_merge($courier,$courier_array);
//print_r($main_courier);exit();
            $courier = Courier::create($main_courier);
            DB::commit();

           

             if(isset($request['courier']['awb_number']) && $request['courier']['awb_number']!=""){

            
              // $courier['awb_number'] = Core::getLabel('AWB_NUMBER_PREFIX') . $request['courier']['awb_number'];
               $courier['awb_number'] =  $request['courier']['awb_number'];
             }else{
               $courier['awb_number'] = Core::getLabel('AWB_NUMBER_PREFIX') . $courier->id;
            }
            $Pdf_url = "";
            $courier['pdf_url'] = $Pdf_url;
            $courier->update();
            $booking_location ="";
            if($user->office_address_id!="" && $user->office_address_id!=0){
             $addressdetails = AddressDetail::where('id',$user->office_address_id)->first();
             $booking_location= $addressdetails->area;
            }else{
              $booking_location = config('app.MAIN_BRANCH_LOCATION');
            }

            
            $Couriertracking= new CourierTracking();
            $Couriertracking->user_id = $user->id;
            $Couriertracking->courier_id = $courier->id;
            $Couriertracking->courier_location = $booking_location;
            $Couriertracking->status = "booked";
            $Couriertracking->save();

            $user = auth()->user();
                 $actionlog=array();
                 $actionlog=array(
                          'user_id'=>$user->id,
                          'action_type'=>'Create Courier '.$courier->awb_number,
                          'target_id'=>$courier->id,
                          'target_type'=>Courier::class,
                          'new_data'=>$request->all(),
                          'note'=>'Create Courier.'
                          );
                // $result_log= $this->Actionlogs($actionlog);

            $this->sendCourierSMS($sender->mobile, $receiver->mobile, $courier->awb_number, $sender->origin, $receiver->destination);
            return response()->json([
                "code" => 100,
                'message' => 'courier booked successfully',
                'status' => 1,
                'data' => [
                    'pdf_url' => config('app.url')."/api/invoice/".$courier->id,
                    'courier_id' => $courier->awb_number
                ]
            ], 201);
     ///  } catch (\Exception $e) {
            
      //     DB::rollBack();
      //     return JsonResponse::sendErrorRes();
     //  }
   
   }
   public function getRateforshopi($data)
   {
       try {
            $parameters = [$data['pincode'], $data['state']];
            $query = Core::getQuery('GET_RATE_FROM_PINCODE');

            if ($data['courier_type'] == 109) {
                $query = str_replace('rate_type', 'parcel_rate', $query);
            } else if ($data['courier_type'] == 110) {
                $query = str_replace('rate_type', 'document_rate', $query);
            } else if ($data['courier_type'] == 111) {
                $query = str_replace('rate_type', 'document_500g_rate', $query);
            } else {
                $query = str_replace('rate_type', 'cargo_rate', $query);
            }
            $resultSet = DB::select($query, $parameters);
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
        return $resultSet;
   }
   public function addCourierTraking(CourierTrakingRequest $request)
   {
       $CourierTracking = CourierTracking::addTrakingPoint($request);
       $user = auth()->user();
       $courier = Courier::select('couriers.*','senders.mobile')->leftjoin('senders','senders.id','=','couriers.sender_id')->where('couriers.id','=',$request->courier_id)->first();
        if(isset($request['status']) &&  $request['status'] =="delivered"){
            
            $msgToSender = Core::sendFST2SMS($courier->mobile, config('app.receiveredSMS'), $courier->awb_number, "");
             $actionlog=array();
            //'bookby','bookto','delivered' 
            $actionlog=array(
                     'awb_number'=>$courier->awb_number,
                     'mobile'=>$courier->mobile,
                     'msg'=>config('app.receiveredSMS'),
                     'log'=>$msgToSender,
                     'note'=>'delivered'
                     );
            $result_log= $this->smslogs($actionlog);
            
        }
                        
                        $actionlog=array();
                        $actionlog=array(
                          'user_id'=>$user->id,
                          'action_type'=>'add Courier Traking '.$courier->awb_number,
                          'target_id'=>$courier->id,
                          'target_type'=>Courier::class,
                          'new_data'=>$request->all(),
                          'note'=>'Add Courier Traking with '.$request['status']
                          );
                // $result_log= $this->Actionlogs($actionlog);
        $ResponseData = array("code" => 100,'message'=>"Add CourierTraking sucessfully.",'status'=>'success','data'=>$CourierTracking);
          return  $this->responseFun($ResponseData);
           
   }
   public function AddPod(PodAddRequest $request)
   {
   
     try {  
         if (isset($request['pod']['data']) && $request['pod']['data'] != null) {
                    $doc = $request['pod']['data'];
                    $extension = $request['pod']['extension'];
                    $file = $this->commonService->converBase64ToFileObject($doc);
                    $url = $this->uploadAttachment($file, $extension, 'pod_photo','/courier/pod/', $request['courier_id'], $request->getSchemeAndHttpHost());
        $Couriertracking= new CourierTracking();
        $Couriertracking->user_id = (isset($request['user_id']) && $request['user_id']!="")?$request['user_id']:0;
        $Couriertracking->courier_id = (isset($request['courier_id']) && $request['courier_id']!="")?$request['courier_id']:0;
        $Couriertracking->courier_location = (isset($request['courier_location']) && $request['courier_location']!="")?$request['courier_location']:"";
        $Couriertracking->status = (isset($request['status']) &&  $request['status'] !="")?$request['status']:'pending';
        $Couriertracking->other = (isset($url) &&  $url !="")?$url:null;
        $Couriertracking->save();
        $courier_details = CourierTracking::select('courier_tracking.*','users.name as user_name','users.office_address_id','address_details.area','address_details.address')->leftjoin('users','users.id','=','courier_tracking.user_id')->leftjoin('address_details','address_details.id','=','users.office_address_id')->where('courier_tracking.courier_id',$request['courier_id'])->get();
     
      $ResponseData = array("code" => 100,'message'=>"Add Pod CourierTraking sucessfully.",'status'=>'success','data'=>$courier_details);
        //return $courier_details;
                    //$user['pod_photo'] = $url;
                }
       } catch (\Exception $e) {
          return JsonResponse::sendErrorRes();
        }
                
          return  $this->responseFun($ResponseData);
   }
   public function uploadAttachment($file, $extension, $key,$path, $id, $host)
    {
        $filename = $key . '-' . time() . '.' . $extension;
        $path = $file->storeAs('public'.$path . $id, $filename);
        $url = $host . Core::getLabel('PROJECT_FOLDER_PREFIX') . '/public' . storage::url($path);
        return $url;
    }
   public function getTrakingList(CourierRequest $request)
   {
     try {  
       $CourierTracking = CourierTracking::select('courier_tracking.id','courier_tracking.other','courier_tracking.user_id','courier_tracking.courier_id','courier_tracking.courier_location','courier_tracking.status','users.name as user_name','users.office_address_id','address_details.area','address_details.address',DB::raw('DATE_FORMAT(courier_tracking.created_at, "%d-%m-%Y %h:%i:%s %p") as created_date'))->leftjoin('users','users.id','=','courier_tracking.user_id')->leftjoin('address_details','address_details.id','=','users.office_address_id')->where('courier_tracking.courier_id',$request['courier_id'])->get();
        $ResponseData = array("code" => 100,'message'=>"CourierTraking list show sucessfully.",'status'=>'success','data'=>$CourierTracking);

        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
        
        return  $this->responseFun($ResponseData);
           
   }

    public function getRate(CourierRequest $request)
    {
        try {
            $parameters = [$request->pincode, $request->state];
            $query = Core::getQuery('GET_RATE_FROM_PINCODE');

            if ($request->courier_type == '109') {
                $query = str_replace('rate_type', 'parcel_rate', $query);
            } else if ($request->courier_type == 110) {
                $query = str_replace('rate_type', 'document_rate', $query);
            } else if ($request->courier_type == 111) {
                $query = str_replace('rate_type', 'document_500g_rate', $query);
            } else {
                $query = str_replace('rate_type', 'cargo_rate', $query);
            }
            $resultSet = DB::select($query, $parameters);
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
        return $resultSet;
    }
    public function getRates(CourierRequest $request)
    {
        try {
            //$parameters = [$request->pincode, $request->state];
            //$query = Core::getQuery('GET_RATE_FROM_PINCODE');
            ///select rate_type as rate from pincodes where pincode=? and state=?

            $state_find = Pincode::where("pincode","=",$request->pincode)->where('active_flag','=',1)->first();
            $sprice =0;
            $transit_days =0;

            if($state_find){

            $checkspecial_prize = Specialpincodeprice::where("pincode","=",$request->pincode)->first();

            $statetostate_prize = Statepricelist::where("from_id","=",$request->state)->where("to_id","=",$state_find->state)->first();

                if($checkspecial_prize){
                      $transit_days = $checkspecial_prize->transit_days;
                     if ($request->courier_type == '109') {
                            //parcel_rate
                            $sprice = $statetostate_prize->parcel_rate + $checkspecial_prize->parcel_rate;
                          //  $final_price = ;

                        } else if ($request->courier_type == 110) {
                            //document_rate
                            $sprice = $statetostate_prize->document_rate + $checkspecial_prize->document_rate;

                        } else if ($request->courier_type == 111) {
                           //document_500g_rate
                             $sprice = $statetostate_prize->document_500g_rate + $checkspecial_prize->document_500g_rate;

                        } else {
                            //cargo_rate
                             $sprice = $statetostate_prize->cargo_rate + $checkspecial_prize->cargo_rate;
                        }

                   

                }else{
                      
                      $transit_days = $statetostate_prize->transit_days;
                      if ($request->courier_type == '109') {
                            //parcel_rate
                            $sprice = $statetostate_prize->parcel_rate;

                        } else if ($request->courier_type == 110) {
                            //document_rate
                            $sprice = $statetostate_prize->document_rate;

                        } else if ($request->courier_type == 111) {
                           //document_500g_rate
                             $sprice = $statetostate_prize->document_500g_rate;

                        } else {
                            //cargo_rate
                             $sprice = $statetostate_prize->cargo_rate;
                        }

                }
                  $resultSet[] = array("rate"=>$sprice,"transit_days"=>$transit_days,"message"=>"Service is available.");
            }else{
                 $resultSet = array("code" => 400,'message'=>"Service is not available.",'status'=>'error','data'=>array('picode'=>"no service"));

            }

           
            
           // $resultSet = DB::select($query, $parameters);
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
        return $resultSet;
    }

    public function getArea(CourierRequest $request)
    {
        try {
            //$parameters = [$request->pincode, $request->state];
            //'select area from pincodes where pincode=? and state=?',
            $query = Pincode::where('pincode',"=",$request->pincode)->where("active_flag","=",1)->first();
            if($query){
                $resultSet[] = array("area"=>$query->area);
            }else{
                $resultSet = array();
            }
            
            //$resultSet = DB::select($query, $parameters);
        } catch (\Exception $e) {
            dd($e);
            return JsonResponse::sendErrorRes();
        }
        return $resultSet;
    }

    public function generateInvoicePDF($data, $host, $awb)
    {
        try {
            $pdf = PDF::loadView('newinvoice', $data);
            $fileName = 'public/Invoice/Invoice-' . $awb . '.pdf';
            Storage::put($fileName, $pdf->output());
            $url = $host . Core::getLabel('PROJECT_FOLDER_PREFIX') . '/public' . Storage::url($fileName);
            return $url;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
   

    public function preparePdfData($courier, $sender, $receiver, $user)
    {
        try {

            $item = is_null($courier->item) ? '-' : $courier->item;
            $wq = ($courier->courier_type == 109 || $courier->courier_type == 112) ? $courier->weight . ' Kg' : $courier->quantity;
            $wq_header = ($courier->courier_type == 109 || $courier->courier_type == 112) ? 'WEIGHT' : 'QUANTITY';
 
            $businessDetail = BusinessDetail::find($user->business_dtls_id);
            
            $data = [
                'courier_date' => $courier->created_at->format('d-m-Y'),
                'origin' => $sender->origin,
                'user_code_number' => $user->code_number,
                'destination' => $receiver->destination,
                'awb' => $courier->awb_number,
                'sender_name' => $sender->name,
                'receiver_name' => $receiver->name,
                'sender_mobile' => $sender->mobile,
                'receiver_mobile' => $receiver->mobile,
                'sender_address' => $sender->address,
                'sender_pincode' => $sender->pincode,
                'receiver_pincode' => $receiver->pincode,
                'receiver_address' => $receiver->address,
                'courier_type' => ucfirst(Core::getConstant($courier->courier_type)),
                'item' => $item,
                'wq' => $wq,
                'wq_header' => $wq_header,
                'amount' => $courier->amount,
                'discount' => is_null($courier->discount) ? '0' : $courier->discount,
                'total_amount' => $courier->total_amount,
                'franchise_name' => (isset($businessDetail->firm_name))?$businessDetail->firm_name:""
            ];

            return $data;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }

    public function sendCourierSMS($senderMobile, $receiverMobile, $awb_number, $origin, $destination)
    {
        $msgToSender = Core::sendFST2SMS($senderMobile, config('app.senderSMS'), $awb_number, $destination);
         $actionlog=array();
        //'bookby','bookto','delivered' 
        $actionlog=array(
                 'awb_number'=>$awb_number,
                 'mobile'=>$senderMobile,
                 'msg'=>config('app.senderSMS'),
                 'log'=>$msgToSender,
                 'note'=>'bookby'
                 );
        $result_log= $this->smslogs($actionlog);
        $msgToReceiver = Core::sendFST2SMS($receiverMobile, config('app.receiverSMS'), $awb_number, $origin);
         $actionlog1=array();
        $actionlog1=array(
                'awb_number'=>$awb_number,
                'mobile'=>$receiverMobile,
                'msg'=>config('app.receiverSMS'),
                'log'=>$msgToReceiver,
                 'note'=>'bookto'
                 );
       $result_log= $this->smslogs($actionlog1);
    }
     public function getCourierShowPdf($courier_id)
    {     
          $courier = Courier::where('id',$courier_id)->first();
          if($courier){
            $user = User::where('id',$courier->adding_user_id)->first();
            $sender = Sender::where('id',$courier->sender_id)->first();
           
            $receiver = Receiver::where('id',$courier->receiver_id)->first();
            
          $pdfData = $this->preparePdfData($courier, $sender, $receiver, $user);
          
          $contents = view('newinvoice', $pdfData)->render();

          $ResponseData = array("code" => 100,'message'=>"CourierTraking list show sucessfully.",'status'=>'success','data'=>$contents);
        return  $this->responseFun($ResponseData);
       
        }else{
         echo "error_log";exit();
        }
    } 
    public function getCourierDownloadPdf($courier_id)
    {     
          $courier = Courier::where('id',$courier_id)->first();
 
          if($courier){
            $user = User::where('id',$courier->adding_user_id)->first();
            $sender = Sender::where('id',$courier->sender_id)->first();
           
            $receiver = Receiver::where('id',$courier->receiver_id)->first();
            
          $pdfData = $this->preparePdfData($courier, $sender, $receiver, $user);
          
          $pdf = PDF::loadView('newinvoice', $pdfData);
        
          return $pdf->download("pdf_filename.pdf");
          
        }else{
         echo "error_log";exit();
        }
    }

    public function courierReport(CourierRequest $request)
    {
        //try {
            $parameters = [];
        
             
             $couriers = Courier::select('couriers.adding_user_id','couriers.amount','couriers.id as c_id','bd.firm_name','couriers.awb_number','couriers.courier_type','couriers.item','couriers.weight','couriers.quantity','couriers.total_amount','couriers.pdf_url','u.code_number','u.role','u.name','r.destination',DB::raw('DATE_FORMAT(couriers.created_at, "%Y-%m-%d %H:%i:%s") as created_date'),'couriers.created_at','t.status','r.name as party_name')->leftjoin('users as u','u.id','=','couriers.adding_user_id')->leftjoin('receivers as r','couriers.receiver_id','=','r.id')->leftjoin('business_details as bd','bd.id','=','u.business_dtls_id')->leftjoin(DB::raw("(SELECT * FROM courier_tracking WHERE id IN (SELECT MAX(id) FROM courier_tracking GROUP BY courier_id) ) as t"),'t.courier_id','=','couriers.id');
            $user = auth()->user();

            if ($user->role == 104 || $user->role == 105) {
                if (!is_null($request->user_id) && $request->user_id!="") {

                    $abc = explode(',', $request->user_id);
                    $user_role_check = User::find($abc[0]);
                    if($user_role_check->role == 103){

                        $user_parent = Userparent::where('user_parent.parent_id',$abc)->leftjoin('users','users.id','=','user_parent.user_id')->pluck('user_id')->toArray();
                        $userids = array_merge($abc,$user_parent);
                         $couriers = $couriers->whereIn('couriers.adding_user_id',$userids);

                    }else{
                       $couriers = $couriers->whereIn('couriers.adding_user_id',$abc);
                    }
                   

                    
                }else if($user->role == 104){

                      $user_parent = Userparent::where('user_parent.parent_id','=',$user->id)->leftjoin('users','users.id','=','user_parent.user_id')->pluck('user_id')->toArray();
                    $couriers = $couriers->whereIn('couriers.adding_user_id',$user_parent);
                }
            } else {
                
                 $couriers = $couriers->where('couriers.adding_user_id',$user->id);
             
            }


            if (!is_null($request->from_date) && !is_null($request->to_date) && $request->to_date!='null' && $request->from_date!='null') {
                $couriers = $couriers->whereBetween('couriers.created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"]);
            }
            if (!is_null($request->pincode)) {
                $couriers = $couriers->where('couriers.pincode','=',$request->pincode);
            }
            if (!is_null($request->awb_number)) {
                $couriers = $couriers->where('couriers.awb_number','=',$request->awb_number);
            }
            $resultSet = $couriers->get()->toarray();

         
          
       //} catch (\Exception $e) {
       //     return JsonResponse::sendErrorRes();
       // }
        return $resultSet;
    }

    public function trackCourier($awb_number)
    {
        try {
            $CourierTracking = CourierTracking::select('courier_tracking.id','courier_tracking.user_id','courier_tracking.courier_id','courier_tracking.courier_location','courier_tracking.status','courier_tracking.other','users.name as user_name','users.office_address_id','address_details.area','address_details.mobile','address_details.address',DB::raw('DATE_FORMAT(courier_tracking.created_at, "%d-%m-%Y %h:%i:%s %p") as created_date'))->leftjoin('couriers','couriers.id','=','courier_tracking.courier_id')->leftjoin('users','users.id','=','courier_tracking.user_id')->leftjoin('address_details','address_details.id','=','users.office_address_id')->where('couriers.awb_number',$awb_number)->get();
            
             $ResponseData = array("code" => 100,'message'=>"track Courier show sucessfully.",'status'=>'success','data'=>$CourierTracking,'mobile_number'=>"+91 99048 40607");
            return $ResponseData;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function statuslist()
    {
        try {
          $status =  array(
                                  array("key"=>"booked","value"=>"Booked"),
                                  array("key"=>"pending","value"=>"Pending"),
                                  array("key"=>"received","value"=>"Received"),
                                  array("key"=>"forward_to","value"=>"Forward To"),
                                  array("key"=>"out_for_delivery","value"=>"Out for Delivery"),
                                  array("key"=>"delivered","value"=>"Delivered")
                                  
                              );
        $other =  array(array("key"=>"pod","value"=>"POD"));
       $ResponseData = array("code" => 100,'message'=>"Status list show sucessfully.",'status'=>'success','data'=>$status,"other"=>$other);
          return  $this->responseFun($ResponseData);
           } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function downloadInvoicePdf($courier_id)
    { 
        try{
        $courier = Courier::where('id',$courier_id)->first();
          if($courier){
            $user = User::where('id',$courier->adding_user_id)->first();
            $sender = Sender::where('id',$courier->sender_id)->first();
           
            $receiver = Receiver::where('id',$courier->receiver_id)->first();
            
          $pdfData = $this->preparePdfData($courier, $sender, $receiver, $user);
          $pdf = PDF::loadView('newinvoice', $pdfData);
         return $pdf->download($courier->awb_number.'_invoice.pdf');
    
       
            }else{
             echo "error_log";exit();
            }
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
   
     public function deleteCourier($CourierId)
    {    
         
        // Check if the assignment exists
        if (is_null($Courier = Courier::find($CourierId))) {

           $ResponseData = array("code" => 400,'message'=>"Invalid CourierId",'status'=>'error');
         return  $this->responseFun($ResponseData);  
        }     
                 $user = auth()->user();
                 $actionlog=array();
                 $actionlog=array(
                          'user_id'=>$user->id,
                          'action_type'=>'Delete Courier '.$Courier->awb_number,
                          'target_id'=>$Courier->id,
                          'target_type'=>Courier::class,
                          'new_data'=>$Courier,
                          'note'=>'Deleted Courier'
                          );
                 $result_log= $this->Actionlogs($actionlog);
              
            $Courier->delete();

             $ResponseData = array("code" => 100,'message'=>"Courier delete sucessfully.",'status'=>'success','data'=>$Courier);
             return  $this->responseFun($ResponseData);


    }
     public function getCourier($courier_id)
    {     
          $courier = Courier::where('id',$courier_id)->first();
          if($courier){
            $user = User::where('id',$courier->adding_user_id)->first();
            $sender = Sender::where('id',$courier->sender_id)->first();
            $receiver = Receiver::where('id',$courier->receiver_id)->first();

            $courier_details= array('courier'=>$courier,
                                    'sender'=>$sender,
                                    'receiver'=>$receiver
                                    );


          $ResponseData = array("code" => 100,'message'=>"CourierTraking list show sucessfully.",'status'=>'success','data'=>$courier_details);
        return  $this->responseFun($ResponseData);
       
        }else{
         echo "error_log";exit();
        }
    }  public function getCourierwithid(CourierRequest $request)
    {     
        try{
        $courier_id  = $request->courier_id;
          $courier = Courier::where('id',$courier_id)->first();
          if($courier){
            $user = User::where('id',$courier->adding_user_id)->first();
            $sender = Sender::where('id',$courier->sender_id)->first();
            $receiver = Receiver::where('id',$courier->receiver_id)->first();

            $courier_details= array('courier'=>$courier,
                                    'sender'=>$sender,
                                    'receiver'=>$receiver
                                    );
            
          
          


          $ResponseData = array("code" => 100,'message'=>"CourierTraking list show sucessfully.",'status'=>'success','data'=>$courier_details);
        return  $this->responseFun($ResponseData);
       
        }else{
         echo "error_log";exit();
        }
         } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    } 
    public function updateCourier(CourierRequest $request)
    {
        try{
             $user = User::where('id',$request['courier']['adding_user_id'])->first();
         
            $sender = Sender::where('id', $request['sender']['id'])->update($request['sender']);
            $receiver = Receiver::where('id', $request['receiver']['id'])->update($request['receiver']);
            $courier = $request['courier'];
            $courier = Courier::where('id', $courier['id'])->update($courier);
            DB::commit();
           $courier_u = Courier::find($request['courier']['id']);
          
            $user = auth()->user();
                 $actionlog=array();
                 $actionlog=array(
                          'user_id'=>$user->id,
                          'action_type'=>'update Courier '.$courier_u->awb_number,
                          'target_id'=>$courier_u->id,
                          'target_type'=>Courier::class,
                          'new_data'=>$request->all(),
                          'note'=>'Create Courier.'
                          );
                // $result_log= $this->Actionlogs($actionlog);

          //  $this->sendCourierSMS($sender->mobile, $receiver->mobile, $courier->awb_number, $sender->origin, $receiver->destination);
            return response()->json([
                "code" => 100,
                'message' => 'courier update successfully',
                'status' => 1,
                'data' => [
                    'pdf_url' => config('app.url')."/api/invoice/".$courier_u->id,
                    'courier_id' => $courier_u->awb_number
                ]
            ], 201);

             } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function myCouriers()
    {
       

             $user_auth = auth()->user();
       // select courier_tracking.* from courier_tracking  inner join (select courier_id, max(id) as maxid from courier_tracking where forward_id is NOT null  group by courier_id) as b on courier_tracking.id = b.maxid and courier_tracking.forward_id = ".$user_auth->id." 
             $couriers_ids = CourierTracking::join(DB::raw("(select courier_id, max(id) as maxid from courier_tracking where forward_id is NOT null  group by courier_id) as b"),'courier_tracking.id','=','b.maxid')->where('courier_tracking.forward_id','=',$user_auth->id)->pluck('courier_tracking.courier_id')->toArray();

      $couriers = Courier::select('couriers.adding_user_id','couriers.amount','couriers.id as c_id','bd.firm_name','couriers.awb_number','couriers.courier_type','couriers.item','couriers.weight','couriers.quantity','couriers.total_amount','couriers.pdf_url','u.code_number','u.role','u.name','r.destination',DB::raw('DATE_FORMAT(couriers.created_at, "%Y-%m-%d %H:%i:%s") as created_date'),'couriers.created_at','t.status')->leftjoin('users as u','u.id','=','couriers.adding_user_id')->leftjoin('receivers as r','couriers.receiver_id','=','r.id')->leftjoin('business_details as bd','bd.id','=','u.business_dtls_id')
      ->join(DB::raw("(select courier_tracking.* from courier_tracking  inner join (select courier_id, max(id) as maxid from courier_tracking   group by courier_id) as b on courier_tracking.id = b.maxid) as t"),'couriers.id','=','t.courier_id')->whereIn('t.courier_id',$couriers_ids)->get()->toarray();
    
 
        // $couriers = Courier::select('couriers.adding_user_id','couriers.amount','couriers.id as c_id','bd.firm_name','couriers.awb_number','couriers.courier_type','couriers.item','couriers.weight','couriers.quantity','couriers.total_amount','couriers.pdf_url','u.code_number','u.role','u.name','r.destination',DB::raw('DATE_FORMAT(couriers.created_at, "%Y-%m-%d %H:%i:%s") as created_date'),'couriers.created_at','t.status')->leftjoin('users as u','u.id','=','couriers.adding_user_id')->leftjoin('receivers as r','couriers.receiver_id','=','r.id')->leftjoin('business_details as bd','bd.id','=','u.business_dtls_id')->join('courier_tracking as t',"t.courier_id","=","couriers.id")->whereIn('t.courier_id',$couriers_ids)->get()->toarray();

         $ResponseData = array("code" => 100,'message'=>"My Courier list show sucessfully.",'status'=>'success','data'=>$couriers);
        return  $this->responseFun($ResponseData);
    }

}
