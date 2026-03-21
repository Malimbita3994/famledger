<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPageContent extends Model
{
    public const SINGLETON_ID = 1;

    protected $table = 'notification_page_contents';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'dnd_intro',
        'dnd_learn_more_url',
        'dnd_learn_more_label',
    ];

    public static function singleton(): self
    {
        return static::firstOrCreate(
            ['id' => self::SINGLETON_ID],
            [
                'dnd_intro' => '',
                'dnd_learn_more_url' => null,
                'dnd_learn_more_label' => null,
            ]
        );
    }
}
