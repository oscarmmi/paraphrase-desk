<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParaphraseJob extends Model
{
    protected $fillable = [
        'source',
        'status',
    ];

    /** @return HasMany<Paraphrase, $this> */
    public function paraphrases(): HasMany
    {
        return $this->hasMany(Paraphrase::class);
    }
}
