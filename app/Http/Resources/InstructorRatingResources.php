<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorRatingResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        "id"=> $this->id,
         'rating' => $this->rating,
         'review'=>$this->review,
         'is_accept'=>$this->is_accept,
         'user'=>[
            'id'=>$this->user->id,
            'first_name'=>$this->user->first_name,
            'last_name'=>$this->user->last_name,
            'image'=>asset('/storage/'.$this->user->image),
         ]
        ];
    }
}