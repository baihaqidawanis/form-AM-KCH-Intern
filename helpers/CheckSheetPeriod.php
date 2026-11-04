<?php
/** Period keys 1/2 remain reserved for legacy half-month signed documents. */
class CheckSheetPeriod
{
    public static function isValid($period): bool
    {
        return in_array((int)$period, array(1, 2, 11, 12, 13, 14), true);
    }

    public static function forDay($day): int
    {
        $day = (int)$day;
        if ($day < 1 || $day > 31) throw new InvalidArgumentException('Tanggal periode tidak valid.');
        return $day <= 7 ? 11 : ($day <= 14 ? 12 : ($day <= 22 ? 13 : 14));
    }

    public static function bounds($year, $month, $period): array
    {
        $year = (int)$year; $month = (int)$month; $period = (int)$period;
        if ($year < 2020 || $year > 2100 || $month < 1 || $month > 12 || !self::isValid($period)) {
            throw new InvalidArgumentException('Parameter periode dokumen tidak valid.');
        }
        $last = (int)(new DateTime(sprintf('%04d-%02d-01', $year, $month)))->format('t');
        $ranges = array(1 => array(1,16), 2 => array(17,$last), 11 => array(1,7), 12 => array(8,14), 13 => array(15,22), 14 => array(23,$last));
        return $ranges[$period];
    }

    public static function label($year, $month, $period): string
    {
        list($start, $end) = self::bounds($year, $month, $period);
        return (($period >= 11) ? 'P' . ((int)$period - 10) : 'P' . (int)$period . ' lama') . ': ' . $start . '-' . $end;
    }

    public static function coveringKeys($day): array
    {
        return array((int)$day <= 16 ? 1 : 2, self::forDay($day));
    }
}
