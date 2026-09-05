<?php

namespace App\Models;

use App\Enums\ProfileClaimStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileClaim extends Model
{
    protected $table = 'profile_claims';

    protected $fillable = [
        'profile_id', 'claimant_name', 'claimant_email', 'claimant_role',
        'evidence_reference', 'status', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'status'      => ProfileClaimStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_PENDING  = ProfileClaimStatus::Pending->value;
    public const STATUS_VERIFIED = ProfileClaimStatus::Verified->value;
    public const STATUS_REJECTED = ProfileClaimStatus::Rejected->value;

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }
}
