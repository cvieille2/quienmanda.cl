<?php

namespace App\Services;

use App\Enums\OutboundClickSource;
use App\Enums\ProfileLinkType;
use App\Enums\ProfileStatus;
use App\Models\OutboundClickEvent;
use App\Models\Profile;
use Illuminate\Support\Arr;

class OutboundClickService
{
    public function __construct(
        private RankingPeriodService $periods,
        private ProfileUrlNormalizer $normalizer,
    ) {}

    public function resolveDestinationUrl(Profile $profile): ?string
    {
        if ($profile->use_profile_as_destination) {
            return route('profile.show', $profile->slug);
        }

        $link = $profile->links()
            ->where('link_type', ProfileLinkType::Destination->value)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->first();

        if ($link) {
            return $link->original_url;
        }

        $website = $profile->links()
            ->whereIn('link_type', [ProfileLinkType::Website->value, ProfileLinkType::Source->value])
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->first();

        if ($website) {
            return $website->original_url;
        }

        return null;
    }

    public function recordClick(
        Profile $profile,
        string $destinationUrl,
        ?OutboundClickSource $source,
        array $context = [],
    ): OutboundClickEvent {
        $normalized = $this->normalizer->normalize($destinationUrl);
        $period = $this->periods->activePeriod();

        return OutboundClickEvent::create([
            'profile_id' => $profile->id,
            'ranking_period_id' => $period?->id,
            'source' => $source?->value,
            'destination_url' => $normalized,
            'session_id' => Arr::get($context, 'session_id'),
            'referrer' => Arr::get($context, 'referrer'),
            'utm_source' => Arr::get($context, 'utm_source'),
            'utm_medium' => Arr::get($context, 'utm_medium'),
            'utm_campaign' => Arr::get($context, 'utm_campaign'),
            'created_at' => now(),
        ]);
    }

    public function isTrackable(Profile $profile): bool
    {
        return $profile->status === ProfileStatus::Active
            && ($profile->use_profile_as_destination || $this->resolveDestinationUrl($profile) !== null);
    }
}
