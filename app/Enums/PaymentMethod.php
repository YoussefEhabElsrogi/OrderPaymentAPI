<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PAYPAL = 'paypal';
    case CREDIT_CARD = 'credit_card';
    case BANK_TRANSFER = 'bank_transfer';

    /**
     * Get all enum values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get enum label for display
     */
    public function label(): string
    {
        return match($this) {
            self::PAYPAL => 'PayPal',
            self::CREDIT_CARD => 'Credit Card',
            self::BANK_TRANSFER => 'Bank Transfer',
        };
    }
}
