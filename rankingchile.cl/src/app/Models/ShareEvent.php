<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareEvent extends Model
{
    protected $table = 'share_events';

    protected $fillable = [
        'profile_id', 'support_transaction_id', 'source', 'period_code',
        'referrer_id', 'utm_source', 'utm_medium', 'utm_campaign',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function supportTransaction(): BelongsTo
    {
        return $this->belongsTo(SupportTransaction::class, 'support_transaction_id');
    }
}
