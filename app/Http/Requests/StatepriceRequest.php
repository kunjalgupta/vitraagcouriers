<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatepriceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {  $arr = explode('@', $this->route()->getActionName());
        $rules = [];
        if ($this->route()->methods()[0] == 'POST') {
            $rules = [
                'from_id' => 'required',
                'to_id' => 'required',
                'parcel_rate' => 'required',
                'document_rate' => 'required',
                'document_500g_rate' => 'required'
               // 'cargo_rate' => 'required'
            
               
            ];
        } else if ($this->route()->methods()[0] == 'PUT') {
            $rules = [];
        } else if ($this->route()->methods()[0] == 'GET') {
            $rules = [
                     //'from_id' => 'required',
                     //'courier_id' => 'required',
               ];
        }
        return $rules;
    }
}
