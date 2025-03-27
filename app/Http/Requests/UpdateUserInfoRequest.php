<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;
class UpdateUserInfoRequest extends FormRequest
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
         $userId = $this->route('id'); // Get ID from route parameter
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,'.$userId,
            'identity_id' => 'sometimes|string|max:255|unique:users,identity_id,'.$userId,
            'email' => 'sometimes|email|max:255|unique:users,email,'.$userId,
            'phone_number' => 'sometimes|string|max:20|unique:users,phone_number,'.$userId,
            // 'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color' => 'sometimes|string|max:50',
            'date_of_birth' => 'sometimes|date',
            'school_name' => 'sometimes|string|max:255',
            'grade_name' => 'sometimes|string|max:100',
            'educational_stage' => 'sometimes|string|max:100',
            'neighborhood' => 'sometimes|string|max:255',
            // 'parent_id' => 'sometimes|exists:users,id',
            // 'parent_type' => 'sometimes|string|max:100',
            // 'child_type' => 'sometimes|string|max:100',
            'mother_name' => 'sometimes|string|max:255',
            'mother_identity_id' => 'sometimes|string|max:255',
            'points' => 'sometimes|integer',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error($validator->errors()->first(), 422)
        );
    }
}