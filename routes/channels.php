<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('csv-progress.user.{userId}', function (User $user, $userId) {
    Log::info('Broadcasting to csv-progress.user channel', [
        'userId' => $userId,
        'currentUserId' => $user->id,
    ]);
    return true;
});


