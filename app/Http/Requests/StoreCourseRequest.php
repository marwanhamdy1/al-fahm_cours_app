<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on authentication logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'title_he' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image',
            'earnings_point' => 'required|integer',
            'address' => 'required|string',
            'address_he' => 'required|string',
            'description' => 'required|string',
            'description_he' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_people' => 'required|integer|min:1',
            'age_range' => 'required|string',
            'session_count' => 'nullable|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'instructor_id' => 'required|exists:instructors,id',
            'active' => 'required|in:0,1',
            'item_type'=>'required|string',
        ];
    }

    /**
     * Handle validation failure.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}