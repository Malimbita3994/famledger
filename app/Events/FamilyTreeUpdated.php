<?php

namespace App\Events;

use App\Models\FamilyRelationship;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FamilyTreeUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $familyId,
        public ?FamilyRelationship $relationship = null,
        public string $action = 'created',
        public ?int $relationshipId = null
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('family.'.$this->familyId)];
    }

    public function broadcastAs(): string
    {
        return 'tree.updated';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'action' => $this->action,
            'relationship_id' => $this->relationshipId ?? $this->relationship?->id,
        ];

        if ($this->relationship) {
            $r = $this->relationship;
            $payload['relationship'] = [
                'id' => $r->id,
                'family_id' => $r->family_id,
                'user_id' => $r->user_id,
                'related_user_id' => $r->related_user_id,
                'type' => $r->type,
            ];
        } else {
            $payload['relationship'] = null;
        }

        return $payload;
    }
}
