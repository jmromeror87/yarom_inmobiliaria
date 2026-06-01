<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingCostCenter extends Model
{
    protected $table = 'accounting_cost_centers';

    protected $fillable = [
        'codigo','nombre','descripcion','property_id','estado',
    ];

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function lines(): HasMany      { return $this->hasMany(AccountingEntryLine::class, 'cost_center_id'); }
}
