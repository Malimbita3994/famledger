<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FamilyProfileUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $familyId;

    public $healthIndex;

    public $leaderboard;

    /** @var array{total_income: float, total_expenses: float, net_flow: float, wallet_balance_total: float} */
    public $ledgerTotals;

    /**
     * Create a new event instance.
     */
    public function __construct(int $familyId, array $healthIndex, array $leaderboard = [], array $ledgerTotals = [])
    {
        $this->familyId = $familyId;
        $this->healthIndex = $healthIndex;
        $this->leaderboard = $leaderboard;
        $this->ledgerTotals = $ledgerTotals;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('family.'.$this->familyId),
        ];
    }

    /**
     * Jina la event litakalosikilizwa kwenye frontend.
     */
    public function broadcastAs(): string
    {
        return 'profile.updated';
    }

    /**
     * Data itakayotumwa kwa frontend.
     */
    public function broadcastWith(): array
    {
        return [
            'health_index' => $this->healthIndex,
            'leaderboard' => $this->leaderboard,
            'ledger_totals' => $this->ledgerTotals,
        ];
    }
}
