<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when income, expense, or transfer data changes for a family.
 * Clients should refetch authoritative totals from the API (or reload the dashboard).
 */
class FamilyFinancialDataChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $familyId,
        public string $reason = 'mutation'
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('family.'.$this->familyId)];
    }

    public function broadcastAs(): string
    {
        return 'financial.data.changed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'family_id' => $this->familyId,
            'reason' => $this->reason,
        ];
    }
}
