<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNote extends Model
{
    protected $table = 'user_notes';

    protected $fillable = ['user_id', 'texto', 'prioridad', 'categoria', 'completada'];

    protected $casts = ['completada' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
