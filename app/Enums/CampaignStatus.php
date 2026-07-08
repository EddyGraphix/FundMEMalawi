<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Flagged = 'flagged';
    case Rejected = 'rejected';
}
