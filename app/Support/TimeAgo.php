<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;

final class TimeAgo
{
    public static function human(mixed $date): string
    {
        if ($date === null) {
            return '-';
        }

        $moment = $date instanceof DateTimeInterface ? Carbon::instance($date) : Carbon::parse((string) $date);
        $seconds = $moment->diffInSeconds(now(), absolute: true);

        return match (true) {
            $seconds < 60 => 'baru saja',
            $seconds < 3600 => intdiv($seconds, 60).' menit lalu',
            $seconds < 86400 => intdiv($seconds, 3600).' jam lalu',
            $seconds < 2592000 => intdiv($seconds, 86400).' hari lalu',
            default => $moment->locale('id')->translatedFormat('d M Y H:i'),
        };
    }
}
