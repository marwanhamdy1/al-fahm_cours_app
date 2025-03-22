<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
     protected $attendedSessions;

    // ✅ Pass attended sessions to constructor (avoid DB query per session)
    public function __construct($resource, $attendedSessions = [])
    {
        parent::__construct($resource);
        $this->attendedSessions = $attendedSessions;
    }
    public function toArray(Request $request): array
    {
       return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            // 'attended' => in_array($this->id, $this->attendedSessions) ? 1 : 0 // Fast lookup
        ];
    }
}