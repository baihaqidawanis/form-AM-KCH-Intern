<?php
defined('ROOT') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * CheckSheetSpreadsheetExporter
 *
 * XLSX exporter using PhpSpreadsheet for full image support:
 *  - Kalbe / PT. Bintang Toedjoe logo in the header
 *  - Part photos embedded in the "Gambar" column
 *  - QR code TTD (operator & SPV) in the signature area
 *
 * ISOLATED: This class does NOT touch any business logic.
 * Drop-in replacement for CheckSheetExcelExporter that adds image embedding.
 * The old XLSXWriter-based class remains untouched.
 */
class CheckSheetSpreadsheetExporter
{
    private const COLOR_BLACK  = 'FF000000';
    private const COLOR_WHITE  = 'FFFFFFFF';
    private const COLOR_GRAY   = 'FFF2F2F2';
    private const COLOR_HEADER = 'FF009639'; // Kalbe green

    private const ROW_LOGO_HEIGHT    = 70;
    private const ROW_HEADER_HEIGHT  = 20;
    private const ROW_DATA_HEIGHT    = 50;
    private const ROW_SECTION_HEIGHT = 15;
    private const ROW_FOOTER_HEIGHT  = 15;

    private const COL_WIDTHS = [
        'A' => 13, 'B' => 5,  'C' => 26, 'D' => 14,
        'E' => 13, 'F' => 36, 'G' => 7,  'H' => 17,
    ];

    // ─── Public API ────────────────────────────────────────────────────────────

    public static function download(array $data, string $filename): void
    {
        $spreadsheet = self::build($data);
        $safe = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '_', $filename) . '.xlsx';
        header('Content-Disposition: attachment; filename="' . $safe . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        ob_end_clean();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public static function build(array $d): Spreadsheet
    {
        $days      = range((int)$d['start_day'], (int)$d['end_day']);
        $dayCount  = count($days);
        $totalCols = 8 + $dayCount;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator(defined('SITE_NAME') ? SITE_NAME : 'Form AM')
            ->setTitle((string)($d['machine_name'] ?? 'Check Sheet'));

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Check Sheet');

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 6);
        $sheet->getPageMargins()->setTop(0.39)->setBottom(0.39)
            ->setLeft(0.39)->setRight(0.39)->setHeader(0)->setFooter(0);
        $sheet->setShowGridlines(false);

        // Apply column widths inline
        $colWidths = ['A'=>13,'B'=>5,'C'=>26,'D'=>14,'E'=>13,'F'=>36,'G'=>7,'H'=>17];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        foreach (array_keys($days) as $i) {
            $sheet->getColumnDimension(self::colLetter(8 + $i))->setWidth(4);
        }
        $sheet->freezePane('A7');

        $nextRow = self::buildHeader($sheet, $d, $totalCols, $days);
        $nextRow = self::buildColumnHeaders($sheet, $nextRow, $days, $totalCols);
        $nextRow = self::buildDataRows($sheet, $d, $nextRow, $days, $totalCols);
        self::buildFooter($sheet, $d, $nextRow, $days, $totalCols);

        return $spreadsheet;
    }

    // ─── Header (rows 1-5) ─────────────────────────────────────────────────────

    private static function buildHeader($sheet, array $d, int $totalCols, array $days): int
    {
        $lastCol      = self::colLetter($totalCols - 1);
        $sigStart     = $totalCols - 4;
        $titleEnd     = $sigStart - 1;
        $sigC1        = self::colLetter($sigStart);
        $sigC2        = self::colLetter($sigStart + 2);

        $sheet->getRowDimension(1)->setRowHeight(self::ROW_LOGO_HEIGHT);
        foreach ([2, 3, 4] as $r) { $sheet->getRowDimension($r)->setRowHeight(22); }
        $sheet->getRowDimension(5)->setRowHeight(16);

        $bStyle = self::baseBorderStyle();
        $centerWrap = ['horizontal' => Alignment::HORIZONTAL_CENTER,
                       'vertical'   => Alignment::VERTICAL_CENTER, 'wrapText' => true];

        // Logo cell
        $sheet->setCellValue('A1', "KALBE\nConsumer Health");
        self::applyStyle($sheet, 'A1', $bStyle + [
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => $centerWrap,
        ]);
        $sheet->mergeCells('A1:' . self::colLetter(2) . '4');

        // Title
        $sheet->setCellValue('D1', "PT. BINTANG TOEDJOE\nTotal Productive Maintenance\nSite Pulo Gadung");
        self::applyStyle($sheet, 'D1', $bStyle + ['font' => ['bold' => true, 'size' => 9], 'alignment' => $centerWrap]);
        $sheet->mergeCells('D1:' . self::colLetter($titleEnd) . '4');

        // AM Standard
        $amEnd = self::colLetter($sigStart - 1);
        $sheet->setCellValue('I1', "AUTONOMOUS MAINTENANCE STANDARD\nCheck Sheet Kerja\nSaya Pakai, Saya Rawat");
        self::applyStyle($sheet, 'I1', $bStyle + ['font' => ['bold' => true, 'size' => 9], 'alignment' => $centerWrap]);
        if ('I' !== $amEnd) { $sheet->mergeCells('I1:' . $amEnd . '4'); }

        // Sig labels
        $operatorName = $d['period_signature']['operator_user']['nama'] ?? '(Belum TTD)';
        $spvName      = $d['period_signature']['spv_user']['nama']      ?? '(Belum TTD)';
        $opDetail     = self::signatureDetail($d, 'operator');
        $spvDetail    = self::signatureDetail($d, 'spv');
        $periodText   = self::monthName((int)$d['month']) . ' ' . $d['year'] . ' (P' . $d['period'] . ')';

        $sigData = [
            1 => ["Diperiksa Oleh\nOperator Produksi", "Disetujui Oleh\nSupervisor"],
            2 => [$operatorName, $spvName],
            3 => [$opDetail,     $spvDetail],
        ];

        foreach ($sigData as $r => [$v1, $v2]) {
            $sheet->setCellValue($sigC1 . $r, $v1);
            $sheet->setCellValue($sigC2 . $r, $v2);
            $isBold = ($r === 1);
            self::applyStyle($sheet, $sigC1 . $r, $bStyle + [
                'font' => ['bold' => $isBold, 'size' => $r === 3 ? 7 : 8],
                'alignment' => $centerWrap,
            ]);
            self::applyStyle($sheet, $sigC2 . $r, $bStyle + [
                'font' => ['bold' => $isBold, 'size' => $r === 3 ? 7 : 8],
                'alignment' => $centerWrap,
            ]);
            $sheet->mergeCells($sigC1 . $r . ':' . self::colLetter($sigStart + 1) . $r);
            $sheet->mergeCells($sigC2 . $r . ':' . $lastCol . $r);
        }

        // Period row 4
        $sheet->setCellValue($sigC1 . '4', 'Periode: ' . $periodText);
        self::applyStyle($sheet, $sigC1 . '4', $bStyle + [
            'font' => ['bold' => true, 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->mergeCells($sigC1 . '4:' . $lastCol . '4');

        // Row 5: meta
        $area = self::areaName((string)($d['machine_key'] ?? ''));
        $metaEnd = min(12, $totalCols - 5);
        $metaEndLetter = self::colLetter($metaEnd);
        $metaStyle = $bStyle + ['font' => ['bold' => true, 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]];
        $sheet->setCellValue('A5', 'Area: ' . $area);
        $sheet->setCellValue('D5', 'Mesin / Line: ' . ($d['machine_name'] ?? '-'));
        $sheet->setCellValue($metaEndLetter . '5', 'Bulan / Tahun: ' . self::monthName((int)$d['month']) . ' ' . $d['year']);
        self::applyStyle($sheet, 'A5', $metaStyle);
        self::applyStyle($sheet, 'D5', $metaStyle);
        self::applyStyle($sheet, $metaEndLetter . '5', $metaStyle);
        $sheet->mergeCells('A5:C5');
        $sheet->mergeCells('D5:' . self::colLetter($metaEnd - 1) . '5');
        $sheet->mergeCells($metaEndLetter . '5:' . $lastCol . '5');

        // Embed images
        self::embedLogoImage($sheet);
        self::embedSignatureQr($sheet, $d, $sigStart);

        return 6;
    }

    // ─── Column headers (row 6) ────────────────────────────────────────────────

    private static function buildColumnHeaders($sheet, int $row, array $days, int $totalCols): int
    {
        $sheet->getRowDimension($row)->setRowHeight(self::ROW_HEADER_HEIGHT);
        $headers = ['Gambar','No','Nama Part','Alat','Metode','Standar','Durasi','Pelaksanaan'];
        foreach ($days as $day) { $headers[] = (string)$day; }
        foreach ($headers as $i => $h) {
            $cell = self::colLetter($i) . $row;
            $sheet->setCellValue($cell, $h);
            self::applyStyle($sheet, $cell, [
                'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => self::COLOR_WHITE]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER]],
                'borders'   => self::thinBorders(),
            ]);
        }
        return $row + 1;
    }

    // ─── Data rows ─────────────────────────────────────────────────────────────

    private static function buildDataRows($sheet, array $d, int $startRow, array $days, int $totalCols): int
    {
        $row         = $startRow;
        $number      = 0;
        $lastSection = null;
        $lastCol     = self::colLetter($totalCols - 1);

        foreach (($d['part_details'] ?? []) as $part) {
            if (($part['section'] ?? '') !== $lastSection) {
                $lastSection = (string)($part['section'] ?? '');
                $sheet->getRowDimension($row)->setRowHeight(self::ROW_SECTION_HEIGHT);
                $label = $lastSection . ', diisi dengan memberikan tanda (' . self::symbol('ok') . ')';
                $sheet->setCellValue('A' . $row, $label);
                self::applyStyle($sheet, 'A' . $row, [
                    'font'      => ['bold' => true, 'size' => 8],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                                    'vertical'   => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_GRAY]],
                    'borders'   => self::thinBorders(),
                ]);
                $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
                $row++;
            }

            $number++;
            $field    = (string)$part['field_name'];
            $shifts   = self::shiftsForPart($part, $d['checks'][$field] ?? []);
            $startR   = $row;
            $hasPhoto = !empty($part['image_path']);

            foreach ($shifts as $shiftOffset => $shift) {
                $rowH = ($shiftOffset === 0 && $hasPhoto) ? self::ROW_DATA_HEIGHT : 18;
                $sheet->getRowDimension($row)->setRowHeight($rowH);

                if ($shiftOffset === 0) {
                    $sheet->setCellValue('A' . $row, '');
                    $sheet->setCellValue('B' . $row, (string)$number);
                    $sheet->setCellValue('C' . $row, (string)($part['label']    ?? ''));
                    $sheet->setCellValue('D' . $row, (string)($part['alat']     ?? ''));
                    $sheet->setCellValue('E' . $row, (string)($part['metode']   ?? ''));
                    $sheet->setCellValue('F' . $row, (string)($part['standard'] ?? ''));
                    $sheet->setCellValue('G' . $row, (string)($part['durasi']   ?? ''));
                }

                $pelak = count($shifts) > 1 ? 'Awal Shift ' . $shift : (string)($part['pelaksanaan'] ?? '');
                $sheet->setCellValue('H' . $row, $pelak);

                foreach ($days as $dayOffset => $day) {
                    $col = self::colLetter(8 + $dayOffset);
                    if (isset($d['deactivated_days'][$day])) {
                        $sheet->setCellValue($col . $row, self::symbol('deactive'));
                        self::applyStyle($sheet, $col . $row, [
                            'font'      => ['size' => 8],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                            'vertical'   => Alignment::VERTICAL_CENTER],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF2CC']],
                            'borders'   => self::thinBorders(),
                        ]);
                    } else {
                        $entries = $d['checks'][$field][$day] ?? [];
                        $value   = '';
                        if (count($shifts) > 1) {
                            $value = $entries[(string)$shift] ?? ($shiftOffset === 0 ? ($entries['__default__'] ?? '') : '');
                        } elseif (!empty($entries)) {
                            $value = in_array('NOK', $entries, true) ? 'NOK' : reset($entries);
                        }
                        $symbol = $value === 'NOK' ? self::symbol('nok') : ($value === 'OK' ? self::symbol('ok') : '');
                        $isNok  = ($value === 'NOK');
                        $sheet->setCellValue($col . $row, $symbol);
                        self::applyStyle($sheet, $col . $row, [
                            'font'      => ['bold' => $isNok, 'size' => 8,
                                            'color' => ['argb' => $isNok ? 'FFCC0000' : self::COLOR_BLACK]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                            'vertical'   => Alignment::VERTICAL_CENTER],
                            'borders'   => self::thinBorders(),
                        ]);
                    }
                }

                $centerAlign = ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER];
                $leftAlign   = ['horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical'   => Alignment::VERTICAL_CENTER, 'wrapText' => true];
                $textStyle   = ['font' => ['size' => 8], 'borders' => self::thinBorders()];
                foreach (['A','B','G','H'] as $c) {
                    self::applyStyle($sheet, $c . $row, $textStyle + ['alignment' => $centerAlign]);
                }
                foreach (['C','D','E','F'] as $c) {
                    self::applyStyle($sheet, $c . $row, $textStyle + ['alignment' => $leftAlign]);
                }

                $row++;
            }

            if (count($shifts) > 1 && $startR < $row - 1) {
                foreach (range(0, 6) as $ci) {
                    $c = self::colLetter($ci);
                    $sheet->mergeCells($c . $startR . ':' . $c . ($row - 1));
                }
            }

            if ($hasPhoto) {
                self::embedPartPhoto($sheet, $part['image_path'], $startR);
            }
        }

        return $row;
    }

    // ─── Footer ────────────────────────────────────────────────────────────────

    private static function buildFooter($sheet, array $d, int $row, array $days, int $totalCols): void
    {
        $lastCol = self::colLetter($totalCols - 1);
        $bStyle  = self::baseBorderStyle();
        $centerAlign = ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER];

        // Paraf row
        $sheet->getRowDimension($row)->setRowHeight(self::ROW_FOOTER_HEIGHT);
        $sheet->setCellValue('A' . $row, 'Paraf Pelaksana');
        self::applyStyle($sheet, 'A' . $row, $bStyle + ['font' => ['bold' => true, 'size' => 8], 'alignment' => $centerAlign]);
        $sheet->mergeCells('A' . $row . ':H' . $row);
        foreach ($days as $i => $day) {
            $col = self::colLetter(8 + $i);
            $val = isset($d['deactivated_days'][$day]) ? 'DEAKTIF' : (string)($d['daily_paraf'][$day]['user_initials'] ?? '');
            $sheet->setCellValue($col . $row, $val);
            self::applyStyle($sheet, $col . $row, $bStyle + ['font' => ['size' => 7], 'alignment' => $centerAlign]);
        }
        $row++;

        // Keterangan row
        $sheet->getRowDimension($row)->setRowHeight(18);
        $ket = 'Keterangan: (' . self::symbol('ok') . ') OK | (' . self::symbol('nok') . ') NOK | (' . self::symbol('deactive') . ') Deaktivasi Mesin';
        $doc = 'CR-PR-PR-1203.00 (26 Jan 2026)' . "\n" . 'Halaman: 1/1';
        $refCol = self::colLetter($totalCols - 4);
        $sheet->setCellValue('A' . $row, $ket);
        $sheet->setCellValue($refCol . $row, $doc);
        self::applyStyle($sheet, 'A' . $row, $bStyle + ['font' => ['size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]]);
        self::applyStyle($sheet, $refCol . $row, $bStyle + ['font' => ['size' => 7],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER, 'wrapText' => true]]);
        $sheet->mergeCells('A' . $row . ':' . self::colLetter($totalCols - 5) . $row);
        $sheet->mergeCells($refCol . $row . ':' . $lastCol . $row);
        $row++;

        // Approval row
        $sheet->getRowDimension($row)->setRowHeight(16);
        $approval = !empty($d['all_approved']) ? 'APPROVED' : 'MENUNGGU APPROVAL';
        $approvalColor = !empty($d['all_approved']) ? 'FF009639' : 'FFCC8800';
        $sheet->setCellValue('A' . $row, $approval);
        self::applyStyle($sheet, 'A' . $row, $bStyle + [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => $approvalColor]],
            'alignment' => $centerAlign,
        ]);
        $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
    }

    // ─── Image helpers ─────────────────────────────────────────────────────────

    private static function embedLogoImage($sheet): void
    {
        $logoPath = defined('ROOT') ? ROOT . 'assets/images/logo.png' : '';
        if (!$logoPath || !file_exists($logoPath)) return;
        try {
            $drawing = new Drawing();
            $drawing->setName('Logo')->setDescription('Kalbe Logo')
                ->setPath($logoPath)->setCoordinates('A1')
                ->setOffsetX(5)->setOffsetY(5)->setHeight(60)
                ->setWorksheet($sheet);
        } catch (\Throwable $e) { /* silently skip */ }
    }

    private static function embedSignatureQr($sheet, array $d, int $sigStartIdx): void
    {
        $sig   = $d['period_signature'] ?? [];
        $roles = ['operator' => $sigStartIdx, 'spv' => $sigStartIdx + 2];
        foreach ($roles as $role => $colIdx) {
            $token = $sig[$role . '_token'] ?? null;
            if (!$token) continue;
            $pngBytes = self::generateQrPngForToken($token);
            if (!$pngBytes) continue;
            try {
                $tmpFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
                file_put_contents($tmpFile, $pngBytes);
                $drawing = new Drawing();
                $drawing->setName('QR_' . $role)->setDescription('TTD ' . $role)
                    ->setPath($tmpFile)->setCoordinates(self::colLetter($colIdx) . '2')
                    ->setOffsetX(5)->setOffsetY(2)->setHeight(38)
                    ->setWorksheet($sheet);
                register_shutdown_function(static fn() => is_file($tmpFile) ? @unlink($tmpFile) : null);
            } catch (\Throwable $e) { /* silently skip */ }
        }
    }

    private static function generateQrPngForToken(string $token): ?string
    {
        if (!class_exists('QrSignatureHelper')) return null;
        $verifyUrl = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/verify/' . urlencode($token);
        try {
            $b64 = QrSignatureHelper::generateQrBase64($verifyUrl, 6, true);
            $pos = strpos($b64, ',');
            return $pos !== false ? base64_decode(substr($b64, $pos + 1)) : null;
        } catch (\Throwable $e) { return null; }
    }

    private static function embedPartPhoto($sheet, string $imagePath, int $excelRow): void
    {
        if (!$imagePath) return;
        $absPath = $imagePath;
        if (!file_exists($absPath) && defined('ROOT')) {
            $absPath = ROOT . ltrim($imagePath, '/\\');
        }
        if (!file_exists($absPath)) return;
        try {
            $drawing = new Drawing();
            $drawing->setName('Part_' . $excelRow)->setDescription('Part photo')
                ->setPath($absPath)->setCoordinates('A' . $excelRow)
                ->setOffsetX(3)->setOffsetY(3)->setHeight(44)
                ->setWorksheet($sheet);
        } catch (\Throwable $e) { /* silently skip */ }
    }

    // ─── Style helpers ─────────────────────────────────────────────────────────

    private static function applyStyle($sheet, string $cell, array $style): void
    {
        $s = $sheet->getStyle($cell);
        if (isset($style['font'])) {
            $f = $s->getFont();
            if (isset($style['font']['bold']))  $f->setBold($style['font']['bold']);
            if (isset($style['font']['size']))  $f->setSize($style['font']['size']);
            if (isset($style['font']['name']))  $f->setName($style['font']['name']);
            if (isset($style['font']['color'])) $f->getColor()->setARGB($style['font']['color']['argb']);
        }
        if (isset($style['alignment'])) {
            $a = $s->getAlignment();
            if (isset($style['alignment']['horizontal'])) $a->setHorizontal($style['alignment']['horizontal']);
            if (isset($style['alignment']['vertical']))   $a->setVertical($style['alignment']['vertical']);
            if (isset($style['alignment']['wrapText']))   $a->setWrapText($style['alignment']['wrapText']);
        }
        if (isset($style['fill'])) {
            $fill = $s->getFill();
            $fill->setFillType($style['fill']['fillType']);
            if (isset($style['fill']['startColor'])) {
                $fill->getStartColor()->setARGB($style['fill']['startColor']['argb']);
            }
        }
        if (isset($style['borders'])) {
            $borders = $s->getBorders();
            foreach (['left','right','top','bottom'] as $side) {
                $borders->{'get' . ucfirst($side)}()
                    ->setBorderStyle($style['borders'][$side] ?? Border::BORDER_THIN);
            }
        }
    }

    private static function baseBorderStyle(): array
    {
        return ['font' => ['size' => 8, 'name' => 'Arial'], 'borders' => self::thinBorders()];
    }

    private static function thinBorders(): array
    {
        return [
            'left'   => Border::BORDER_THIN, 'right'  => Border::BORDER_THIN,
            'top'    => Border::BORDER_THIN, 'bottom' => Border::BORDER_THIN,
        ];
    }

    // ─── Data helpers ──────────────────────────────────────────────────────────

    private static function shiftsForPart(array $part, array $checks): array
    {
        $shifts = array_values(array_unique(array_filter(
            array_map('trim', explode(',', (string)($part['shift_schedule'] ?? ''))),
            fn($v) => in_array($v, ['1','2','3'], true)
        )));
        if (empty($shifts) || $shifts === ['1']) {
            if (preg_match('/(?<!\d)1\s*,\s*2(?:\s*,\s*3)?(?!\d)/', (string)($part['pelaksanaan'] ?? ''), $m)) {
                $shifts = array_values(array_unique(array_map('trim', explode(',', $m[0]))));
            }
        }
        foreach ($checks as $entries) {
            foreach ((array)$entries as $key => $_) {
                if (in_array((string)$key, ['2','3'], true)) { $shifts[] = (string)$key; }
            }
        }
        $shifts = array_values(array_unique($shifts ?: ['1']));
        sort($shifts, SORT_NUMERIC);
        return $shifts;
    }

    private static function signatureDetail(array $d, string $role): string
    {
        $sig   = $d['period_signature'] ?? [];
        $date  = $sig[$role . '_signed_at'] ?? null;
        $token = $sig[$role . '_token']     ?? null;
        return $token ? (($date ? date('d/m/y H:i', strtotime($date)) : '') . "\nRef: " . substr($token, 0, 8)) : '';
    }

    private static function monthName(int $month): string
    {
        $names = [1=>'Januari','Februari','Maret','April','Mei','Juni',
                  'Juli','Agustus','September','Oktober','November','Desember'];
        return $names[$month] ?? '';
    }

    private static function areaName(string $key): string
    {
        if (in_array($key, ['chimei','temach','jihcheng','jinsung_1_4','jinsung_5',
                             'best_pack','check_weigher','conveyor_sig'], true)) return 'PACKAGING 1';
        if (in_array($key, ['cosmec','fbd_jaw_chuan','fbd_glatt','supermixer',
                             'storage_tank','storage_tank_tetrapak','mixing_tank','granulator'], true)) return 'COMPOUNDING';
        return 'FILLING';
    }

    private static function symbol(string $type): string
    {
        return [
            'ok'       => "\xE2\x9C\x93",
            'nok'      => "\xC3\x97",
            'deactive' => "\xE2\x80\x94",
        ][$type] ?? '';
    }

    /** 0-based column index to Excel column letter. 0→A, 25→Z, 26→AA */
    private static function colLetter(int $index): string
    {
        $letter = '';
        $n = $index;
        while ($n >= 0) {
            $letter = chr(65 + ($n % 26)) . $letter;
            $n = intdiv($n, 26) - 1;
        }
        return $letter;
    }
}