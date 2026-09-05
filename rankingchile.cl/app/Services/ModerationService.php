<?php

namespace App\Services;

use App\Enums\ProfileClaimStatus;
use App\Enums\ProfileReportStatus;
use App\Enums\ProfileSubmissionStatus;
use App\Enums\ProfileStatus;
use App\Enums\VerificationStatus;
use App\Models\Profile;
use App\Models\ProfileClaim;
use App\Models\ProfileReport;
use App\Models\ProfileSubmission;

class ModerationService
{
    public function sanitizeName(?string $name): ?string
    {
        if (! $name || trim($name) === '') {
            return null;
        }

        $cleaned = trim(strip_tags($name));
        $cleaned = mb_substr($cleaned, 0, 64);

        return $cleaned === '' ? null : $cleaned;
    }

    public function approveSubmission(ProfileSubmission $submission, ?int $adminId = null): void
    {
        if ($submission->status !== ProfileSubmissionStatus::Pending) {
            return;
        }

        $submission->update([
            'status'      => ProfileSubmissionStatus::Approved,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        audit('profile_created', 'profile_submission', $submission->id, [], 'admin', $adminId);
    }

    public function rejectSubmission(ProfileSubmission $submission, string $reason, ?int $adminId = null): void
    {
        if ($submission->status !== ProfileSubmissionStatus::Pending) {
            return;
        }

        $submission->update([
            'status'           => ProfileSubmissionStatus::Rejected,
            'rejection_reason'  => $reason,
            'reviewed_by'      => $adminId,
            'reviewed_at'      => now(),
        ]);
        audit('profile_rejected', 'profile_submission', $submission->id, ['reason' => $reason], 'admin', $adminId);
    }

    public function resolveReport(ProfileReport $report, string $resolution, ?int $adminId = null): void
    {
        if ($report->status !== ProfileReportStatus::Open) {
            return;
        }

        $report->update([
            'status'      => ProfileReportStatus::Resolved,
            'resolution'  => $resolution,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        audit('profile_report_resolved', 'profile_report', $report->id, [], 'admin', $adminId);
    }

    public function verifyClaim(ProfileClaim $claim, ?int $adminId = null): void
    {
        if ($claim->status !== ProfileClaimStatus::Pending) {
            return;
        }

        $claim->update([
            'status'      => ProfileClaimStatus::Verified,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        $claim->profile?->update(['verification_status' => VerificationStatus::Verified]);
        audit('profile_verified', 'profile_claim', $claim->id, [], 'admin', $adminId);
    }
}
