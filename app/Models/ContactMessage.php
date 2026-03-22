<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'message', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function markAsRead(): void
    {
        if ($this->read_at !== null) {
            return;
        }
        $this->forceFill(['read_at' => now()])->save();
    }

    public function markAsUnread(): void
    {
        if ($this->read_at === null) {
            return;
        }
        $this->forceFill(['read_at' => null])->save();
    }
}
