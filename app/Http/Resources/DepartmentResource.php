<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
            'session' => SessionResource::collection($this->courseSessions),
            's' =>$this->attendedSessions,
        ];
    }
}