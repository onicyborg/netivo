<?php

namespace App\Enums;

enum UpgradeStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case APPLIED = 'applied';
    case REJECTED = 'rejected';
}
