<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class StoreEnrolledCourseRequest extends FormRequest
{
    /**
     * تحديد صلاحية المستخدم.
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
            'course_id'      => 'required|exists:courses,id', // يجب أن يكون الكورس موجودًا
            'assigned_by'    => 'nullable|exists:users,id', // إذا كان الأب هو من يسجل ابنه
            'amount_paid'    => 'nullable|numeric|min:0',
            'remaining_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:unpaid,partially_paid,paid',
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