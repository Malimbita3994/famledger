<?php

namespace App\Http\Controllers;

use App\Events\FamilyTimelineUpdated;
use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Family;
use App\Models\Milestone;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family)
    {
        $this->authorizeFamilyMember($family);
        $currentFamily = $family;

        $query = Milestone::where('family_id', $currentFamily->id)
            ->with(['user', 'reactions']);

        if (request('category')) {
            $query->where('category', request('category'));
        }

        if (request('year')) {
            $query->whereYear('date', request('year'));
        }

        $milestones = $query->orderBy('date', 'desc')->get();

        return view('families.timeline.index', compact('currentFamily', 'milestones'));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);
        $currentFamily = $family;

        return view('families.timeline.create', compact('currentFamily'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'media_url' => 'nullable|string',
            'category' => 'nullable|string|max:50',
        ]);

        $milestone = new Milestone($validated);
        $milestone->family_id = $family->id;
        $milestone->user_id = auth()->id();
        $milestone->save();

        broadcast(new FamilyTimelineUpdated($family->id, $milestone, 'created'));

        return redirect()
            ->route('families.timeline.index')
            ->with('success', __('Memory added to the family timeline!'));
    }

    public function show(Milestone $milestone, Family $family)
    {
        $this->authorizeFamilyMember($family);
        abort_if((int) $milestone->family_id !== (int) $family->id, 404);

        $currentFamily = $family;
        $milestone->loadMissing(['user', 'reactions']);

        return view('families.timeline.show', compact('currentFamily', 'milestone'));
    }

    public function edit(Milestone $milestone, Family $family)
    {
        $this->authorizeFamilyMember($family);
        abort_if((int) $milestone->family_id !== (int) $family->id, 404);
        abort_if((int) $milestone->user_id !== (int) auth()->id(), 403);

        $currentFamily = $family;

        return view('families.timeline.edit', compact('currentFamily', 'milestone'));
    }

    public function update(Request $request, Milestone $milestone, Family $family)
    {
        $this->authorizeFamilyMember($family);
        abort_if((int) $milestone->family_id !== (int) $family->id, 404);
        abort_if((int) $milestone->user_id !== (int) auth()->id(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'media_url' => 'nullable|string',
            'category' => 'nullable|string|max:50',
        ]);

        $milestone->update($validated);
        broadcast(new FamilyTimelineUpdated($family->id, $milestone->fresh(), 'updated'));

        return redirect()
            ->route('families.timeline.index')
            ->with('success', __('Memory updated successfully!'));
    }

    public function destroy(Milestone $milestone, Family $family)
    {
        $this->authorizeFamilyMember($family);

        abort_if((int) $milestone->family_id !== (int) $family->id, 404);

        $milestoneId = $milestone->id;
        $milestone->delete();

        broadcast(new FamilyTimelineUpdated($family->id, null, 'deleted', $milestoneId));

        return redirect()
            ->route('families.timeline.index')
            ->with('success', __('Memory removed from timeline.'));
    }
}
