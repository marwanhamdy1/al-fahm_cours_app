<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class AddChildRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'username'      => 'required|string|unique:users,username|max:255', // Ensure unique username
            'password'      => 'required|string|min:6|confirmed', // Uses password confirmation
            'email'         => 'nullable|email|unique:users,email,' ,
            'phone_number'  => 'nullable|string|regex:/^\+?[0-9]{7,15}$/|unique:users,phone_number',
            'child_type'    => 'required|string|in:male,female', // Add other roles if needed
            'identity_id'   => 'required|string|max:255', // Only required for specific roles
            'color'         => "required|string|max:255",
            'image'         => 'required|string|max:255',
            'date_of_birth' => 'required|date',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ResponseHelper::error($validator->errors()->first(),422));
    }
}