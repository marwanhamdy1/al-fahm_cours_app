<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class AssignCourseRequest extends FormRequest
{
    /**
     * تحديد صلاحية المستخدم.
     */
    public function authorize(): bool
    {
        return true; // تأكد من أن المستخدم لديه الصلاحية
    }

    /**
     * قواعد التحقق.
     */
    public function rules(): array
    {
        return [
            'id'        => 'required|exists:enrolled_courses,id', // يجب أن يكون التسجيل موجودًا
            'child_id'  => 'required|exists:users,id', // يجب أن يكون الطفل موجودًا
        ];
    }

    /**
     * تخصيص الاستجابة عند فشل التحقق.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error($validator->errors()->first(), 422)
        );
    }
}