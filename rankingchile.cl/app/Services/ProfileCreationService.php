<?php

namespace App\Services;

use App\Enums\ProfileStatus;
use App\Enums\ProfileType;
use App\Enums\VerificationStatus;
use App\Models\Profile;
use App\Models\ProfileLink;
use App\Models\ProfileSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileCreationService
{
    public function __construct(private ProfileModerationService $moderation) {}

    public function createFromSubmission(ProfileSubmission $submission): Profile
    {
        return DB::transaction(function () use ($submission) {
            $locked = ProfileSubmission::query()->whereKey($submission->id)->lockForUpdate()->firstOrFail();
            $locked->loadMissing(['profileCategory', 'links', 'profile']);

            if ($locked->profile) {
                return $locked->profile;
            }

            if ($locked->duplicate_profile_id) {
                throw new \RuntimeException('La solicitud es un duplicado y no puede publicarse.');
            }

            $profile = Profile::create([
                'display_name' => $locked->display_name,
                'slug' => $this->uniqueSlug($locked->display_name),
                'category' => $locked->category,
                'profile_category_id' => $locked->profile_category_id,
                'region_id' => $locked->region_id,
                'profile_submission_id' => $locked->id,
                'source_type' => $locked->source_type,
                'source_url' => $locked->source_url,
                'onboarding_normalized_url' => $locked->normalized_url,
                'onboarding_detected_title' => $locked->detected_title,
                'status' => ProfileStatus::Active,
                'type' => ProfileType::Community,
                'verification_status' => VerificationStatus::Unverified,
                'is_community_created' => true,
                'use_profile_as_destination' => $locked->use_profile_as_destination,
            ]);

            ProfileLink::query()
                ->where('profile_submission_id', $locked->id)
                ->update(['profile_id' => $profile->id]);

            $locked->update(['profile_id' => $profile->id]);
            $this->moderation->approveSubmission($locked);

            return $profile->fresh(['profileCategory', 'links', 'submission']);
        });
    }

    private function uniqueSlug(string $displayName): string
    {
        $base = Str::slug($displayName);
        $slug = $base;
        $suffix = 1;

        while (Profile::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
