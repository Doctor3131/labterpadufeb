<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'recipient',
        'transfer_date',
        'item_id',
        'batch_id',
        'source_lab_id',
        'target_lab_id',
        'user_id',
        'tracking_mode',
        'condition',
        'quantity',
        'status',
        'returned_date',
        'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'returned_date' => 'date',
        'quantity' => 'integer',
    ];

    /**
     * Get the item associated with this transfer
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the batch associated with this transfer
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the source lab (Gudang)
     */
    public function sourceLab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'source_lab_id');
    }

    /**
     * Get the target lab (Eksternal)
     */
    public function targetLab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'target_lab_id');
    }

    /**
     * Get the user who performed this transfer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
