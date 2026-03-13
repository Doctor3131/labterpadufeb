<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case RECEIPT = 'RECEIPT';
    case CONDITION_CHANGE = 'CONDITION_CHANGE';
    case TRANSFER = 'TRANSFER';
    case ADJUSTMENT = 'ADJUSTMENT';

    /**
     * Get display label
     */
    public function label(): string
    {
        return match($this) {
            self::RECEIPT => 'Penerimaan',
            self::CONDITION_CHANGE => 'Perubahan Kondisi',
            self::TRANSFER => 'Transfer',
            self::ADJUSTMENT => 'Penyesuaian',
        };
    }
}
