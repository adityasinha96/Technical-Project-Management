<?php

namespace App\Enums;

enum PaymentMode: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Upi = 'upi';
    case Cheque = 'cheque';
    case Card = 'card';
    case Razorpay = 'razorpay';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank Transfer',
            self::Upi => 'UPI',
            self::Cheque => 'Cheque',
            self::Card => 'Debit / Credit Card',
            self::Razorpay => 'Razorpay',
            self::Other => 'Other',
        };
    }
}