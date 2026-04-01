<?php

namespace App\Http\Controllers;

use App\Events\FamilyTreeUpdated;
use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Family;
use App\Models\FamilyRelationship;
use App\Services\FamilyTreeBuilder;
use Illuminate\Http\Request;

class FamilyTreeController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family, FamilyTreeBuilder $treeBuilder)
    {
        $this->authorizeFamilyMember($family);
        $currentFamily = $family;

        $members = $family->familyMembers()->with('user')->get();
        $relationships = FamilyRelationship::where('family_id', $family->id)
            ->with(['user', 'relatedUser'])
            ->get();

        $tree = $treeBuilder->buildRoots($members, $relationships);

        return view('families.tree.index', compact('currentFamily', 'members', 'relationships', 'tree'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);
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

        if ($request->expectsJson()) {
            return response()->json($relationship, 201);
        }

        return redirect()
            ->route('families.tree.index')
            ->with('success', __('Relationship saved.'));
    }

    public function destroy(Request $request, Family $family, FamilyRelationship $relationship)
    {
        $this->authorizeFamilyMember($family);
        $this->authorize('update', $family);

        abort_if((int) $relationship->family_id !== (int) $family->id, 404);

        $relationshipId = $relationship->id;
        $relationship->delete();

        broadcast(new FamilyTreeUpdated($family->id, null, 'deleted', $relationshipId));

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['message' => 'Deleted']);
        }

        return redirect()
            ->route('families.tree.index')
            ->with('success', __('Relationship removed.'));
    }
}
