<?php

namespace App\Models;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class VedModel extends Model
{
    // Setters that are appropriate across multiple models.
    public function setPurchaseDateAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['purchase_date'] = $value;
        return;
    }

    /**
     * @param $value
     */
    public function setPurchaseCostAttribute($value)
    {
        $value =  Helper::ParseFloat($value);

        if ($value == '0.0') {
            $value = null;
        }
        $this->attributes['purchase_cost'] = $value;
        return;
    }

    public function setLocationIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['location_id'] = $value;
        return;
    }

    public function setCategoryIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['category_id'] = $value;
        // dd($this->attributes);
        return;
    }

    public function setSupplierIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['supplier_id'] = $value;
        return;
    }

    public function setDepreciationIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['depreciation_id'] = $value;
        return;
    }

    public function setManufacturerIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['manufacturer_id'] = $value;
        return;
    }

    public function setMinAmtAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['min_amt'] = $value;
        return;
    }

    public function setParentIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['parent_id'] = $value;
        return;
    }


    //
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }
    public static function Databasedateformats($date = null)
  {   

      if($date != null)
      {   
                if(date_create($date)){
                   return date("Y-m-d",strtotime($date));
        
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
  public static function Datetimeformats($date = null)
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
}
