<?php

namespace App\Enums;

/**
 * Card and attendance status values shared with the terminal.
 */
enum CardStatus: string
{
    case CheckedIn = 'CHECKED_IN';
    case OnBreak = 'ON_BREAK';
    case CheckedOut = 'CHECKED_OUT';
}
