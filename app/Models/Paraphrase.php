<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paraphrase extends Model
{
    protected $fillable = [
        'paraphrase_job_id',
        'rewritten',
        'kept_tokens',
        'valid',
    ];

    protected function casts(): array
    {
        return [
            'kept_tokens' => 'array',
            'valid' => 'boolean',
        ];
    }

    /** @return BelongsTo<ParaphraseJob, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(ParaphraseJob::class, 'paraphrase_job_id');
    }
}
