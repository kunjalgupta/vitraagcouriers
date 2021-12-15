<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Model\Actionlog;
use App\Model\Smslog;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    	public static function Actionlogs($row){
		
		    $logaction = new Actionlog();
            $logaction->user_id = $row['user_id'];
            $logaction->target_type = $row['target_type'];
            $logaction->action_type = $row['action_type'];
            $logaction->target_id = $row['target_id'];
            $logaction->note = $row['note'];
			$logaction->new_data = json_encode($row['new_data']);
            $logaction->save();
     
			
			return "successful";
		
	}
	 public static function smslogs($row){
		
       
        $logaction = new Smslog();
        $logaction->awb_number = $row['awb_number'];
        $logaction->mobile = $row['mobile'];
        $logaction->msg = $row['msg'];
        $logaction->log = json_encode($row['log']);
        $logaction->note = $row['note'];
       
        $logaction->save();
 
        
        return "successful";
    
}
	public static function displayDates($date1, $date2, $format = 'Y-m-d' ) {
      $dates = array();
      $current = strtotime($date1);
      $date2 = strtotime($date2);
      $stepVal = '+1 day';
      while( $current <= $date2 ) {
         $dates[] = date($format, $current);
         $current = strtotime($stepVal, $current);
      }
      return $dates;
   }
}
