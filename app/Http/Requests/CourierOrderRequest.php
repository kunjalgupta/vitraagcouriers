<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourierOrderRequest extends FormRequest
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
                'sender_origin' => 'required',
                'sender_name' => 'required',
                'sender_mobile' => 'required|max:12',
                'sender_address' => 'required',
                'sender_pincode' => 'required',
                'receiver_destination' => 'required',
                'receiver_name' => 'required',
                'receiver_mobile' => 'required|max:12',
                'receiver_address' => 'required',
                'receiver_pincode' => 'required',
                'item' => 'required',
                'courier_type' => 'required',
                'weight' => 'required_without:quantity',
                'quantity' => 'required_without:weight'
            ];
        } else if ($this->route()->methods()[0] == 'GET') {
            if ($arr[1] == 'rejectOrder') {
                $rules += [
                    'id' => 'required'
                ];
            } elseif ($arr[1] == 'courierOrderList') {
                $rules += [
                    'status' => 'required'
                ];
            }
        }
        return $rules;
    }
}
