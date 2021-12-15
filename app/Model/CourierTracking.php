<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CourierTracking extends Model
{
    protected $guarded=[];
    protected $table = 'courier_tracking';

     public static function addTrakingPoint($request)
    {
        $Couriertracking= new CourierTracking();
        $Couriertracking->user_id = (isset($request['user_id']) && $request['user_id']!="")?$request['user_id']:0;
        $Couriertracking->courier_id = (isset($request['courier_id']) && $request['courier_id']!="")?$request['courier_id']:0;
        $Couriertracking->courier_location = (isset($request['courier_location']) && $request['courier_location']!="")?$request['courier_location']:"";
        $Couriertracking->status = (isset($request['status']) &&  $request['status'] !="")?$request['status']:'pending';
        
        if(isset($request['status']) &&  $request['status'] =="forward_to"){
        $Couriertracking->forward_id = (isset($request['forward_id']) &&  $request['forward_id'] !="")?$request['forward_id']:NULL;
        }
        $Couriertracking->save();
        $courier_details = CourierTracking::select('courier_tracking.*','users.name as user_name','users.office_address_id','address_details.area','address_details.address')->leftjoin('users','users.id','=','courier_tracking.user_id')->leftjoin('address_details','address_details.id','=','users.office_address_id')->where('courier_tracking.courier_id',$request['courier_id'])->get();
      
        return $courier_details;
    }
}
