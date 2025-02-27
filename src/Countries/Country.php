<?php

namespace Spatie\Holidays\Countries;

use Carbon\CarbonImmutable;
use RuntimeException;
use Spatie\Holidays\Exceptions\InvalidYear;
use Spatie\Holidays\Exceptions\UnsupportedCountry;
use Spatie\Holidays\States\State;

abstract class Country
{
    abstract public function countryCode(): string;

    /** @return array<string, string|CarbonImmutable> */
    abstract protected function allHolidays(int $year): array;

    /** @return array<string, CarbonImmutable> */
    public function get(int $year, ?State $state = null): array
    {
        $this->ensureYearCanBeCalculated($year);

        $allHolidaysCountry = $this->formatHolidays(
            holidays: $this->allHolidays($year),
            year: $year,
        );

        $allHolidaysState = $this->formatHolidays(
            holidays: $state?->allHolidays($year) ?? [],
            year: $year,
        );

        $allHolidays = [
            ...$allHolidaysCountry,
            ...$allHolidaysState,
        ];

        uasort($allHolidays,
            static fn (CarbonImmutable $a, CarbonImmutable $b) => $a->timestamp <=> $b->timestamp
        );

        return $allHolidays;
    }

    public static function make(?State $state = null): static
    {
        return new static(...func_get_args());
    }

    protected function easter(int $year): CarbonImmutable
    {
        $easter = CarbonImmutable::createFromFormat('Y-m-d', "{$year}-03-21")
            ->startOfDay();

        return $easter->addDays(easter_days($year));
    }

    public static function find(string $countryCode): ?Country
    {
        $countryCode = strtolower($countryCode);

        foreach (glob(__DIR__.'/../Countries/*.php') as $filename) {
            if (basename($filename) === 'Country.php') {
                continue;
            }

            // determine class name from file name
            $countryClass = '\\Spatie\\Holidays\\Countries\\'.basename($filename, '.php');

            /** @var Country $country */
            $country = new $countryClass;

            if (strtolower($country->countryCode()) === $countryCode) {
                return $country;
            }
        }

        return null;
    }

    public static function findOrFail(string $countryCode): Country
    {
        $country = self::find($countryCode);

        if (! $country) {
            throw UnsupportedCountry::make($countryCode);
        }

        return $country;
    }

    protected function ensureYearCanBeCalculated(int $year): void
    {
        /**
         * Most holidays have Easter as an anchor. Elsewhere in the
         * code we use PHP's native easter-date function, which can only handle
         * years between 1970 and 2037
         *
         * https://www.php.net/manual/en/function.easter-date.php
         */
        if ($year < 1970) {
            throw InvalidYear::yearTooLow();
        }

        if ($year > 2037) {
            throw InvalidYear::yearTooHigh();
        }
    }

    /**
     * @param array<string, string|CarbonImmutable> $holidays
     * @return array<string, CarbonImmutable>
     */
    private function formatHolidays(array $holidays, int $year): array
    {
        return array_map(static function ($date) use ($year) {
            if (is_string($date)) {
                $date = CarbonImmutable::createFromFormat('Y-m-d', "{$year}-{$date}");
            }

            if ($date === false) {
                throw new RuntimeException("Could not parse date for holidays.");
            }

            return $date;
        }, $holidays);
    }
}
