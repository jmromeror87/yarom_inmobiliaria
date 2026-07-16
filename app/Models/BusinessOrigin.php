<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessOrigin extends Model
{
    protected $fillable = ['nombre', 'color', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function properties(): HasMany { return $this->hasMany(Property::class); }
    public function thirds(): HasMany     { return $this->hasMany(Third::class); }
}
