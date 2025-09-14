<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class AddUserToCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'assigned_by' => 'nullable|exists:users,id',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:unpaid,partially_paid,paid',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User not found',
            'course_id.required' => 'Course ID is required',
            'course_id.exists' => 'Course not found',
            'assigned_by.exists' => 'Assigned by user not found',
            'amount_paid.numeric' => 'Amount paid must be a number',
            'amount_paid.min' => 'Amount paid cannot be negative',
            'payment_status.in' => 'Invalid payment status',
        ];
    }

    /**
     * Handle validation failure.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error($validator->errors()->first(), 422, $validator->errors())
        );
    }
}