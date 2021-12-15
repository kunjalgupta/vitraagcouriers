<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAuthRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $arr = explode('@', $this->route()->getActionName());
        $rules = [];

        if ($arr[1] == 'changePassword') {
            $rules['password'] = 'required|string';
        } else {

            $rules = [
                'email' => 'required_without:mobile|email|max:255',
                'mobile' => 'required_without:email|digits:10',
                'password' => 'required|string',
            ];

            if ($arr[1] == 'createUser') {
                $rules['email'] = 'required_without:mobile|email|max:255|unique:users';
                $rules['password'] = 'required|string';
                $rules += ['email' => 'unique:users', 'name' => 'required|string|max:50'];
            }
        }
        return $rules;
    }
}
