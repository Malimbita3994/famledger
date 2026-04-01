<?php

namespace App\Events;

use App\Models\Milestone;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FamilyTimelineUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $familyId,
        public ?Milestone $milestone = null,
        public string $action = 'created',
        public ?int $milestoneId = null
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('family.'.$this->familyId)];
    }

    public function broadcastAs(): string
    {
        return 'timeline.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'milestone_id' => $this->milestoneId ?? $this->milestone?->id,
            'milestone' => $this->milestone ? [
                'id' => $this->milestone->id,
                'title' => $this->milestone->title,
                'date' => $this->milestone->date->format('Y-m-d'),
                'description' => $this->milestone->description,
                'media_url' => $this->milestone->media_url,
            ] : null,
        ];
    }
}
