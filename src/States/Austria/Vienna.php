<?php

namespace Spatie\Holidays\States\Austria;

use Carbon\CarbonImmutable;
use Spatie\Holidays\Countries\Austria;
use Spatie\Holidays\Countries\Germany;
use Spatie\Holidays\States\State;

class
Vienna extends State
{
    public static function country(): string
    {
        return Austria::class;
    }

    public function stateCode(): string
    {
        return 'wi';
    }

    /** @return array<string, string|CarbonImmutable> */
    public function allHolidays(int $year): array
    {
        return [
            'Leopold' => '11-15',
            'Wiener Landtag' => '01-21',
        ];
    }
}
