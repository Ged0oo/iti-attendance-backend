<?php

namespace App\Enums;

enum UserRole: string
{
    case STUDENT = 'student';
    case TRACK_ADMIN = 'track_admin';
    case INSTRUCTOR = 'instructor';
    case BRANCH_MANAGER = 'branch_manager';
}
