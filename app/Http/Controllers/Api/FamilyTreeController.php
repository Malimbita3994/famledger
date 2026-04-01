<?php

namespace App\Http\Controllers\Api;

use App\Events\FamilyTreeUpdated;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyRelationship;
use App\Services\FamilyTreeBuilder;
use Illuminate\Http\Request;

class FamilyTreeController extends Controller
{
    public function index(Family $family, FamilyTreeBuilder $treeBuilder)
    {
        $this->authorize('view', $family);

        $members = $family->familyMembers()->with('user')->get();

        $relationships = FamilyRelationship::where('family_id', $family->id)
            ->with(['user', 'relatedUser'])
            ->get();

        $tree = $treeBuilder->buildRoots($members, $relationships);

        return response()->json([
            'members' => $members->map(function ($member) {
                return [
                    'id' => $member->user_id,
                    'name' => $member->user->name,
                    'avatar' => $member->user->avatar_url,
                    'role' => $member->role->name ?? 'Member',
                    'engagement_score' => $member->engagement_score,
                    'budget_status' => 'success',
                ];
            }),
            'relationships' => $relationships->map(function ($rel) {
                return [
                    'id' => $rel->id,
                    'from' => $rel->user_id,
                    'to' => $rel->related_user_id,
                    'type' => $rel->type,
                ];
            }),
            'tree' => $tree,
        ]);
    }

    public function store(Request $request, Family $family)
    {
        $this->authorize('update', $family);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'related_user_id' => 'required|exists:users,id|different:user_id',
            'type' => 'required|in:parent,child,spouse,sibling',
        ]);

        $memberIds = $family->familyMembers()->pluck('user_id');
        if (! $memberIds->contains($data['user_id']) || ! $memberIds->contains($data['related_user_id'])) {
            return response()->json(['error' => 'Both users must be family members'], 422);
        }

        $relationship = FamilyRelationship::create(array_merge($data, ['family_id' => $family->id]));

        broadcast(new FamilyTreeUpdated($family->id, $relationship));

        return response()->json($relationship, 201);
    }

    public function destroy(Family $family, FamilyRelationship $relationship)
    {
        $this->authorize('update', $family);

        abort_if((int) $relationship->family_id !== (int) $family->id, 404);

        $relationshipId = $relationship->id;
        $relationship->delete();

        broadcast(new FamilyTreeUpdated($family->id, null, 'deleted', $relationshipId));

        return response()->json(['message' => 'Deleted']);
    }
}
