<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyRole;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FamilyController extends Controller
{
    /**
     * Families the current user belongs to.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole(['Super Admin', 'super-admin'])) {
            // Platform owners can see all families
            $families = Family::query()
                ->with('creator:id,name', 'familyMembers.role')
                ->withCount('familyMembers')
                ->orderByDesc('created_at')
                ->get();
        } else {
            // Regular users only see families they belong to
            $families = $user
                ->families()
                ->distinct()
                ->with('creator:id,name', 'familyMembers.role')
                ->withCount('familyMembers')
                ->orderByPivot('created_at', 'desc')
                ->get();
        }

        return view('families.index', compact('families'));
    }

    /**
     * Show create family form.
     */
    public function create()
    {
        $currencies = Arr::except(config('currencies', []), ['default']);

        return view('families.create', compact('currencies'));
    }

    /**
     * Store a new family. Creator MUST become first member (Owner).
     *
     * Flow: User creates family → System creates family → System adds user to family_user
     *       with role=Owner, is_primary=true, status=active.
     *
     * Required: name, currency_code, timezone. Optional: country, description.
     * System: created_by, status=active, and first membership (Owner, primary).
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->families()->exists()) {
            return redirect()
                ->route('families.index')
                ->with('error', 'You can only belong to one family. Leave your existing family before creating a new one.');
        }

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'currency_code' => ['required', 'string', 'size:3'],
            'timezone'      => ['required', 'string', 'max:50'],
            'country'       => ['nullable', 'string', 'max:100'],
        ]);

        $ownerRole = FamilyRole::where('name', 'Owner')->firstOrFail();

        $family = DB::transaction(function () use ($validated, $user, $ownerRole) {
            // 1. Create family
            $family = Family::create([
                'name'          => $validated['name'],
                'description'   => $validated['description'] ?? null,
                'currency_code' => strtoupper($validated['currency_code']),
                'timezone'      => $validated['timezone'],
                'country'       => $validated['country'] ?? null,
                'created_by'    => $user->id,
                'status'        => 'active',
            ]);

            // 2. Add creator as first member (Owner) — guarantees every family has an owner
            FamilyMember::create([
                'family_id'  => $family->id,
                'user_id'    => $user->id,
                'role_id'    => $ownerRole->id,
                'is_primary' => true,
                'status'     => 'active',
                'joined_at'  => now(),
            ]);

            // 3. Create a default main wallet for this family
            $family->wallets()->create([
                'name'            => 'Main account',
                'type'            => 'cash',
                'currency_code'   => $family->currency_code,
                'description'     => 'Primary family wallet (central account).',
                'initial_balance' => 0,
                'is_primary'      => true,
                'is_shared'       => true,
                'status'          => 'active',
                'created_by'      => $user->id,
            ]);

            return $family;
        });

        session()->put('current_family_id', $family->id);

        return redirect()->route('families.index')->with('success', 'Family created successfully.');
    }

    /**
     * Show single family (detail).
     */
    public function show(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $family->load('creator:id,name', 'familyMembers.user:id,name,email', 'familyMembers.role');

        $currentMembership = FamilyMember::where('family_id', $family->id)
            ->where('user_id', auth()->id())
            ->with('role')
            ->first();
        $canManageMembers = $currentMembership && in_array($currentMembership->role->name ?? '', ['Owner', 'Co-owner'], true);

        return view('families.show', compact('family', 'canManageMembers'));
    }

    /**
     * Show edit family form.
     */
    public function edit(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $currencies = Arr::except(config('currencies', []), ['default']);
        if ($family->currency_code && ! isset($currencies[$family->currency_code])) {
            $currencies = [$family->currency_code => $family->currency_code] + $currencies;
        }

        return view('families.edit', compact('family', 'currencies'));
    }

    /**
     * Update family.
     */
    public function update(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);
        $this->authorizeManageFamilySettings($family);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'currency_code' => ['required', 'string', 'size:3'],
            'timezone'      => ['required', 'string', 'max:50'],
            'country'       => ['nullable', 'string', 'max:100'],
            'status'       => ['required', Rule::in(['active', 'archived'])],
        ]);

        $family->update([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'currency_code' => strtoupper($validated['currency_code']),
            'timezone'      => $validated['timezone'],
            'country'       => $validated['country'] ?? null,
            'status'       => $validated['status'],
        ]);

        return redirect()->route('families.index')->with('success', 'Family updated successfully.');
    }

    /**
     * Delete family.
     */
    public function destroy(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $family->delete();

        return redirect()->route('families.index')->with('success', 'Family deleted.');
    }

    /**
     * Ensure current user is a member of the family.
     */
    protected function authorizeFamilyMember(Family $family): void
    {
        $user = auth()->user();

        if ($user && $user->hasRole(['Super Admin', 'super-admin'])) {
            // Super administrators can access any family
            return;
        }

        if (! $family->members()->where('user_id', $user?->id)->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    /**
     * Ensure only owners / co-owners can change core family settings (including currency).
     */
    protected function authorizeManageFamilySettings(Family $family): void
    {
        $membership = FamilyMember::where('family_id', $family->id)
            ->where('user_id', auth()->id())
            ->with('role')
            ->first();

        if (! $membership || ! in_array($membership->role->name ?? '', ['Owner', 'Co-Owner', 'Co-owner'], true)) {
            abort(403, 'Only the owner or co-owner can update family settings.');
        }
    }
}
