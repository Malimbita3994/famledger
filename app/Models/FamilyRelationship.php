<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyRelationship extends Model
{
    protected $fillable = ['family_id', 'user_id', 'related_user_id', 'type'];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }
}
