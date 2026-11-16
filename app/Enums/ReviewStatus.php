<?php

namespace App\Enums;

/**
 * Manager review state of an attendance event or remote check-in.
 */
enum ReviewStatus: string
{
    case None = 'none';
    case Pending = 'pending';
    case Overridden = 'overridden';
}
