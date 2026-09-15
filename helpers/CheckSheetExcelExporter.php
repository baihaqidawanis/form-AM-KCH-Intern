<?php
defined('ROOT') or exit('No direct script access allowed');

/**
 * Renderer XLSX khusus check sheet periode.
 * Tidak melakukan query atau mengubah data; seluruh isi berasal dari view_data period_report.
 */
class CheckSheetExcelExporter
{
    public static function download(array $data, string $filename): void
    {
        $bytes = self::build($data);
        $safe = XLSXWriter::sanitize_filename($filename . '.xlsx');
        header('Content-Disposition: attachment; filename="' . $safe . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Length: ' . strlen($bytes));
        header('Cache-Control: must-revalidate');
        echo $bytes;
    }

    public static function build(array $d): string
    {
        $days = range((int)$d['start_day'], (int)$d['end_day']);
        $totalCols = 8 + count($days);
        $sheet = 'Check Sheet';
        $writer = new XLSXWriter();
        $writer->setAuthor(SITE_NAME);
        $writer->setTitle((string)($d['machine_name'] ?? 'Check Sheet'));

        $widths = array(7, 4, 24, 14, 12, 34, 7, 15);
        foreach ($days as $_) { $widths[] = 4; }
        $writer->writeSheetHeader($sheet, array_fill(0, $totalCols, 'string'), array(
            'suppress_row' => true, 'widths' => $widths, 'freeze_rows' => 6
        ));

        $border = array('border' => 'left,right,top,bottom', 'border-style' => 'thin', 'border-color' => '#000000',
            'font' => 'Arial', 'font-size' => 8, 'valign' => 'center', 'wrap_text' => true);
        $center = $border + array('halign' => 'center');
        $boldCenter = $center + array('font-style' => 'bold');
        $section = $boldCenter + array('fill' => '#F2F2F2');
        $yellow = $center + array('fill' => '#FFF2CC', 'font-color' => '#856404');

        $sigStart = $totalCols - 4;
        $titleEnd = $sigStart - 1;
        self::writeRow($writer, $sheet, $totalCols, array(
            0 => 'KALBE' . "\n" . 'Consumer Health',
            3 => 'PT. BINTANG TOEDJOE' . "\n" . 'Total Productive Maintenance' . "\n" . 'Site Pulo Gadung',
            8 => 'AUTONOMOUS MAINTENANCE STANDARD' . "\n" . 'Check Sheet Kerja' . "\n" . 'Saya Pakai, Saya Rawat',
            $sigStart => 'Diperiksa Oleh' . "\n" . 'Operator Produksi',
            $sigStart + 2 => 'Disetujui Oleh' . "\n" . 'Supervisor',
        ), $boldCenter, 25);
        $operator = $d['period_signature']['operator_user']['nama'] ?? '(Belum TTD)';
        $spv = $d['period_signature']['spv_user']['nama'] ?? '(Belum TTD)';
        self::writeRow($writer, $sheet, $totalCols, array($sigStart => $operator, $sigStart + 2 => $spv), $center, 22);
        self::writeRow($writer, $sheet, $totalCols, array($sigStart => self::signatureDetail($d, 'operator'), $sigStart + 2 => self::signatureDetail($d, 'spv')), $center, 18);
        $periodText = self::monthName((int)$d['month']) . ' ' . $d['year'] . ' (P' . $d['period'] . ')';
        self::writeRow($writer, $sheet, $totalCols, array($sigStart => 'Periode: ' . $periodText), $boldCenter, 14);

        $writer->markMergedCell($sheet, 0, 0, 3, 2);
        $writer->markMergedCell($sheet, 0, 3, 3, 7);
        $writer->markMergedCell($sheet, 0, 8, 3, $titleEnd);
        foreach (array(0, 1, 2) as $r) {
            $writer->markMergedCell($sheet, $r, $sigStart, $r, $sigStart + 1);
            $writer->markMergedCell($sheet, $r, $sigStart + 2, $r, $totalCols - 1);
        }
        $writer->markMergedCell($sheet, 3, $sigStart, 3, $totalCols - 1);

        $area = self::areaName((string)($d['machine_key'] ?? ''));
        self::writeRow($writer, $sheet, $totalCols, array(
            0 => 'Area: ' . $area,
            3 => 'Mesin / Line: ' . ($d['machine_name'] ?? '-'),
            13 => 'Bulan / Tahun: ' . self::monthName((int)$d['month']) . ' ' . $d['year'],
        ), $border + array('font-style' => 'bold'), 15);
        $writer->markMergedCell($sheet, 4, 0, 4, 2);
        $metaMiddleEnd = min(12, $totalCols - 5);
        $writer->markMergedCell($sheet, 4, 3, 4, $metaMiddleEnd);
        $writer->markMergedCell($sheet, 4, $metaMiddleEnd + 1, 4, $totalCols - 1);

        $headers = array('Gambar', 'No', 'Nama Part', 'Alat', 'Metode', 'Standar', 'Durasi', 'Pelaksanaan');
        foreach ($days as $day) { $headers[] = (string)$day; }
        self::writeRow($writer, $sheet, $totalCols, $headers, $boldCenter, 18);

        $rowIndex = 6;
        $number = 0;
        $lastSection = null;
        foreach (($d['part_details'] ?? array()) as $part) {
            if (($part['section'] ?? '') !== $lastSection) {
                $lastSection = (string)($part['section'] ?? '');
                self::writeRow($writer, $sheet, $totalCols, array(0 => $lastSection . ', diisi dengan memberikan tanda (' . self::symbol('ok') . ')'), $section, 15);
                $writer->markMergedCell($sheet, $rowIndex, 0, $rowIndex, $totalCols - 1);
                $rowIndex++;
            }
            $number++;
            $field = (string)$part['field_name'];
            $shifts = self::shiftsForPart($part, $d['checks'][$field] ?? array());
            $startRow = $rowIndex;
            foreach ($shifts as $shiftOffset => $shift) {
                $values = array_fill(0, $totalCols, '');
                if ($shiftOffset === 0) {
                    $values[0] = !empty($part['image_path']) ? '[Foto]' : '';
                    $values[1] = (string)$number;
                    $values[2] = (string)($part['label'] ?? '');
                    $values[3] = (string)($part['alat'] ?? '');
                    $values[4] = (string)($part['metode'] ?? '');
                    $values[5] = (string)($part['standard'] ?? '');
                    $values[6] = (string)($part['durasi'] ?? '');
                }
                $values[7] = count($shifts) > 1 ? 'Awal Shift ' . $shift : (string)($part['pelaksanaan'] ?? '');
                $styles = array_fill(0, $totalCols, $border);
                $styles[0] = $styles[1] = $styles[6] = $styles[7] = $center;
                foreach ($days as $dayOffset => $day) {
                    $col = 8 + $dayOffset;
                    if (isset($d['deactivated_days'][$day])) {
                        $values[$col] = self::symbol('deactive');
                        $styles[$col] = $yellow;
                    } else {
                        $entries = $d['checks'][$field][$day] ?? array();
                        $value = '';
                        if (count($shifts) > 1) {
                            $value = $entries[(string)$shift] ?? ($shiftOffset === 0 ? ($entries['__default__'] ?? '') : '');
                        } elseif (!empty($entries)) {
                            $value = in_array('NOK', $entries, true) ? 'NOK' : reset($entries);
                        }
                        $values[$col] = $value === 'NOK' ? self::symbol('nok') : ($value === 'OK' ? self::symbol('ok') : '');
                        $styles[$col] = $center + ($value === 'NOK' ? array('font-style' => 'bold') : array());
                    }
                }
                self::writeRow($writer, $sheet, $totalCols, $values, $styles, 20);
                $rowIndex++;
            }
            if (count($shifts) > 1) {
                foreach (range(0, 6) as $col) { $writer->markMergedCell($sheet, $startRow, $col, $rowIndex - 1, $col); }
            }
        }

        $paraf = array_fill(0, $totalCols, '');
        $paraf[0] = 'Paraf Pelaksana';
        foreach ($days as $i => $day) {
            if (isset($d['deactivated_days'][$day])) {
                $paraf[8 + $i] = 'DEAKTIF';
            } else {
                $paraf[8 + $i] = (string)($d['daily_paraf'][$day]['user_initials'] ?? '');
            }
        }
        self::writeRow($writer, $sheet, $totalCols, $paraf, $center, 17);
        $writer->markMergedCell($sheet, $rowIndex, 0, $rowIndex, 7);
        $rowIndex++;

        self::writeRow($writer, $sheet, $totalCols, array(0 => 'Keterangan: (' . self::symbol('ok') . ') OK | (' . self::symbol('nok') . ') NOK | (' . self::symbol('deactive') . ') Deaktivasi Mesin', $totalCols - 4 => 'CR-PR-PR-1203.00 (26 Jan 2026)' . "\n" . 'Halaman: 1/1'), $border, 18);
        $writer->markMergedCell($sheet, $rowIndex, 0, $rowIndex, $totalCols - 5);
        $writer->markMergedCell($sheet, $rowIndex, $totalCols - 4, $rowIndex, $totalCols - 1);
        $rowIndex++;

        $approval = !empty($d['all_approved']) ? 'APPROVED' : 'MENUNGGU APPROVAL';
        self::writeRow($writer, $sheet, $totalCols, array(0 => $approval), $boldCenter, 18);
        $writer->markMergedCell($sheet, $rowIndex, 0, $rowIndex, $totalCols - 1);

        $tmp = tempnam(sys_get_temp_dir(), 'check_sheet_');
        try {
            $writer->writeToFile($tmp);
            self::setLandscapePrintLayout($tmp);
            return (string)file_get_contents($tmp);
        } finally {
            if (is_file($tmp)) { @unlink($tmp); }
        }
    }

    private static function writeRow(XLSXWriter $writer, string $sheet, int $count, array $values, array $style, int $height): void
    {
        $row = array_fill(0, $count, '');
        foreach ($values as $key => $value) {
            if (is_int($key) && $key >= 0 && $key < $count) { $row[$key] = $value; }
        }
        $styles = isset($style[0]) ? $style : array_fill(0, $count, $style);
        $styles['height'] = $height;
        $writer->writeSheetRow($sheet, $row, $styles);
    }

    private static function shiftsForPart(array $part, array $checks): array
    {
        $shifts = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)($part['shift_schedule'] ?? ''))), fn($v) => in_array($v, array('1','2','3'), true))));
        if (empty($shifts) || $shifts === array('1')) {
            if (preg_match('/(?<!\d)1\s*,\s*2(?:\s*,\s*3)?(?!\d)/', (string)($part['pelaksanaan'] ?? ''), $m)) {
                $shifts = array_values(array_unique(array_map('trim', explode(',', $m[0]))));
            }
        }
        foreach ($checks as $entries) {
            foreach ((array)$entries as $key => $_) {
                if (in_array((string)$key, array('2','3'), true)) { $shifts[] = (string)$key; }
            }
        }
        $shifts = array_values(array_unique($shifts ?: array('1')));
        sort($shifts, SORT_NUMERIC);
        return $shifts;
    }

    private static function signatureDetail(array $d, string $role): string
    {
        $sig = $d['period_signature'] ?? array();
        $date = $sig[$role . '_signed_at'] ?? null;
        $token = $sig[$role . '_token'] ?? null;
        return $token ? (($date ? date('d/m/y H:i', strtotime($date)) : '') . "\nRef: " . substr($token, 0, 8)) : '';
    }

    private static function monthName(int $month): string
    {
        $names = array(1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
        return $names[$month] ?? '';
    }

    private static function areaName(string $key): string
    {
        if (in_array($key, array('chimei','temach','jihcheng','jinsung_1_4','jinsung_5','best_pack','check_weigher','conveyor_sig'), true)) return 'PACKAGING 1';
        if (in_array($key, array('cosmec','fbd_jaw_chuan','fbd_glatt','supermixer','storage_tank','storage_tank_tetrapak','mixing_tank','granulator'), true)) return 'COMPOUNDING';
        return 'FILLING';
    }

    private static function setLandscapePrintLayout(string $file): void
    private static function symbol(string $type): string
    {
        $symbols = array(
            'ok' => "\xE2\x9C\x93",
            'nok' => "\xC3\x97",
            'deactive' => "\xE2\x80\x94",
        );
        return $symbols[$type] ?? '';
    }

    {
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) return;
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (is_string($xml)) {
            $xml = str_replace('fitToPage="false"', 'fitToPage="true"', $xml);
            $xml = str_replace('showGridLines="true"', 'showGridLines="false"', $xml);
            $xml = str_replace('orientation="portrait" pageOrder=', 'orientation="landscape" pageOrder=', $xml);
            $xml = str_replace('paperSize="1"', 'paperSize="9"', $xml);
            $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        }
        $zip->close();
    }
}

