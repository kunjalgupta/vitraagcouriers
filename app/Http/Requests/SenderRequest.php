<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SenderRequest extends FormRequest
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
               
                'smstosender' => 'required'
               
            ];
        } else if ($this->route()->methods()[0] == 'PUT') {
            $rules = [];
        } else if ($this->route()->methods()[0] == 'GET') {
            $rules = [];
        }
        return $rules;
    }
}
