<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InsertRequest extends FormRequest
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
        return [
            'real_name' => 'required',
            'tenhou_name' => 'required',
            'month' => 'required',
            'twitter_id' => 'regex:/@[0-9a-zA-Z_]{1,15}/',
            'csvfile' => 'required|file|mimes:csv,txt|max:100',
        ];
    }
}
