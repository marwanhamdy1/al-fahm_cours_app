<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'earnings_point' => $this->earnings_point,
            'address' => $this->address,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'max_people' => $this->max_people,
            'image' => asset('/storage/'.$this->image),
            // 'signed_people' => $this->signed_people,
            'age_range' => $this->age_range,
            'session_count' => $this->session_count,
            'instructor' => $this->instructor,
            'active' => $this->active,
            'type' => $this->type,
            'category' => new CategoryResources($this->category),
            'rate_avg' => $this->rating_count / $this->rating_sum
        ];
    }
}