<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'document_type',
        'original_name',
        'path',
        'size',
        'mime_type',
        'is_archived',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_archived' => 'bool',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

