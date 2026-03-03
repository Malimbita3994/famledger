<?php

namespace Database\Seeders;

use App\Models\FamilyRole;
use Illuminate\Database\Seeder;

class FamilyRoleSeeder extends Seeder
{
    /**
     * Default family-level roles (Owner, Co-owner, Member, Viewer, Child).
     * Used for family membership (family_user.role_id).
     */
    public function run(): void
    {
        // Normalize legacy name
        FamilyRole::where('name', 'Co-Owner')->update(['name' => 'Co-owner']);

        // Family membership roles (used in family_user.role_id)
        $roles = [
            ['name' => 'Owner',    'description' => 'Full control; can delete family and manage all members', 'is_system' => true],
            ['name' => 'Co-owner', 'description' => 'Can manage members and settings; cannot delete family',  'is_system' => true],
            ['name' => 'Member',   'description' => 'Can add and edit transactions, budgets, and accounts',  'is_system' => true],
            ['name' => 'Viewer',   'description' => 'Read-only access to family finance data',               'is_system' => true],
            ['name' => 'Child',    'description' => 'Limited access; view and limited edit as configured',   'is_system' => true],
        ];

        foreach ($roles as $role) {
            FamilyRole::firstOrCreate(
                ['name' => $role['name']],
                [
                    'description' => $role['description'],
                    'is_system'   => $role['is_system'],
                ]
            );
        }
    }
}
