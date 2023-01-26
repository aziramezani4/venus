<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'phone' => 'required|regex:/(09)[0-9]{9}/|digits:11|numeric',
            'phone.digits' => 'The :phone digits must be 11 digits',
            'phone.regex' => 'The :phone regex must like 09171011144',
            'phone.numeric' => 'The :phone number must be numeric',
        ];
    }
    public function messages()
    {
        return [
            'phone.digits' => 'The :phone digits must be 11 digits',
            'phone.regex' => 'The :phone regex must like 09171011144',
            'phone.numeric' => 'The :phone number must be numeric',
        ];
    }
}
