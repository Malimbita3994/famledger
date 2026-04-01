<?php

namespace App\Events;

use App\Models\Goal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FamilyGoalUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $familyId;

    public $goal;

    public $action; // 'created', 'updated', 'step_completed', 'completed'

    public function __construct($familyId, Goal $goal, string $action = 'updated')
    {
        $this->familyId = $familyId;
        $this->goal = $goal;
        $this->action = $action;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('family.'.$this->familyId);
    }

    public function broadcastWith()
    {
        return [
            'action' => $this->action,
            'goal' => [
                'id' => $this->goal->id,
                'title' => $this->goal->title,
                'progress' => $this->goal->progress,
                'status' => $this->goal->status,
                'steps' => $this->goal->steps,
            ],
        ];
    }
}
