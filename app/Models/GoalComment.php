<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalComment extends Model
{
    protected $fillable = ['goal_id', 'user_id', 'comment'];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
