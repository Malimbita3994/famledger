<?php

namespace App\Services;

use App\Events\FamilyGoalUpdated;
use App\Events\FamilyProfileUpdated;
use App\Models\Family;
use App\Models\Goal;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GoalService
{
    public function createGoal(Family $family, array $data, User $creator): Goal
    {
        $data['family_id'] = $family->id;
        $data['created_by'] = $creator->id;
        $data['progress'] = 0;
        $data['steps'] = $data['steps'] ?? [];
        $data['status'] = $data['status'] ?? 'active';

        $goal = Goal::create($data);

        $this->broadcastUpdate($family, $goal, 'created');

        return $goal;
    }

    public function updateSteps(Goal $goal, array $steps): Goal
    {
        $goal->steps = $steps;
        $goal->progress = $this->calculateProgress($steps);

        $isNewlyCompleted = $goal->progress === 100 && $goal->status !== 'completed';

        if ($isNewlyCompleted) {
            $goal->status = 'completed';
        }

        $goal->save();

        $this->logEngagement(auth()->user(), $goal->family, 'goal_step', 5);

        if ($isNewlyCompleted) {
            $this->completeGoal($goal, auth()->user());
        } else {
            $this->broadcastUpdate($goal->family, $goal, 'step_completed');
        }

        return $goal;
    }

    public function completeGoal(Goal $goal, User $completer): void
    {
        $goal->status = 'completed';
        $goal->progress = 100;
        $goal->save();

        // Log engagement for finishing
        $this->logEngagement($completer, $goal->family, 'goal_completed', 20);

        // Create Milestone
        Milestone::create([
            'family_id' => $goal->family_id,
            'user_id' => $completer->id,
            'title' => 'Goal Completed: '.$goal->title,
            'description' => 'The family achieved the goal: '.$goal->title,
            'date' => now(),
            'type' => 'achievement',
        ]);

        $this->broadcastUpdate($goal->family, $goal, 'completed');
    }

    private function calculateProgress(array $steps): int
    {
        if (empty($steps)) {
            return 0;
        }

        $total = count($steps);
        $completed = collect($steps)->filter(fn ($s) => isset($s['completed']) && $s['completed'])->count();

        return (int) (($completed / $total) * 100);
    }

    private function logEngagement(User $user, Family $family, string $type, int $points): void
    {
        $user->engagementActivities()->create([
            'family_id' => $family->id,
            'type' => $type,
            'points' => $points,
        ]);

        // Increment pivot score
        DB::table('family_user')
            ->where('family_id', $family->id)
            ->where('user_id', $user->id)
            ->increment('engagement_score', $points);

        // Trigger profile update for leaderboard and financial tiles
        $finance = app(FamilyFinancialService::class);
        broadcast(new FamilyProfileUpdated(
            $family->id,
            $finance->getFamilyHealthIndex($family->id),
            $finance->getContributionLeaderboard($family->id),
            $finance->getProfileLedgerSummary($family->id),
        ));
    }

    private function broadcastUpdate(Family $family, Goal $goal, string $action): void
    {
        broadcast(new FamilyGoalUpdated($family->id, $goal, $action));
    }
}
