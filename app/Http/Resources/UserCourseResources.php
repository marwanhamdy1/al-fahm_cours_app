<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCourseResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data =  $this->assignedBy ? $this->assignedBy: $this->user;
        return [
            'user_id' => $data->id,
            "course_id" => $this->course_id,
            'first_name' => $data->first_name,
            'last_name' =>$data->last_name,
            'username' => $data->username,
            'identity_id' => $data->identity_id,
            'phone_number' => $data->phone_number,
            'email' => $data->color,
            'image' => asset('storage/' . $data->image),
            'role' => $data->role,
            "attended_session"=> $this->attended_session ??0
        ];
    }
}