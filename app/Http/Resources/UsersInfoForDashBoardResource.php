<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersInfoForDashBoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $enrollCourse = $this->enrolledCourses->first();
        // Get the first enrolled course if available
        $firstEnrollment = $this->enrolledCourses->first();
        $titleFirstEnrollment=null;

        if ($firstEnrollment) {
            $titleFirstEnrollment = $firstEnrollment->course->title ?? null;
        }
        return [
        'id' => $this->id,
        'first_name' => $this->first_name,
        'last_name'=> $this->last_name,
        'username'=> $this->username,
        'identity_id'=> $this->identity_id,
        'email' => $this->email,
        'phone_number'=> $this->phone_number,
        'image'=> asset('/storage/'.$this->image),
        'color' => $this->color,
        'date_of_birth'=> $this->date_of_birth,
        'school_name' => $this->school_name,
        'grade_name' => $this->grade_name,
        'educational_stage' => $this->educational_stage,
        'neighborhood' => $this->neighborhood,
        'parent_id'=> $this->parent_id,
        'parent_type' => $this->parent_type,
        'child_type' => $this->child_type,
        'mother_name' => $this->mother_name,
        'mother_identity_id'=> $this->mother_identity_id,
        'points' => $this->points,
        'first_enrolled_course' => $titleFirstEnrollment,
        ];
    }
}