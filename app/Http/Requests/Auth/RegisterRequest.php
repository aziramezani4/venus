<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'numeric', 'max:10'],
            'phone' => ['nullable', 'digits:11', 'unique:accounts,phone'],
            'national_code' => ['required', 'digits:10', 'unique:accounts,national_code'],
            'password' => ['nullable', 'string', 'min:8','max:16'],
        ];
    }
}
