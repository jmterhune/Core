<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeslotEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return backpack_auth()->check();
    }

    protected function prepareForValidation()
    {
        //Here email we are receiving as comma seperated, so we make it array


        $this->merge(['plaintiff_email' => array_filter(array_map('trim',explode(';', $this->plaintiff_email)))]);
        $this->merge(['defendant_email' => array_filter(array_map('trim',explode(';', $this->defendant_email)))]);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'plaintiff_email.*' => 'email:rfc,dns,filter',
            'defendant_email.*' => 'email:rfc,dns,filter',
            'otherMotion.*' => 'required|max:255'
        ];
    }

    public function messages()
    {
        return [
            'plaintiff_email.*' => 'One or Many Plaintiffs Email Address are Invalid',
            'defendant_email.*' => 'One or Many Defendant Email Address are Invalid',
        ];
    }
}
