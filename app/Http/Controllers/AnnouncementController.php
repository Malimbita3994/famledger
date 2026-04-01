<?php

namespace App\Http\Controllers;

use App\Events\FamilyAnnouncementUpdated;
use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Announcement;
use App\Models\Family;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family)
    {
        $this->authorizeFamilyMember($family);
        $currentFamily = $family;

        $announcements = Announcement::where('family_id', $currentFamily->id)
            ->with(['user', 'reactions'])
            ->orderBy('pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('families.announcements.index', compact('currentFamily', 'announcements'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'message' => 'required|string',
            'pinned' => 'sometimes|boolean',
        ]);

        $announcement = new Announcement($validated);
        $announcement->family_id = $family->id;
        $announcement->user_id = auth()->id();
        $announcement->pinned = $request->boolean('pinned');
        $announcement->save();

        broadcast(new FamilyAnnouncementUpdated($family->id, $announcement, 'created'));

        return redirect()
            ->route('families.announcements.index')
            ->with('success', __('Announcement posted successfully!'));
    }

    public function togglePin(Family $family, Announcement $announcement)
    {
        $this->authorizeFamilyMember($family);

        $announcement->pinned = ! $announcement->pinned;
        $announcement->save();

        broadcast(new FamilyAnnouncementUpdated($family->id, $announcement, 'updated'));

        return back()->with('success', $announcement->pinned ? __('Announcement pinned.') : __('Announcement unpinned.'));
    }

    public function destroy(Family $family, Announcement $announcement)
    {
        $this->authorizeFamilyMember($family);

        $announcementId = $announcement->id;
        $announcement->delete();

        broadcast(new FamilyAnnouncementUpdated($family->id, null, 'deleted', $announcementId));

        return redirect()
            ->route('families.announcements.index')
            ->with('success', __('Announcement deleted.'));
    }
}
