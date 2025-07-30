<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttorneyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'name' => 'required|min:5|max:255',
            'email.*.email' => 'email:rfc,dns',
            'email' => 'required',
            'bar_num' => [
                'required','numeric','doesnt_start_with:0',
                Rule::unique('attorneys')->ignore($this->id),
            ],
            'notes' => 'max:255'
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'bar_num.doesnt_start_with' => 'Invalid Bar Number, Remove leading "0"s',
            'email.*.email.email' => 'Invalid Email Address(es).'
        ];
    }
}
