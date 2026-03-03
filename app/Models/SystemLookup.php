<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLookup extends Model
{
    protected $fillable = [
        'group',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}

