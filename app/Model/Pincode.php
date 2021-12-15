<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Models\VedModel;

class Pincode extends Model
{
     protected $table = 'pincodes';
     protected $guarded=[];
    protected $dates = ['deleted_at'];
   
   public static function Databasedateformats($date = null)
  {   

      if($date != null)
      {   
                if(date_create($date)){
                   return date("Y-m-d h:i:s",strtotime($date));
        
                }else{
                    //2018-07-30
                       $date = explode('-', $date);
                       $date=array_reverse($date);
                       $date=implode("-", $date);
                    return $date;
                }
      }
      else
      {
         return "";
      }
  }

      public function scopeTextSearch($query, $search,$type)
    {
        if($type == "filter")
        {
            $filterArray = json_decode($search,true);
           // print_r($filterArray);exit();
            return $query->where(function ($query) use ($filterArray) {
                
                if(isset($filterArray['pincode']) && !empty($filterArray['pincode']))
                {
                    $query->where('pincodes.pincode', 'LIKE', '%'.$filterArray['pincode'].'%');
                }


                

             
            });
        }
        else
        {
            $search = explode('+', $search);

            return $query->where(function ($query) use ($search) {

                foreach ($search as $search) 
                {

         // 'pincode','area','state','state_name','active_flag','updated_at'];

                    $dates_search = $this->Databasedateformats($search);
                    $query->where('pincodes.pincode', 'LIKE', "%$search%")
                      ->orWhere('pincodes.area', 'LIKE', "%$search%")
                      ->orWhere('pincodes.state', 'LIKE', "%$search%")
                      ->orWhere('pincodes.state_name', 'LIKE', "%$search%")
                      ->orWhere('pincodes.state_name', 'LIKE', "%$search%")
                    
                      ->orWhere('pincodes.updated_at', 'LIKE', "%$dates_search%");
                   
                    
                }
            });
        }
    }
}
