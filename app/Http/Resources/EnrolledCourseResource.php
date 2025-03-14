<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrolledCourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'amount_paid' => $this->amount_paid,
            'remaining_amount' => $this->remaining_amount,
            'payment_status' => $this->payment_status,
            'attendance_percentage' => $this->attendance_percentage,
            'course' => new CourseResource($this->course),
        ];
    }
}