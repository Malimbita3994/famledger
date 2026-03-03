<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyRole extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'role_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(FamilyInvitation::class, 'role_id');
    }
}
