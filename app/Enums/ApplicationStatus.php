<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case APPLIED = 'applied';
    case REVIEWED = 'reviewed';
    case INTERVIEW = 'interview';
    case HIRED = 'hired';
    case REJECTED = 'rejected';
}
