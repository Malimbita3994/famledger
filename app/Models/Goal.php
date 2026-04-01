<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    protected $fillable = [
        'family_id',
        'title',
        'description',
        'image_url',
        'steps',
        'status',
        'progress',
        'created_by',
        'target_date',
        'category',
    ];

    protected $casts = [
        'steps' => 'json',
        'target_date' => 'date',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(GoalComment::class);
    }

    /**
     * Root-relative /storage/... when the stored value points at this app's public disk, so images load
     * on the current host and port even when APP_URL in .env does not match the browser URL.
     */
    protected function resolvedImageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $url = $this->attributes['image_url'] ?? null;
            if ($url === null || $url === '') {
                return null;
            }
            $url = (string) $url;
            if (preg_match('#^https?://#i', $url)) {
                $path = parse_url($url, PHP_URL_PATH) ?: '';
                if (str_starts_with($path, '/storage/')) {
                    return $path;
                }

                return $url;
            }

            return '/storage/'.ltrim($url, '/');
        });
    }
}
