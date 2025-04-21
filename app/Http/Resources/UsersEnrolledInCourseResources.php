<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersEnrolledInCourseResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray(Request $request): array
{
    $data = $this->assignedBy ?: $this->user;

    $amountPaid = floatval($this->amount_paid);
    $remaining = floatval($this->remaining_amount);
    $total = $amountPaid + $remaining;

    $paymentMessage = ($this->status === 'approved' && $this->payment_status === 'paid')
        ? 'تم دفع المبلغ كامل'
        : 'تم دفع ' . $amountPaid . ' شيكل من أصل ' . $total . '، المتبقي ' . $remaining . ' شيكل';

    return [
        "id" => $this->id,
        'user_id' => $data->id,
        "course_id" => $this->course_id,
        'first_name' => $data->first_name,
        'last_name' => $data->last_name,
        'username' => $data->username,
        'image' => $data->image ? asset('storage/' . $data->image) : null,
        "status" => $this->status,
        "payment_status" => $this->payment_status,
        "payment_message" => $paymentMessage,
    ];
}

}