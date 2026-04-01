<?php

namespace App\Events;

use App\Models\Announcement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FamilyAnnouncementUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $familyId,
        public ?Announcement $announcement = null,
        public string $action = 'created',
        public ?int $announcementId = null
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('family.'.$this->familyId)];
    }

    public function broadcastAs(): string
    {
        return 'announcement.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'announcement_id' => $this->announcementId ?? $this->announcement?->id,
            'announcement' => $this->announcement ? [
                'id' => $this->announcement->id,
                'message' => $this->announcement->message,
                'pinned' => $this->announcement->pinned,
                'user_name' => $this->announcement->user->name ?? 'Family Member',
                'created_at_human' => $this->announcement->created_at->diffForHumans(),
            ] : null,
        ];
    }
}
