<?php

namespace App\Http\Controllers;

//use Input;
use App\mWork\Core;
use App\Model\Pincode;
use App\Model\Statepricelist;
use App\Model\Specialpincodeprice;
use App\Model\Statelist;
use App\mWork\CommonService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PincodeRequest;
use App\Http\Requests\SpecialpincodepriceRequest;
use App\Http\Requests\StatepriceRequest;
use App\mWork\JsonResponse;

class PincodeController extends Controller
{
    protected $commonService;
    public function __construct(CommonService $commonService)
    {
        if (!is_null(auth()->user()) && !auth()->user()->isCompany()) {
            return response()->json(['message' => 'Action Not Authorized'], 401);
        }
        $this->commonService = $commonService;
    }

    public function index()
    {
        return Pincode::all();
    }

    public function show($id)
    {
        return Pincode::find($id);
    }

    public function store(PincodeRequest $request)
    {
        try {
           // $request['state_name'] = $this->commonService->StateNameById($request->state)['name'];
            return Pincode::create($request->all());
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['message' => 'Pincode Already Exist', 'status' => 0, 'data' => []], 400);
        }
    }

    public function update(PincodeRequest $request, $id)
    {

        $pincode = Pincode::find($id);
        if (is_null($pincode)) {
            return response()->json(['message' => "Record Not Found"], 404);
        }
        //$request['state_name'] = $this->commonService->StateNameById($request->state)['name'];
        $pincode->update($request->only('pincode', 'area','state','state_name','active_flag'));
        return $pincode;
    }

    public function destroy($id)
    {
        $pincode = Pincode::find($id);
        if (is_null($pincode)) {
            return response()->json(['message' => "Record Not Found"], 404);
        }
        $pincode->delete();
        return response()->json(['message' => "Record Deleted Successfully"], 200);
    }

    public function stateList()
    {
        try {
            $stateList = Statelist::select('id','name')->get()->toarray();
            return response()->json([
                'message' => 'State List SuccessFully Retreived',
                'status' => 1,
                'data' => $stateList
                //'data' => $this->commonService->indianAllStatesList()
            ]);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['message' => 'Internal Error', 'status' => 0, 'data' => []], 500);
        }
    }

    public function isPincodeExist(PincodeRequest $request)
    {
        try {
            $parameters = [$request->pincode, $request->state];
            $query = Core::getQuery('GET_AREA_FROM_PINCODE');
            $resultSet = DB::select($query, $parameters);
            if (count($resultSet) == 0) {
                return response()->json([
                    'message' => 'Pincode Not Exist',
                    'status' => 1, 'data' => []
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Pincode Already Exist',
                    'status' => 0, 'data' => []
                ], 422);
            }
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function addStateprice(StatepriceRequest $request)
    {
         try {
               $Stateprice = new Statepricelist();
               $Stateprice->from_id = $request->from_id;
               $Stateprice->to_id = $request->to_id;
               $Stateprice->parcel_rate = $request->parcel_rate;
               $Stateprice->document_rate = $request->document_rate;
               $Stateprice->document_500g_rate = $request->document_500g_rate;
               $Stateprice->cargo_rate = $request->cargo_rate;
               $Stateprice->transit_days = $request->transit_days;
               $Stateprice->save();

              $ResponseData = array("code" => 100,'message'=>"Add State price sucessfully.",'status'=>'success','data'=>$Stateprice);
              return  $this->responseFun($ResponseData);
             } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }

    public function getStatePricelist()
    {
         try {
               $Statepricelist = Statepricelist::select('state_price_list.*','fs.name as from_state_name','ts.name as to_state_name')->leftjoin('state_list as fs','fs.id','=','state_price_list.from_id')->leftjoin('state_list as ts','ts.id','=','state_price_list.to_id')->get()->toarray();

              $ResponseData = array("code" => 100,'message'=>"Get State Price List sucessfully.",'status'=>'success','data'=>$Statepricelist);
              return  $this->responseFun($ResponseData);
             } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
     public function editStateprice(StatepriceRequest $request)
    {
         try {
               $Stateprice = Statepricelist::find($request->id);
               $Stateprice->from_id = $request->from_id;
               $Stateprice->to_id = $request->to_id;
               $Stateprice->parcel_rate = $request->parcel_rate;
               $Stateprice->document_rate = $request->document_rate;
               $Stateprice->document_500g_rate = $request->document_500g_rate;
               $Stateprice->cargo_rate = $request->cargo_rate;
               $Stateprice->transit_days = $request->transit_days;
               $Stateprice->save();

              $ResponseData = array("code" => 100,'message'=>"update State price sucessfully.",'status'=>'success','data'=>$Stateprice);
              return  $this->responseFun($ResponseData);
             } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }

     public function getSpecialPricelist()
    {
         try {
               $Statepricelist = Specialpincodeprice::get()->toarray();

              $ResponseData = array("code" => 100,'message'=>"Get Special pincode price sucessfully.",'status'=>'success','data'=>$Statepricelist);
              return  $this->responseFun($ResponseData);
             } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function addSpecialpincodeprice(SpecialpincodepriceRequest $request)
    {
         try {
               $Specialpincodeprice = new Specialpincodeprice();
               $Specialpincodeprice->state_id = $request->state_id;
               $Specialpincodeprice->pincode = $request->pincode;
               $Specialpincodeprice->transit_days = $request->transit_days;
               $Specialpincodeprice->comment = $request->comment;
               $Specialpincodeprice->parcel_rate = $request->parcel_rate;
               $Specialpincodeprice->document_rate = $request->document_rate;
               $Specialpincodeprice->document_500g_rate = $request->document_500g_rate;
               $Specialpincodeprice->cargo_rate = $request->cargo_rate;
               
               $Specialpincodeprice->save();

              $ResponseData = array("code" => 100,'message'=>"Add Special pincode price sucessfully.",'status'=>'success','data'=>$Specialpincodeprice);
              return  $this->responseFun($ResponseData);

             } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    } 
    public function editSpecialpincodeprice(SpecialpincodepriceRequest $request)
    {
         try {
               $Specialpincodeprice = Specialpincodeprice::find($request->id);
               $Specialpincodeprice->state_id = $request->state_id;
               $Specialpincodeprice->pincode = $request->pincode;
               $Specialpincodeprice->transit_days = $request->transit_days;
               $Specialpincodeprice->comment = $request->comment;
               $Specialpincodeprice->parcel_rate = $request->parcel_rate;
               $Specialpincodeprice->document_rate = $request->document_rate;
               $Specialpincodeprice->document_500g_rate = $request->document_500g_rate;
               $Specialpincodeprice->cargo_rate = $request->cargo_rate;
               
               $Specialpincodeprice->save();


              $ResponseData = array("code" => 100,'message'=>"edit Special pincode price sucessfully.",'status'=>'success','data'=>$Specialpincodeprice);
              return  $this->responseFun($ResponseData);
             } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function deleteSpecialprice($id)
    { 
        // Check if the assignment exists
        if (is_null($Specialpincodeprice = Specialpincodeprice::find($id))) {

           $ResponseData = array("code" => 400,'message'=>"Invalid Special pincode price Id",'status'=>'error');
         return  $this->responseFun($ResponseData);  
        }     
                 $user = auth()->user();
                 $actionlog=array();
                 $actionlog=array(
                          'user_id'=>$user->id,
                          'action_type'=>'Delete Special pincode'.$user->id,
                          'target_id'=>$Specialpincodeprice->id,
                          'target_type'=>Specialpincodeprice::class,
                          'new_data'=>$id,
                          'note'=>'Deleted Special pincode price'
                          );
                 $result_log= $this->Actionlogs($actionlog);
              
            $Specialpincodeprice->delete();
            //$flag = $User->update(['active_flag' => 0]);

             $ResponseData = array("code" => 100,'message'=>"State price list delete sucessfully.",'status'=>'success','data'=>$Specialpincodeprice);
             return  $this->responseFun($ResponseData);


    
    } 
    public function deleteStatepricelist($id)
    { 
        // Check if the assignment exists
        if (is_null($Statepricelist = Statepricelist::find($id))) {

           $ResponseData = array("code" => 400,'message'=>"Invalid State price list",'status'=>'error');
         return  $this->responseFun($ResponseData);  
        }     
                 $user = auth()->user();
                 $actionlog=array();
                 $actionlog=array(
                          'user_id'=>$user->id,
                          'action_type'=>'Delete State price list'.$user->id,
                          'target_id'=>$Statepricelist->id,
                          'target_type'=>Statepricelist::class,
                          'new_data'=>$id,
                          'note'=>'Deleted State price list'
                          );
                 $result_log= $this->Actionlogs($actionlog);
              
            $Statepricelist->delete();
            //$flag = $User->update(['active_flag' => 0]);

             $ResponseData = array("code" => 100,'message'=>"State price list delete sucessfully.",'status'=>'success','data'=>$Statepricelist);
             return  $this->responseFun($ResponseData);


    
    }
    public function SearchPincode($pincode)
    {
        try {
            $Pincode = Pincode::where('pincode',$pincode)->where('active_flag','=',1)->first();
            if($Pincode){

                    $ResponseData = array("code" => 100,'message'=>"Service is available.",'status'=>'success','data'=>$Pincode);
            }else{
                    $ResponseData = array("code" => 400,'message'=>"Service is not available.",'status'=>'error','data'=>array('picode'=>"no service"));
            }
            
             
            return $ResponseData;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function SearchfrenchiserPincode($pincode)
    {
        try {
            $Pincode = Pincode::where('pincode',$pincode)->where('active_flag','=',1)->first();
            if($Pincode){

                    $ResponseData = array("code" => 100,'message'=>"Picode is available.",'status'=>'success','data'=>$Pincode);
            }else{
                    $ResponseData = array("code" => 400,'message'=>"Picode is not available.",'status'=>'error','data'=>array('picode'=>"no service"));
            }
            
             
            return $ResponseData;
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
    public function getpincodeList(PincodeRequest $request)
    {
        //$request['sender']
        if (isset($request['limit'])) {
            $limit = $request['limit'];
        } else {
            $limit = 50;
        }

         

        

       // if (Input::has('offset')) {
             if (isset($request['offset'])) {
            $offset = $request['offset'];
        } else {
            $offset = 0;
        }

        //if (Input::has('limit')) {
        if (isset($request['limit'])) {
           // $limit = e(Input::get('limit'));
            $limit = $request['limit'];
        } else {
            $limit = 50;
        }

        //if (Input::get('sort')=='form_name') {
        if (isset($request['sort'])) {
            $sort = $request['sort'];
        } else {
            $sort = $request['sort'];
        }

        // Grab all the groups

       $Pincode  = Pincode::select('*');
    

        //if (Input::has('filter')) 
        //{
       if (isset($request['filter'])) {
          $Pincode = $Pincode->TextSearch($request['filter'],"filter");
        }
        else
        {
            //if (Input::has('search')) {
            if (isset($request['search'])) {
                $Pincode = $Pincode->TextSearch($request['search'],"search");
            }
        } 
        $order = $request['order'] === 'asc' ? 'asc' : 'desc';

        $allowed_columns =
         [
           'pincode','area','state','state_name','active_flag','updated_at'];

        $sort = in_array($sort, $allowed_columns) ? $sort : 'id';
        $Pincode = $Pincode->orderBy($sort, $order);

        $formsCount = $Pincode->count();
        $Pincode = $Pincode->skip($offset)->take($limit)->get();

        $rows = array();

        foreach ($Pincode as $Pincodes) {

            

            $rows[] = array(
                'id'         => $Pincodes->id,
                'pincode'         => $Pincodes->pincode,
                'area'         => $Pincodes->area,
                'state'         => $Pincodes->state,
                'state_name'         => $Pincodes->state_name,
                'active_flag'         => $Pincodes->active_flag,
                
                'updated_at'        => $Pincodes->updated_at,
                
            );

        }

        $data = array('total'=>$formsCount, 'rows'=>$rows);
        return $data;
    }
}
