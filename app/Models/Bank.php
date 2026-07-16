<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $fillable = ['nombre', 'numero_cuenta', 'tipo_cuenta', 'accounting_account_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function accountingAccount(): BelongsTo { return $this->belongsTo(AccountingAccount::class); }
    public function payments(): HasMany { return $this->hasMany(RentPayment::class); }
}
