<?php

namespace App\Enums;

enum DonationPaymentMethod: string
{
    case AirtelMoney = 'airtel_money';
    case TnmMpamba = 'tnm_mpamba';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
}
