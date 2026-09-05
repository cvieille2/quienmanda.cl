<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\ProfileClaim;
use App\Models\ProfileReport;
use App\Models\ProfileSubmission;

class ModerationService
{
    /** Sanea un nombre de supporter (evita inyección / abuso visibilidad pública). */
    public function sanitizeName(?string $name): ?string
    {
        if (! $name || trim($name) === '') return null;
        $cleaned = trim(strip_tags($name));
        $cleaned = mb_substr($cleaned, 0, 64);
        return $cleaned === '' ? null : $cleaned;
    }

    public function approveSubmission(ProfileSubmission $submission, ?int $adminId = null): void
    {
        if ($submission->status !== ProfileSubmission::STATUS_PENDING) {
            return;
        }
        $submission->update([
            'status'      => ProfileSubmission::STATUS_APPROVED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        audit('profile_created', 'profile_submission', $submission->id, [], 'admin', $adminId);
    }

    public function rejectSubmission(ProfileSubmission $submission, string $reason, ?int $adminId = null): void
    {
        if ($submission->status !== ProfileSubmission::STATUS_PENDING) {
            return;
        }
        $submission->update([
            'status'          => ProfileSubmission::STATUS_REJECTED,
            'rejection_reason'=> $reason,
            'reviewed_by'     => $adminId,
            'reviewed_at'     => now(),
        ]);
        audit('profile_rejected', 'profile_submission', $submission->id, ['reason' => $reason], 'admin', $adminId);
    }

    public function resolveReport(ProfileReport $report, string $resolution, ?int $adminId = null): void
    {
        if ($report->status !== ProfileReport::STATUS_OPEN) {
            return;
        }
        $report->update([
            'status'      => ProfileReport::STATUS_RESOLVED,
            'resolution'  => $resolution,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        audit('profile_report_resolved', 'profile_report', $report->id, [], 'admin', $adminId);
    }

    public function verifyClaim(ProfileClaim $claim, ?int $adminId = null): void
    {
        if ($claim->status !== ProfileClaim::STATUS_PENDING) {
            return;
        }
        $claim->update([
            'status'      => ProfileClaim::STATUS_VERIFIED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        // Claim verificado -> el perfil queda verificado sin borrar historial.
        $claim->profile?->update(['verification_status' => Profile::VERIFICATION_VERIFIED]);
        audit('profile_verified', 'profile_claim', $claim->id, [], 'admin', $adminId);
    }
}
