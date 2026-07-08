<?php

namespace App\Enums;

enum DonationStatus: string
{
    case Initiated = 'initiated';
    case AwaitingCustomer = 'awaiting_customer';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Refunded = 'refunded';
}
