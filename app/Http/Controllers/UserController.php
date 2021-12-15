<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserpermissionRequest;
use App\Model\User;
use App\Model\BusinessDetail;
use App\Model\BankDetail;
use App\Model\AddressDetail;
use App\Model\Userparent;
use App\Model\Companyparent;
use App\Model\Userpermission;
use App\Model\Courier;
use App\Model\Accountstatement;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Mail\UserCreated;
use App\mWork\CommonService;
use App\mWork\Core;
use App\mWork\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
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
        try { } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function usersmainlist($role)
    {   
        $user_auth = auth()->user();
        //return $user_auth;
        $users = User::select('users.*','user_parent.user_id','user_parent.parent_id','pu.name as parent_name','address_details.area','address_details.mobile','business_details.firm_name')
        ->leftjoin('user_parent','user_parent.user_id','=','users.id')
        ->leftjoin('address_details','address_details.id','=','users.office_address_id')
        ->leftjoin('business_details','business_details.id','=','users.business_dtls_id')
        ->leftjoin('users as pu','pu.id','=','user_parent.parent_id')
        ->where('users.role','=',$role);
        if($user_auth->role == 104){

            $user_parent = Userparent::where('user_parent.parent_id','=',$user_auth->id)->leftjoin('users','users.id','=','user_parent.user_id')->where('users.role',$role)->pluck('user_id')->toArray();
            $users = $users->whereIn('users.id',$user_parent);

        }
        $users = $users->get();

        $rows = array();
        $totalbalance = 0;
        $balance = 0;
        $margin_amount = 0;
        
        foreach ($users as $user) {
            $balance =0;
            if($user->role == 103){
                ////////prepaid
                 if($user->franchise_role == 'prepaid'){
                    $user_parent = Userparent::where('parent_id','=',$user->id)->pluck('user_id')->toArray();
                     $user_all =array();
                      if($user_parent){
                        $akk =  array($user->id);
                         $user_all = array_merge($user_parent,$akk);
                       }else{
                        $user_all =array($user->id);
                       }
                      $book_couriers = Courier::select(DB::raw('count(couriers.id) as total_courier'),DB::raw('sum(couriers.amount) as total_amount'))->whereIn('couriers.adding_user_id',$user_all)->first();
                        $margin_amount = ($book_couriers->total_amount * $user->percentage) / 100;
                    
                    $totalbalance = Accountstatement::where('user_id',$user->id)->sum('amount');
                    $payable_amount = $book_couriers->total_amount - $margin_amount;
                    $balance = $totalbalance - $payable_amount;

                 }
            }
            

            $rows[] = array(
               
                'id'              => $user->id,
                'code_number'              => $user->code_number,
                'firm_name'              => $user->firm_name,
                'status'              => $user->status,
                'percentage'              => $user->percentage,
                'franchise_role'              => $user->franchise_role,
                'parent_name'              => $user->parent_name,
                'state_name'              => $user->state_name,
                'area'              => $user->area,
                'mobile'              => $user->mobile,
                'totalbalance'              => $totalbalance,
                'balance'              => $balance,
                'margin_amount'              => $margin_amount
                
            );
            
        }

        return $rows;
    }

    public function store(UserRequest $request)
    {
        if ($this->isEmailExist($request['user_detail']['email'])) {
            return response()->json(['message' => 'Email Already Exist', 'status' => 0, 'data' => []], 400);
        }

        DB::beginTransaction();
        try {
            $bankDetails = null;
            $residentDetails = null;
            if (!is_null($request['bank_detail']) && isset($request['bank_detail']['account_number'])) {
                $bankDetails = BankDetail::create($request['bank_detail']);
            }

            $businessDetails = $request['business_detail'];

            if (!is_null($bankDetails)) {
                $businessDetails['bank_dtls_id'] = $bankDetails->id;
            }

            $businessDetails = BusinessDetail::create($businessDetails);
            $officeDetails = AddressDetail::create($request['office_detail']);

            if (!is_null($request['resident_detail']) && isset($request['resident_detail']['area']) && isset($request['resident_detail']['address']) && isset($request['resident_detail']['mobile'])) {
                $residentDetails = AddressDetail::create($request['resident_detail']);
            }
            
           

            $userDetail = $request['user_detail'];
            $userDetail['aadhar_card'] = null;
            $userDetail['person_photo'] = null;
            $userDetail['pancard'] = null;
            $userDetail['lightbill'] = null;
            $userDetail['cheque'] = null;
            if (!is_null($businessDetails)) {
                $userDetail['business_dtls_id'] = $businessDetails->id;
            }
            if (!is_null($officeDetails)) {
                $userDetail['office_address_id'] = $officeDetails->id;
            }
            if (!is_null($residentDetails)) {
                $userDetail['resident_address_id'] = $residentDetails->id;
            }

            $userDetail['password'] = Core::getLabel('DEFAULT_PASSWORD');

            $userDetail['state_name'] = $this->commonService->StateNameById($userDetail['state'])['name'];

            $user = User::create($userDetail);
            $role = "";
            if(isset($request['parent_id']) && $request['parent_id']!=null){
                
                $add_parent_user = $this->addUserParent($user->id,$request['parent_id']);
            }else{
                  if(isset($request['hub_parent']) && $request['hub_parent']!=null){
                    $add_hub_parent_user = $this->addUserParent($user->id,$request['hub_parent']);
               }
            } 
           
            if(isset($request['franchise_id']) && $request['franchise_id']!=null){
                
                $add_parent_company = $this->addCompanyParent($user->id,$request['franchise_id']);
            }

            if (!is_null($user)) {
                DB::commit();

                if ($request['user_detail']['aadhar_card']['data'] != null) {
                    $doc = $request['user_detail']['aadhar_card']['data'];
                    $extension = $request['user_detail']['aadhar_card']['extension'];
                    $file = $this->commonService->converBase64ToFileObject($doc);
                    $url = $this->uploadAttachment($file, $extension, 'aadhar_card', $user->id, $request->getSchemeAndHttpHost());
                    $user['aadhar_card'] = $url;
                }
                if ($request['user_detail']['person_photo']['data'] != null) {
                    $doc = $request['user_detail']['person_photo']['data'];
                    $extension = $request['user_detail']['person_photo']['extension'];
                    $file = $this->commonService->converBase64ToFileObject($doc);
                    $url = $this->uploadAttachment($file, $extension, 'person_photo', $user->id, $request->getSchemeAndHttpHost());
                    $user['person_photo'] = $url;
                }
                if ($request['user_detail']['pancard']['data'] != null) {
                    $doc = $request['user_detail']['pancard']['data'];
                    $extension = $request['user_detail']['pancard']['extension'];
                    $file = $this->commonService->converBase64ToFileObject($doc);
                    $url = $this->uploadAttachment($file, $extension, 'pancard', $user->id, $request->getSchemeAndHttpHost());
                    $user['pancard'] = $url;
                }
                if ($request['user_detail']['lightbill']['data'] != null) {
                    $doc = $request['user_detail']['lightbill']['data'];
                    $extension = $request['user_detail']['lightbill']['extension'];
                    $file = $this->commonService->converBase64ToFileObject($doc);
                    $url = $this->uploadAttachment($file, $extension, 'lightbill', $user->id, $request->getSchemeAndHttpHost());
                    $user['lightbill'] = $url;
                }
                if ($request['user_detail']['cheque']['data'] != null) {
                    $doc = $request['user_detail']['cheque']['data'];
                    $extension = $request['user_detail']['cheque']['extension'];
                    $file = $this->commonService->converBase64ToFileObject($doc);
                    $url = $this->uploadAttachment($file, $extension, 'cheque', $user->id, $request->getSchemeAndHttpHost());
                    $user['cheque'] = $url;
                }

                $user['code_number'] .= $user->id;
                $user->update();

                $user['plain_password'] = Core::getLabel('DEFAULT_PASSWORD');
                    $user_auth = auth()->user();
                             $actionlog=array();
                             $actionlog=array(
                          'user_id'=>$user_auth->id,
                          'action_type'=>'Add New '.Core::getConstant($user->role),
                          'target_id'=>$user->id,
                          'target_type'=>User::class,
                          'new_data'=>$request->all(),
                          'note'=>'Add New '. Core::getConstant($user->role).' User name '.$user->name." code numeber : ".$user->code_number
                          );
                 $result_log= $this->Actionlogs($actionlog);
                Mail::send(new UserCreated($user));

                return response()->json([
                    'message' => Core::getConstant($user->role) . ' Added Successfully',
                    'status' => 1, 'data' => ['user_id' => $user->id]
                ], 201);
            } else {
                DB::rollBack();
                return JsonResponse::sendErrorRes();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return JsonResponse::sendErrorRes();
        }
    }
    public function addUserParent($user_id,$parent_id)
    {
        try {
            $user_parent = Userparent::where('user_id',$user_id)->first();
            if($user_parent){
                $user_parent = Userparent::where('user_id',$user_id)->delete();
            }
             $Userparent = new Userparent();
             $Userparent->user_id = $user_id;
             $Userparent->parent_id = $parent_id;
             $Userparent->save();
            return $Userparent;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
   
    public function addCompanyParent($user_id,$parent_id)
    {
        try {
            foreach ($parent_id as $key => $value) {
             $Companyparent = new Companyparent();
             $Companyparent->company_id = $user_id;
             $Companyparent->franchise_id = $value;
             $Companyparent->save();
           }
            return $Companyparent;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }

    public function show($id)
    {
        try {
            if ($id != 102 && $id != 103 && $id != 104) {
                return JsonResponse::sendErrorRes();
            }
            $parameters = [$id];
            $query = Core::getQuery('GET_USER_TABLE_DETAILS');
            $query = $query . Core::getQuery('GET_USER_ROLE');
            $resultSet = DB::select($query, $parameters);
            return $resultSet;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }

    public function getUserDetail($id)
    {
        //try {
            $userDetails = User::select('users.*','user_parent.parent_id')->leftjoin('user_parent','user_parent.user_id','=','users.id')->where('users.id','=',$id)->first();
            if (is_null($userDetails)) {
                return response()->json(['message' => 'Invalid User', 'status' => 0, 'data' => []]);
            }
            $hub_parent_id = null;
            $perent_id_main = null;
            $officeDetails = AddressDetail::find($userDetails->office_address_id);
            $residentDetails = AddressDetail::find($userDetails->resident_address_id);
            $businessDetails = BusinessDetail::find($userDetails->business_dtls_id);
            if($userDetails->role == 102){
                    $parent_id = Userparent::select('parent_id')->where('user_id','=',$id)->first();
                   
                    if(!$parent_id){
                      $perent_id_main = null;
                    }else{
                        $perent_id_main = $parent_id->parent_id;
                        $hub_parent = Userparent::select('parent_id')->where('user_id','=',$perent_id_main)->first();
                         if(!$hub_parent){
                           $hub_parent_id = null;
                         }else{
                            $hub_parent_id = $hub_parent->parent_id;
                         }
                    }

            }else{
                $hub_parent = Userparent::select('parent_id')->where('user_id','=',$id)->first();
              
                    if(!$hub_parent){
                      $hub_parent_id = null;
                    }else{
                        $hub_parent_id = $hub_parent->parent_id;
                    }

            }

            if (!is_null($businessDetails)) {
                $bankDetails = BankDetail::find($businessDetails->bank_dtls_id);
            } else {
                $bankDetails = null;
            }
             $Companyparent = Companyparent::where('company_id','=',$id)->pluck('franchise_id')->toArray();
          if(!$Companyparent){
              $Companyparent = null;
          }

            return [
                'user_detail' => $userDetails,
                'office_detail' => $officeDetails,
                'resident_detail' => $residentDetails,
                'business_detail' => $businessDetails,
                'bank_detail' => $bankDetails,
                'perent_id' => $perent_id_main,
                'franchise_id' => $Companyparent,
                'hub_parent' => $hub_parent_id
            ];
       // } catch (\Exception $e) {
       //     return JsonResponse::sendErrorRes();
       // }
    }

    public function updateUser(UserRequest $request)
    {
        
        try {
            $bank = null;
            if (!is_null($request['bank_detail'])) {
                if (!is_null($request['bank_detail']['id'])) {
                    $bank = BankDetail::where('id', $request['bank_detail']['id'])->first();
                    $bank->update($request['bank_detail']);
                } else {
                    if (isset($request['bank_detail']['account_number'])) {
                        $bank = BankDetail::create($request['bank_detail']);
                    }
                }
            }

            $resident = null;
            if (!is_null($request['resident_detail'])) {
                if (!is_null($request['resident_detail']['id'])) {
                    $resident = AddressDetail::where('id', $request['resident_detail']['id'])->first();
                    $resident->update($request['resident_detail']);
                } else {
                    if (isset($request['resident_detail']['area']) && isset($request['resident_detail']['address']) && isset($request['resident_detail']['mobile'])) {
                        $resident = AddressDetail::create($request['resident_detail']);
                    }
                }
            }

            $userDetail = $request['user_detail'];
            if (!is_null($userDetail)) {
                $user = User::where('id', $userDetail['id']);
                if (!is_null($resident)) {
                    $userDetail['resident_address_id'] = $resident->id;
                }
                $userDetail['state_name'] = $this->commonService->StateNameById($userDetail['state'])['name'];
                $user->update($userDetail);
            }
             if(isset($request['hub_parent']) && $request['hub_parent']!=null){
                    $user = User::select('users.*','user_parent.parent_id','user_parent.user_id')->where('users.id', $userDetail['id'])->leftjoin('user_parent','user_parent.user_id','=','users.id')->first();
               
                if($request['hub_parent'] != $user->parent_id){

                    $add_parent_user = $this->addUserParent($user->id,$request['hub_parent']);
                }else{
                     
                    $perent = Userparent::where('user_id','=',$user->user_id)->first();
                    $perent->parent_id = $request['hub_parent'];
                    $perent->save();
                }
                
                
            }
             if(isset($request['parent_id']) && $request['parent_id']!=null){
                    $user = User::select('users.*','user_parent.parent_id','user_parent.user_id')->where('users.id', $userDetail['id'])->leftjoin('user_parent','user_parent.user_id','=','users.id')->first();
               
                if($request['parent_id'] != $user->parent_id){

                    $add_parent_user = $this->addUserParent($user->id,$request['parent_id']);
                }else{
                     
                    $perent = Userparent::where('user_id','=',$user->user_id)->first();
                    $perent->parent_id = $request['parent_id'];
                    $perent->save();
                }
                
                
            }
            if (isset($request['franchise_id']) && !is_null($request['franchise_id'])) 
            {  
                $Companyparent_ids = Companyparent::where('company_id', $userDetail['id'])->pluck('franchise_id')->toArray();
                 $result = array_diff($request['franchise_id'],$Companyparent_ids);
                 $result2 = array_diff($Companyparent_ids,$request['franchise_id']);

                    foreach ($result as $key => $value) {
                         $Companyparent = new Companyparent();
                         $Companyparent->company_id = $userDetail['id'];
                         $Companyparent->franchise_id = $value;
                         $Companyparent->save();
                        
                    }
                    foreach ($result2 as $key2 => $value2) {
                        $companyDetail = Companyparent::where('company_id', $userDetail['id'])->where('franchise_id', $value2)->delete();
                    }
                

                //$companyDetail->update($request['franchise_id']);
            }

            if (!is_null($request['office_detail'])) {
                $officeDetail = AddressDetail::where('id', $request['office_detail']['id']);
                $officeDetail->update($request['office_detail']);
            }

            if (!is_null($request['business_detail'])) {
                $businessDetail = BusinessDetail::where('id', $request['business_detail']['id']);
                if (!is_null($bank)) {
                    $bankData = $request['business_detail'];
                    $bankData['bank_dtls_id'] = $bank->id;
                     $businessDetail->update($bankData);
                }

            }

            return response()->json([
                'message' => 'User Updated Successfully',
                'status' => 1, 'data' => []
            ]);
            //echo "1";exit();
           // return 1;
        } catch (\Exception $e) {
            dd($e);
            return JsonResponse::sendErrorRes();
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return JsonResponse::sendErrorRes();
        }
        $flag = $user->update(['active_flag' => 0]);
        if ($flag) {
            return response()->json(['message' => 'Deleted Successfully', 'status' => 1, 'data' => []], 200);
        } else {
            return JsonResponse::sendErrorRes();
        }
    }

    public function uploadAttachment($file, $extension, $key, $id, $host)
    {
        $filename = $key . '-' . time() . '.' . $extension;
        $path = $file->storeAs('public/users/' . $id, $filename);
        $url = $host . Core::getLabel('PROJECT_FOLDER_PREFIX') . '/public' . storage::url($path);
        return $url;
    }

    public function isEmailExist($email)
    {
        $user = User::where('email', $email)->first();
        return $user ? true : false;
    }

    public function userList($type=null)
    {
        //try {
        if ($type == 102 || $type == 103 || $type == 104 || $type == 105) {
              
        $user_auth = auth()->user();

         $resultSet = User::select('users.id',DB::raw("CONCAT(bd.firm_name,' (',users.code_number,')') AS code_number"),'users.role','bd.firm_name','users.state','users.state_name','office.area','office.mobile')->leftjoin('address_details as office','office.id','=','users.office_address_id')->leftjoin('business_details as bd','bd.id','=','users.business_dtls_id')->where('users.active_flag',"=",1);
            if($user_auth->role == 104){
                    $user_parent = Userparent::where('user_parent.parent_id','=',$user_auth->id)->leftjoin('users','users.id','=','user_parent.user_id')->where('users.role',$type)->pluck('user_id')->toArray();
                   $resultSet = $resultSet->whereIn('users.id',$user_parent);
                } 
                if($type == 105){
                     if($user_auth->role == 103 || $user_auth->role == 102){

                    $user_parent = Companyparent::where('company_parent.franchise_id','=',$user_auth->id)->leftjoin('users','users.id','=','company_parent.franchise_id')->pluck('company_id')->toArray();
                   $resultSet = $resultSet->whereIn('users.id',$user_parent);
                  }
                }

                $resultSet=$resultSet->where('users.role',$type)->get();
                return $resultSet;
            } else {
                 
                 $resultSet = User::select('users.id',DB::raw("CONCAT(bd.firm_name,' (',users.code_number,')') AS name"),'users.role','bd.firm_name','users.state','users.state_name','office.area','office.mobile')->leftjoin('address_details as office','office.id','=','users.office_address_id')->leftjoin('business_details as bd','bd.id','=','users.business_dtls_id')->where('users.active_flag',"=",1)->get();//->toarray();
                 /*$object = new stdClass();
                 $object->id = NULL;
                 $object->name = "others";
                 $object->firm_name = "others";
                 $object->state = "others";
                 $object->state_name = "others";
                 $object->area = "others";
                 $object->mobile = "others";*/
//$myArray[] = $object;
                 $newone = array("id"=>NULL,"name"=>"others","firm_name"=>"others","state"=>"others","state_name"=>"others","area"=>"others","mobile"=>"others");
                 //$object = (object) $newone;
                // // echo "<pre>";
                //  print_r($object);
                //  print_r($newone);
                //  print_r($resultSet);exit();
                //  array_push($resultSet,$object);
                 $ResponseData = array("code" => 100,'message'=>"users list show sucessfully.",'status'=>'success','data'=>$resultSet);
                //  print_r($resultSet);
                 //exit();
                 return  $this->responseFun($ResponseData);
                   //return $resultSet;

              //  return JsonResponse::sendErrorRes();
            }
        //} catch (\Exception $e) {
         //   return JsonResponse::sendErrorRes();
       // }
    }
    public function franchiserlist()
    {
        try {
             $user_auth = auth()->user();
        
                $resultSet = User::select('users.id','users.code_number','users.role','bd.firm_name','users.state','users.state_name','office.area','office.mobile')->leftjoin('address_details as office','office.id','=','users.office_address_id')->leftjoin('business_details as bd','bd.id','=','users.business_dtls_id')->where('users.active_flag',"=",1);
                if($user_auth->role == 104){

                    $user_parent = Userparent::where('user_parent.parent_id','=',$user_auth->id)->leftjoin('users','users.id','=','user_parent.user_id')->pluck('user_id')->toArray();
                   $resultSet = $resultSet->whereIn('users.id',$user_parent);

                }else{
                $resultSet = $resultSet->whereIn('role',array(102,103,104));
               }
                $resultSet = $resultSet->get();
                return $resultSet;
          
        } catch (\Exception $e) {
           return JsonResponse::sendErrorRes();
        }
    }
    public function deleteUser($UserId)
    {    
         
        // Check if the assignment exists
        if (is_null($User = User::find($UserId))) {

           $ResponseData = array("code" => 400,'message'=>"Invalid User Id",'status'=>'error');
         return  $this->responseFun($ResponseData);  
        }     
                 $user = auth()->user();
                 $actionlog=array();
                 $actionlog=array(
                          'user_id'=>$user->id,
                          'action_type'=>'Delete User '.$User->id,
                          'target_id'=>$User->id,
                          'target_type'=>User::class,
                          'new_data'=>$User,
                          'note'=>'Deleted User'
                          );
                 $result_log= $this->Actionlogs($actionlog);
              
            $User->delete();
            $flag = $User->update(['active_flag' => 0]);

             $ResponseData = array("code" => 100,'message'=>"User delete sucessfully.",'status'=>'success','data'=>$User);
             return  $this->responseFun($ResponseData);


    }
   public function deleteTodoById($id)
    {
      // find task
      $todo = User::find($id);

      // delete
      $todo->delete();
      $ResponseData = array("code" => 100,'message'=>"User delete sucessfully.",'status'=>'success','data'=>$todo);
             return  $this->responseFun($ResponseData);
    }
     public function parentlist($id)
    {
        try {
             $user_auth = auth()->user();
        
                $Userparent = Userparent::where('parent_id','=',$id)->pluck('user_id')->toArray();
               $parentlist = array_unique($Userparent);

                    $user_parent = User::select('users.id',DB::raw("CONCAT(bd.firm_name,' (',users.code_number,')') AS code_number"),'users.role','bd.firm_name','users.state','users.state_name','office.area','office.mobile')->leftjoin('address_details as office','office.id','=','users.office_address_id')->leftjoin('business_details as bd','bd.id','=','users.business_dtls_id')->whereIn('users.id',$parentlist)->get();
                  
                $ResponseData = array("code" => 100,'message'=>"User parent sucessfully.",'status'=>'success','data'=>$user_parent);
             return  $this->responseFun($ResponseData);
                return $resultSet;
          
        } catch (\Exception $e) {
           return JsonResponse::sendErrorRes();
        }
    } 
    public function permissionlist()
    {
        try {
             $user_auth = auth()->user();
        $path="constants.permistionsidebar";
                //$permissionlist = 
        $permissionlist= config($path);
              if($permissionlist){
                $ResponseData = array("code" => 100,'message'=>"User permission sucessfully.",'status'=>'success','data'=>$permissionlist);
              }else{
                $ResponseData = array("code" => 104,'message'=>"User permission not available.",'status'=>'errors','data'=>array());
              }    
                
             return  $this->responseFun($ResponseData);
               
          
        } catch (\Exception $e) {
          return JsonResponse::sendErrorRes();
        }
    }
    public function Userpermission(Request $request)
    {
        try {
            // $user_auth = auth()->user();
                 
                $Userparent = Userpermission::where("role_code","=",$request->get('role_code'))->first();
              if($Userparent){
                $ResponseData = array("code" => 100,'message'=>"User permission sucessfully.",'status'=>'success','data'=>$Userparent);
              }else{
                $ResponseData = array("code" => 104,'message'=>"User permission not available.",'status'=>'errors','data'=>array());
              }    
                
             return  $this->responseFun($ResponseData);
               
          
        } catch (\Exception $e) {
           return JsonResponse::sendErrorRes();
        }
    }

    public function addUserpermission(UserpermissionRequest $request)
    {
        try {
             $user_auth = auth()->user();
        
                $Userpermission = new Userpermission();
                $Userpermission->role_name = $request['role_name'];
                $Userpermission->role_code = $request['role_code'];
                $Userpermission->permission = $request['permission'];
                $Userpermission->save();
                //::where('role_code','=',$user_auth->role)->first();
              if($Userpermission){
                $ResponseData = array("code" => 100,'message'=>"add User permission sucessfully.",'status'=>'success','data'=>$Userpermission);
              }else{
                $ResponseData = array("code" => 104,'message'=>"User permission not available.",'status'=>'errors','data'=>array());
              }    
                
             return  $this->responseFun($ResponseData);
               
          
        } catch (\Exception $e) {
           return JsonResponse::sendErrorRes();
        }
    }
    public function UpdateUserpermission(UserpermissionRequest $request)
    {
           try {
                $user_auth = auth()->user();
               
       
                $Userpermission = Userpermission::where("role_code","=",$request['role_code'])->first();
                $Userpermission->permission = $request['permission'];
                $Userpermission->save();
                //::where('role_code','=',$user_auth->role)->first();
              if($Userpermission){
                $ResponseData = array("code" => 100,'message'=>"Update Successfully User permission sucessfully.",'status'=>'success','data'=>$Userpermission);
              }else{
                $ResponseData = array("code" => 104,'message'=>"User permission not available.",'status'=>'errors','data'=>array());
              }    
                
             return  $this->responseFun($ResponseData);
               
          
        } catch (\Exception $e) {
           return JsonResponse::sendErrorRes();
        }
    }
     public function addfranchise()
    {
        try {
            
                $status_list = array(
                                    array('id'=>'','name'=>'Select Status'), 
                                    array('id'=>'active','name'=>'Active'), 
                                    array('id'=>'deactivate','name'=>'Deactivate'), 
                                    array('id'=>'block','name'=>'Block'));
              
                $franchise_role = array(array('id'=>'','name'=>'Select role'),array('id'=>'prepaid','name'=>'Prepaid'),array('id'=>'postpaid','name'=>'Postpaid'));
              
                $ResponseData = array("code" => 100,'message'=>"addfranchise show sucessfully.",'status'=>'success','status_list'=>$status_list,'franchise_role'=>$franchise_role);
             
                
             return  $this->responseFun($ResponseData);
               
          
        } catch (\Exception $e) {
           return JsonResponse::sendErrorRes();
        }
    }
}
