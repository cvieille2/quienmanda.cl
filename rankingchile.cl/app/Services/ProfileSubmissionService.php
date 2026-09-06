<?php

namespace App\Services;

use App\Enums\ProfileLinkType;
use App\Enums\ProfileSubmissionStatus;
use App\Models\ProfileCategory;
use App\Models\ProfileLink;
use App\Models\ProfileSubmission;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileSubmissionService
{
    public function __construct(
        private ProfileSourceDetectionService $sourceDetection,
        private ProfileDuplicateDetectionService $duplicates,
        private ProfileModerationService $moderation,
        private ProfileUrlNormalizer $normalizer,
    ) {}

    public function create(array $payload): ProfileSubmission
    {
        return DB::transaction(function () use ($payload) {
            $source = $this->sourceDetection->detect((string) Arr::get($payload, 'source_url'));
            $category = $this->resolveCategory((string) Arr::get($payload, 'category'));
            $duplicate = $this->duplicates->detect((string) Arr::get($payload, 'display_name'), $source['normalized_url']);

            $submission = ProfileSubmission::create([
                'display_name' => trim((string) Arr::get($payload, 'display_name')),
                'category' => $category->name,
                'profile_category_id' => $category->id,
                'source_type' => $source['source_type']->value,
                'source_url' => $source['original_url'],
                'normalized_url' => $source['normalized_url'],
                'detected_title' => $source['detected_title'],
                'submitted_by_session_id' => Arr::get($payload, 'submitted_by_session_id'),
                'submitted_email_hash' => $this->hashEmail(Arr::get($payload, 'submitted_email')),
                'duplicate_profile_id' => $duplicate?->id,
                'status' => $duplicate ? ProfileSubmissionStatus::Rejected : ProfileSubmissionStatus::Pending,
                'rejection_reason' => $duplicate ? 'duplicate_profile' : null,
                'reviewed_at' => $duplicate ? now() : null,
            ]);

            $this->storeLinks($submission, $payload, $source);

            if ($duplicate) {
                $submission->refresh();
                $this->moderation->rejectSubmission($submission, 'duplicate_profile');
            }

            return $submission->fresh(['profileCategory', 'links', 'duplicateProfile']);
        });
    }

    private function storeLinks(ProfileSubmission $submission, array $payload, array $source): void
    {
        $links = Arr::wrap(Arr::get($payload, 'links', []));
        if ($links === []) {
            $links = [$source['original_url']];
        }

        foreach (array_values($links) as $index => $link) {
            $url = is_array($link) ? (string) Arr::get($link, 'url', '') : (string) $link;
            $label = is_array($link) ? Arr::get($link, 'label') : null;
            if (trim($url) === '') {
                continue;
            }

            $normalized = $this->normalizer->normalize($url);
            ProfileLink::create([
                'profile_submission_id' => $submission->id,
                'link_type' => $index === 0 ? $source['link_type'] : ProfileLinkType::Reference,
                'label' => $label ?: ($index === 0 ? $source['label'] : null),
                'original_url' => $url,
                'normalized_url' => $normalized,
                'host' => (string) parse_url($normalized, PHP_URL_HOST),
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }

        $this->storeDestinationLink($submission, $payload);
    }

    private function storeDestinationLink(ProfileSubmission $submission, array $payload): void
    {
        $destinationUrl = trim((string) Arr::get($payload, 'destination_url'));
        if ($destinationUrl === '') {
            return;
        }

        $normalized = $this->normalizer->normalize($destinationUrl);
        ProfileLink::create([
            'profile_submission_id' => $submission->id,
            'link_type' => ProfileLinkType::Destination,
            'label' => 'Destino',
            'original_url' => $destinationUrl,
            'normalized_url' => $normalized,
            'host' => (string) parse_url($normalized, PHP_URL_HOST),
            'is_primary' => false,
            'sort_order' => 999,
        ]);
    }

    private function resolveCategory(string $categoryName): ProfileCategory
    {
        $name = trim($categoryName);
        if ($name === '') {
            throw new \InvalidArgumentException('La categoría es obligatoria.');
        }

        $slug = Str::slug($name);

        return ProfileCategory::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'is_active' => true]
        );
    }

    private function hashEmail(mixed $email): ?string
    {
        $value = trim((string) $email);
        if ($value === '') {
            return null;
        }

        return hash('sha256', mb_strtolower($value));
    }
}
