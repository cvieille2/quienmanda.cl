<?php

namespace App\Models;

use App\Enums\ProfileSubmissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileSubmission extends Model
{
    protected $table = 'profile_submissions';

    protected $fillable = [
        'display_name',
        'category',
        'profile_category_id',
        'profile_id',
        'source_type',
        'source_url',
        'normalized_url',
        'detected_title',
        'profile_image_url',
        'featured_media_url',
        'submitted_by_session_id',
        'submitted_email_hash',
        'duplicate_profile_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => ProfileSubmissionStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_PENDING = ProfileSubmissionStatus::Pending->value;
    public const STATUS_APPROVED = ProfileSubmissionStatus::Approved->value;
    public const STATUS_REJECTED = ProfileSubmissionStatus::Rejected->value;

    public function profileCategory(): BelongsTo
    {
        return $this->belongsTo(ProfileCategory::class, 'profile_category_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function duplicateProfile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'duplicate_profile_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(ProfileLink::class, 'profile_submission_id');
    }
}
