<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;
class CompleteProfile extends FormRequest
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
            'last_name'     => 'required|string|max:255',
            'email'         => 'nullable|email|unique:users,email,' ,
            'password'      => 'nullable|string|min:6|confirmed', // Uses password confirmation
            'role'          => 'required|string|in:parent,individual,child', // Add other roles if needed
            'parent_type'   => 'nullable|string|in:father,mother', // Add other roles if needed
            'child_type'    => 'nullable|string|in:male,female', // Add other roles if needed
            'identity_id'   => 'required|string|max:255', // Only required for specific roles
            'parent_identity_id'   => 'nullable|string|max:255', // Only required for specific roles
            'neighborhood'  => 'nullable|string|max:255',
            'username'      => 'nullable|string|unique:users,username|max:255', // Ensure unique username
            'color'         => "nullable|string|max:255",
            'image'         => 'nullable|string|max:255',
            'date_of_birth' => 'required|date',
        ];
    }
      protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ResponseHelper::error($validator->errors()->first(),422));
    }
}