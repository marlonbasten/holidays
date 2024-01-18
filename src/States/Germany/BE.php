<?php

namespace Spatie\Holidays\States\Germany;

use Carbon\CarbonImmutable;
use Spatie\Holidays\Countries\Germany;
use Spatie\Holidays\States\State;

class BE extends State
{
    public static function country(): string
    {
        return Germany::class;
    }

    public function stateCode(): string
    {
        return 'be';
    }

    /** @return array<string, string|CarbonImmutable> */
    public function allHolidays(int $year): array
    {
        return [
            'Internationaler Frauentag' => '03-08',
        ];
    }
}
