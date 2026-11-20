<?php

namespace App\Enums;

/**
 * What a manager decided on a review item.
 */
enum ReviewAction: string
{
    case Override = 'override';
    case Dismiss = 'dismiss';
}
