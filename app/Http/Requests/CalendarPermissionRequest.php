<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalendarPermissionRequest extends FormRequest
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
        $court_id = $this->court_id;

        return [
            'active' => 'required',
            'user_id' => [
                Rule::unique('court_permissions')->ignore($this->user_id)->where(fn ($query) => $query->where('judge_id', $this->judge_id)->where('user_id', $this->user_id)->where('editable', $this->editable))
            ]
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
            'user_id.unique' => 'This user already has Permission for this Judge.'
        ];
    }
}
