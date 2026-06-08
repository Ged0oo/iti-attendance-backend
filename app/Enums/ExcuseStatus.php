<?php

namespace App\Enums;

enum ExcuseStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
