<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNoteAttachment extends Model
{
    protected $table = 'user_note_attachments';

    protected $fillable = ['user_note_id', 'path', 'nombre', 'mime', 'size'];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(UserNote::class, 'user_note_id');
    }
}
