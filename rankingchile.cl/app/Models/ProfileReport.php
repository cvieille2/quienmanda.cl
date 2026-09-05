<?php

namespace App\Models;

use App\Enums\ProfileReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileReport extends Model
{
    protected $table = 'profile_reports';

    protected $fillable = [
        'profile_id', 'reason', 'description', 'reporter_email_hash', 'status',
        'reviewed_by', 'reviewed_at', 'resolution',
    ];

    protected $casts = [
        'status'      => ProfileReportStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public const REASON_IMPERSONATION       = 'impersonation';
    public const REASON_PRIVATE_PERSON      = 'private_person';
    public const REASON_MINOR               = 'minor';
    public const REASON_HARASSMENT          = 'harassment';
    public const REASON_INCORRECT_INFORMATION = 'incorrect_information';
    public const REASON_COPYRIGHT           = 'copyright';
    public const REASON_IMAGE_RIGHTS        = 'image_rights';
    public const REASON_MALICIOUS_LINK      = 'malicious_link';
    public const REASON_OTHER               = 'other';

    public const STATUS_OPEN         = ProfileReportStatus::Open->value;
    public const STATUS_UNDER_REVIEW = ProfileReportStatus::UnderReview->value;
    public const STATUS_RESOLVED     = ProfileReportStatus::Resolved->value;
    public const STATUS_REJECTED     = ProfileReportStatus::Rejected->value;

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }
}
