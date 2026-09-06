<?php

namespace App\Services;

use App\Models\ProfileSubmission;

class ProfileModerationService
{
    public function __construct(private ModerationService $moderation) {}

    public function approveSubmission(ProfileSubmission $submission, ?int $adminId = null): void
    {
        $this->moderation->approveSubmission($submission, $adminId);
    }

    public function rejectSubmission(ProfileSubmission $submission, string $reason, ?int $adminId = null): void
    {
        $this->moderation->rejectSubmission($submission, $reason, $adminId);
    }
}
