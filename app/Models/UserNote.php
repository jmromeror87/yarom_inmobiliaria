<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserNote extends Model
{
    protected $table = 'user_notes';

    protected $fillable = ['user_id', 'texto', 'prioridad', 'categoria', 'completada'];

    protected $casts = ['completada' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(UserNoteAttachment::class, 'user_note_id');
    }
}
