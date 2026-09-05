<?php

namespace App\Models;

use App\Enums\ProfileSubmissionStatus;
use Illuminate\Database\Eloquent\Model;

class ProfileSubmission extends Model
{
    protected $table = 'profile_submissions';

    protected $fillable = [
        'display_name', 'category', 'profile_image_url', 'featured_media_url',
        'submitted_by_session_id', 'submitted_email_hash', 'status',
        'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected $casts = [
        'status'      => ProfileSubmissionStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_PENDING  = ProfileSubmissionStatus::Pending->value;
    public const STATUS_APPROVED = ProfileSubmissionStatus::Approved->value;
    public const STATUS_REJECTED = ProfileSubmissionStatus::Rejected->value;
}
