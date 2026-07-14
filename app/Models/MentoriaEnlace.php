<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoriaEnlace extends Model
{
    protected $fillable = ['mentoria_id', 'url', 'texto'];

    public function mentoria(): BelongsTo
    {
        return $this->belongsTo(Mentoria::class);
    }
}
