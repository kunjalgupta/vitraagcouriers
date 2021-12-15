<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        if ($this->route()->methods()[0] == 'POST') {
            $rules = [
                'user_detail.role' => 'required',
                'user_detail.name' => 'required|string',
                'user_detail.state' => 'required',
                'user_detail.code_number' => 'required|string',
                //'user_detail.father_name' => 'required|string',
                'user_detail.email' => 'required|email|max:255',
                'office_detail.area' => 'required',
                'office_detail.address' => 'required',
                'office_detail.mobile' => 'required',
                'business_detail.firm_name' => 'required',
                'business_detail.territory_areas' => 'required'
            ];
        } else if ($this->route()->methods()[0] == 'PUT') {
            $rules = [];
        }
        return $rules;
    }
}
