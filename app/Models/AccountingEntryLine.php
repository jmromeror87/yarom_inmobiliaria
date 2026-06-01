<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingEntryLine extends Model
{
    protected $table = 'accounting_entry_lines';

    protected $fillable = [
        'entry_id','account_id','third_id','cost_center_id',
        'descripcion','debito','credito','base_retencion','orden',
    ];

    protected $casts = [
        'debito'         => 'decimal:2',
        'credito'        => 'decimal:2',
        'base_retencion' => 'decimal:2',
    ];

    public function entry(): BelongsTo      { return $this->belongsTo(AccountingEntry::class); }
    public function account(): BelongsTo    { return $this->belongsTo(AccountingAccount::class, 'account_id'); }
    public function third(): BelongsTo      { return $this->belongsTo(Third::class); }
    public function costCenter(): BelongsTo { return $this->belongsTo(AccountingCostCenter::class, 'cost_center_id'); }
}
