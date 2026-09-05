<?php

namespace App\Models;

use App\Enums\ProfileStatus;
use App\Enums\ProfileType;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Profile extends Model
{
    use SoftDeletes;

    protected $table = 'profiles';

    protected $fillable = [
        'public_id', 'display_name', 'slug', 'category', 'type', 'status', 'verification_status',
        'is_community_created', 'profile_image_url', 'featured_media_url',
    ];

    protected $casts = [
        'is_community_created' => 'boolean',
        'type'                => ProfileType::class,
        'status'              => ProfileStatus::class,
        'verification_status' => VerificationStatus::class,
    ];

    public const STATUS_PENDING_REVIEW = ProfileStatus::PendingReview->value;
    public const STATUS_ACTIVE         = ProfileStatus::Active->value;
    public const STATUS_SUSPENDED      = ProfileStatus::Suspended->value;
    public const STATUS_ARCHIVED       = ProfileStatus::Archived->value;
    public const STATUS_REJECTED       = ProfileStatus::Rejected->value;

    public const VERIFICATION_UNVERIFIED = VerificationStatus::Unverified->value;
    public const VERIFICATION_PENDING    = VerificationStatus::Pending->value;
    public const VERIFICATION_VERIFIED   = VerificationStatus::Verified->value;

    public function transactions(): HasMany
    {
        return $this->hasMany(SupportTransaction::class, 'profile_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(RankingSnapshot::class, 'profile_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Profile $profile) {
            if (! $profile->public_id) {
                $profile->public_id = (string) Str::ulid();
            }
        });
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ProfileReport::class, 'profile_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ProfileClaim::class, 'profile_id');
    }

    public function shareEvents(): HasMany
    {
        return $this->hasMany(ShareEvent::class, 'profile_id');
    }

    public function badge(): Attribute
    {
        return Attribute::get(fn () => match (true) {
            $this->verification_status === VerificationStatus::Verified => 'verified',
            $this->is_community_created                                  => 'community',
            default                                                     => 'none',
        });
    }
}
