<?php

namespace App\Models;

use App\Enums\ProfileLinkType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileLink extends Model
{
    protected $table = 'profile_links';

    protected $fillable = [
        'profile_id',
        'profile_submission_id',
        'link_type',
        'label',
        'original_url',
        'normalized_url',
        'host',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'link_type' => ProfileLinkType::class,
        'is_primary' => 'boolean',
        'sort_order' => 'int',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ProfileSubmission::class, 'profile_submission_id');
    }
}
