<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\ProfileLink;
use Illuminate\Support\Str;

class ProfileDuplicateDetectionService
{
    public function findByUrl(string $normalizedUrl): ?Profile
    {
        return ProfileLink::query()
            ->where('normalized_url', $normalizedUrl)
            ->with('profile')
            ->first()
            ?->profile;
    }

    public function findByDisplayName(string $displayName): ?Profile
    {
        $name = trim($displayName);
        if ($name === '') {
            return null;
        }

        $slug = Str::slug($name);

        return Profile::query()
            ->whereRaw('lower(display_name) = ?', [mb_strtolower($name)])
            ->orWhere('slug', $slug)
            ->first();
    }

    public function detect(string $displayName, ?string $normalizedUrl = null): ?Profile
    {
        if ($normalizedUrl) {
            $byUrl = $this->findByUrl($normalizedUrl);
            if ($byUrl) {
                return $byUrl;
            }
        }

        return $this->findByDisplayName($displayName);
    }
}
