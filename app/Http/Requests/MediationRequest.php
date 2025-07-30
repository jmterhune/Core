<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediationRequest extends FormRequest
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

            // 'c_caseno' => 'unique:mediation_cases|required|min:5|max:255',
            'c_caseno' => 'required|min:5|max:255',
            'c_div' => 'required|numeric',
            // 'c_pltf_name' => 'required',
            // 'c_def_name' => 'required',
            // 'c_pltf_address' => 'required',
            // 'c_def_address' => 'required',
            // 'c_pltf_csz' => 'required',
            // 'c_def_csz' => 'required',
            // 'c_pltf_phone' => 'nullable|numeric',
            // 'c_def_phone' => 'nullable|numeric',
            // 'c_pltf_tele' => 'nullable|numeric',
            // 'c_def_tele' => 'nullable|numeric',
            // 'c_pltf_email.*.email' => 'email:rfc,dns',
            // 'c_def_email.*.email' => 'email:rfc,dns',
            // 'c_Pltf_a_id' => 'required',
            // 'c_def_a_id' => 'required',
            // 'p_a_name' => 'required',
            // 'd_a_name' => 'required',
            // 'p_a_asst' => 'required',
            // 'd_a_asst' => 'required',
            // 'p_a_email.*.email' => 'email:rfc,dns',
            // 'd_a_email.*.email' => 'email:rfc,dns',
            // 'p_a_email2.*.email' => 'email:rfc,dns',
            // 'd_a_email2.*.email' => 'email:rfc,dns',
            // 'p_a_phone' => 'required',
            // 'd_a_phone' => 'required',
            // 'p_a_fax' => 'required',
            // 'd_a_fax' => 'required',
            // 'c_type' => 'required',
            // 'c_otherm_text' => 'required',
            // 'c_cmmts' => 'required',
            // 'c_sch_notes' => 'required'
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
            "c_caseno.unique" => "Case number already exist!"
        ];
    }
}
