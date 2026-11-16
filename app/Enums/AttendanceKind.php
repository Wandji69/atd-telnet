<?php

namespace App\Enums;

/**
 * How an attendance event was recorded: a card tap on a terminal, or the PIN fallback.
 */
enum AttendanceKind: string
{
    case Terminal = 'terminal';
    case Pin = 'pin';
}
