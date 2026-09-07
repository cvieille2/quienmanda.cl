<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\ProfileViewEvent;

class ProfileViewService
{
    public function record(Profile $profile, array $context = []): ProfileViewEvent
    {
        return ProfileViewEvent::create([
            'profile_id' => $profile->id,
            'session_id' => $context['session_id'] ?? null,
            'referrer' => $context['referrer'] ?? null,
            'created_at' => now(),
        ]);
    }
}
