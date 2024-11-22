<?php

namespace Attla\Token;

use Carbon\{
    Carbon,
    CarbonInterface,
    CarbonImmutable
};

class Util
{
    /**
     * Transform the date to a timestamp
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return int
     */
    public static function timestamp(int|CarbonInterface|\DateTimeInterface $date = null): int
    {
        if (is_null($date) || !$date) {
            return 0;
        } elseif (is_int($date)) {
            return $date;
        } elseif ($date instanceof CarbonInterface) {
            return $date->timestamp;
        } elseif ($date instanceof \DateTimeInterface) {
            return $date instanceof \DateTimeImmutable
                ? CarbonImmutable::instance($date)->timestamp
                : Carbon::instance($date)->timestamp;
        }

        return 0;
    }

    /**
     * Transform the date to a timestamp or retrieve a current timestamp
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return int
     */
    public static function minuteToSecond(int|CarbonInterface|\DateTimeInterface $date = null): int
    {
        return static::timestamp($date) ?: time();
    }

    /**
     * Parse string to Carbon instance
     *
     * @param string $str
     * @return \Carbon\Carbon
     */
    public static function strToCarbon(string $str): Carbon
    {
        return Carbon::createFromTimestamp(strtotime($str));
    }

    /**
     * Parse string to Carbon Immutable
     *
     * @param string $str
     * @return \Carbon\CarbonImmutable
     */
    public static function strToCarbonImmutable(string $str): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp(strtotime($str));
    }

    /**
     * Parse string to DateTime
     *
     * @param string $str
     * @return \DateTime
     */
    public static function strToDateTime(string $str): \DateTime
    {
        return (new \DateTime())->setTimeStamp(strtotime($str));
    }

    /**
     * Parse string to DateTimeImmutable
     *
     * @param string $str
     * @return \DateTimeImmutable
     */
    public static function strToDateTimeImmutable(string $str): \DateTimeImmutable
    {
        return date_create_immutable('@' . strtotime($str));
    }
}
