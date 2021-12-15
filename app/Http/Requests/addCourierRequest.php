<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class addCourierRequest extends FormRequest
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
    {
        $arr = explode('@', $this->route()->getActionName());
        $rules = [];
        if ($this->route()->methods()[0] == 'POST') {
            $rules += [
                'sender.origin' => 'required',
                'sender.name' => 'required',
                'sender.mobile' => 'required|max:12',
                'sender.address' => 'required',
                'sender.pincode' => 'required',
                'receiver.destination' => 'required',
                'receiver.name' => 'required',
                'receiver.mobile' => 'required',
                'receiver.address' => 'required',
                'receiver.pincode' => 'required',
                //'courier.courier_type' => 'required',
                'courier.weight' => 'required_without:courier.quantity',
                'courier.quantity' => 'required_without:courier.weight',
                'courier.pincode' => 'required|max:8',
              //  'courier.amount' => 'required',
              //  'courier.total_amount' => 'required',
                'courier.adding_user_id' => 'required',
                'courier.awb_number' => 'required'
            ];
        } else if ($this->route()->methods()[0] == 'GET') {
            if ($arr[1] == 'getArea') {
                $rules['pincode'] = 'required';
                $rules['state'] = 'required';
            }
            if ($arr[1] == 'getRate') {
                $rules['pincode'] = 'required';
                $rules['courier_type'] = 'required';
                $rules['state'] = 'required';
            }
            if ($arr[1] == 'statuslist') {
                //$rules['pincode'] = 'required';
                //$rules['courier_type'] = 'required';
                //$rules['state'] = 'required';
            }
            if ($arr[1] == 'getTrakingList') {
                $rules['user_id'] = 'required';
                $rules['courier_id'] = 'required';
               // $rules['state'] = 'required';
            }
        }
        return $rules;
    }
}
