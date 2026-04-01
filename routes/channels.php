<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('family.{familyId}', function ($user, $familyId) {
    return $user->families()->where('families.id', (int) $familyId)->exists();
});
