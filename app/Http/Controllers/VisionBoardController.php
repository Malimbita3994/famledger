<?php

namespace App\Http\Controllers;

use App\Events\FamilyGoalUpdated;
use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Family;
use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VisionBoardController extends Controller
{
    use AuthorizesFamilyMember;

    protected function goalForFamily(Family $family, Goal $goal): Goal
    {
        if ((int) $goal->family_id !== (int) $family->id) {
            abort(404);
        }

        return $goal;
    }

    public function index(Family $family)
    {
        $this->authorizeFamilyMember($family);
        $currentFamily = $family;

        $goals = Goal::where('family_id', $currentFamily->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('families.goals.index', compact('currentFamily', 'goals'));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);
        $currentFamily = $family;

        return view('families.goals.create', compact('currentFamily'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $progressInput = $request->input('progress');
        if ($progressInput === '' || $progressInput === null) {
            $request->merge(['progress' => 0]);
        }

        if ($request->hasFile('image')) {
            $request->merge(['image_url' => null]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'image_url' => 'nullable|url|max:2048',
            'target_date' => 'nullable|date',
            'category' => 'nullable|string|max:50',
            'status' => 'required|in:draft,active,completed',
            'progress' => 'nullable|integer|min:0|max:100',
            'step_lines' => 'nullable|string|max:20000',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('goals/'.$family->id, 'public');
            $validated['image_url'] = Storage::disk('public')->url($path);
        }

        unset($validated['image']);

        $stepLines = $validated['step_lines'] ?? '';
        unset($validated['step_lines']);

        $steps = [];
        if (is_string($stepLines) && trim($stepLines) !== '') {
            foreach (preg_split('/\r\n|\r|\n/', $stepLines) as $line) {
                $t = trim($line);
                if ($t !== '') {
                    $steps[] = ['title' => $t, 'completed' => false];
                }
            }
        }

        $validated['steps'] = $steps;
        $validated['progress'] = (int) ($validated['progress'] ?? 0);

        $goal = new Goal($validated);
        $goal->family_id = $family->id;
        $goal->save();

        broadcast(new FamilyGoalUpdated($family->id, $goal, 'created'));

        return redirect()
            ->route('families.goals.create')
            ->with('success', __('Goal successfully added to your vision board!'));
    }

    public function show(Goal $goal, Family $family): View
    {
        $this->authorizeFamilyMember($family);
        $goal = $this->goalForFamily($family, $goal);
        $currentFamily = $family;

        return view('families.goals.show', compact('currentFamily', 'goal'));
    }

    public function edit(Goal $goal, Family $family): View
    {
        $this->authorizeFamilyMember($family);
        $goal = $this->goalForFamily($family, $goal);
        $currentFamily = $family;

        $stepLines = '';
        if (is_array($goal->steps)) {
            foreach ($goal->steps as $row) {
                $t = is_array($row) ? ($row['title'] ?? '') : (string) $row;
                $t = trim($t);
                if ($t !== '') {
                    $stepLines .= ($stepLines !== '' ? "\n" : '').$t;
                }
            }
        }

        return view('families.goals.edit', compact('currentFamily', 'goal', 'stepLines'));
    }

    public function update(Request $request, Goal $goal, Family $family): RedirectResponse
    {
        $this->authorizeFamilyMember($family);
        $goal = $this->goalForFamily($family, $goal);

        $progressInput = $request->input('progress');
        if ($progressInput === '' || $progressInput === null) {
            $request->merge(['progress' => 0]);
        }

        if ($request->hasFile('image')) {
            $request->merge(['image_url' => null]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'image_url' => 'nullable|url|max:2048',
            'target_date' => 'nullable|date',
            'category' => 'nullable|string|max:50',
            'status' => 'required|in:draft,active,completed',
            'progress' => 'nullable|integer|min:0|max:100',
            'step_lines' => 'nullable|string|max:20000',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('goals/'.$family->id, 'public');
            $validated['image_url'] = Storage::disk('public')->url($path);
        }

        unset($validated['image']);

        $stepLines = $validated['step_lines'] ?? '';
        unset($validated['step_lines']);

        $steps = [];
        if (is_string($stepLines) && trim($stepLines) !== '') {
            foreach (preg_split('/\r\n|\r|\n/', $stepLines) as $line) {
                $t = trim($line);
                if ($t !== '') {
                    $steps[] = ['title' => $t, 'completed' => false];
                }
            }
        }

        $validated['steps'] = $steps;
        $validated['progress'] = (int) ($validated['progress'] ?? 0);

        $goal->fill($validated);
        $goal->save();

        broadcast(new FamilyGoalUpdated($family->id, $goal->fresh(), 'updated'));

        return redirect()
            ->route('families.goals.show', $goal)
            ->with('success', __('Vision goal updated.'));
    }
}
