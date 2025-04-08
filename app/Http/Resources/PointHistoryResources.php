<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointHistoryResources extends JsonResource
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
            'points' => $this->points,
            'is_gain' => $this->points < 0 ? 0 : 1, // If negative, 0; otherwise, 1
            'description' => $this->description,
            'create_at' => $this->created_at,
            // 'created_at' => $this->created_at->format('Y-m-d H:i:s') ?? null,
        ];
    }
}