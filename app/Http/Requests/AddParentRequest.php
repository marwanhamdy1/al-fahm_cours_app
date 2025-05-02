<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;
class AddParentRequest extends FormRequest
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
            'first_name'           => 'required|string|max:255',
            'last_name'            => 'required|string|max:255',
            'email'                => 'nullable|email|unique:users,email',
            'phone_number'         => 'required|string|unique:users,phone_number',
            'password'             => 'nullable|string|min:6|confirmed',
            'role'                 => 'required|string|in:parent',
            'parent_type'          => 'required|string|in:father,mother',
            'identity_id'          => 'required|string|max:255|unique:users,identity_id',
            'username'             => 'nullable|string|unique:users,username|max:255',
            'color'                => 'nullable|string|max:255',
            'image'                => 'nullable|string|max:255',
            'date_of_birth'        => 'required|date',
            'neighborhood'         => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ResponseHelper::error($validator->errors()->first(), 422));
    }
}