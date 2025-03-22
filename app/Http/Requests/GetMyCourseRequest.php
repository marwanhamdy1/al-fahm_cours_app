<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class GetMyCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   public function authorize(): bool
    {
        return true; // تأكد من أن المستخدم لديه صلاحية
    }

    /**
     * قواعد التحقق.
     */
    public function rules(): array
    {
        return [
            'child_id'    => 'nullable|exists:users,id',
            'status'    => 'nullable|in:on_basket,pending,approved',
        ];
    }

     protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error($validator->errors()->first(), 422)
        );
    }
}