<?php
/** Shared non-blocking calendar flags for AM forms, exports and signed documents. */
final class OperationalCalendar
{
    const AUTO_FROM = '2026-11-12';
    const POLICY = 'weekend_off_v1';

    public static function isWorkingDay(array $row): bool
    {
        return in_array($row['is_working_day'] ?? false, array(true, 1, '1', 't', 'true'), true);
    }

    public static function automaticAllowed($signature): bool
    {
        if (!$signature || (empty($signature['operator_token']) && empty($signature['spv_token']))) return true;
        $payload = $signature['document_payload'] ?? array();
        if (is_string($payload)) $payload = json_decode($payload, true);
        return is_array($payload) && ($payload['calendar_policy'] ?? '') === self::POLICY;
    }

    public static function flags(string $start, string $end, array $manual, bool $automatic = true): array
    {
        $flags = array();
        if ($automatic) {
            $first = max($start, self::AUTO_FROM);
            for ($date = new DateTimeImmutable($first); $date->format('Y-m-d') <= $end; $date = $date->modify('+1 day')) {
                if ((int)$date->format('N') >= 6) {
                    $key = $date->format('Y-m-d');
                    $flags[$key] = array('operational_date'=>$key, 'label'=>'Holiday/Off', 'notes'=>'Sabtu/Minggu (otomatis)');
                }
            }
        }
        foreach ($manual as $row) {
            $date = substr((string)$row['operational_date'], 0, 10);
            if ($date < $start || $date > $end) continue;
            if (self::isWorkingDay($row)) unset($flags[$date]);
            else $flags[$date] = array('operational_date'=>$date, 'label'=>$row['label'], 'notes'=>$row['notes'] ?? null);
        }
        ksort($flags);
        return $flags;
    }
}
