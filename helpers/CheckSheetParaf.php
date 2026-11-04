<?php
/** Display-only derivative; the saved profile paraf is never modified. */
class CheckSheetParaf
{
    public static function forExport($dataUri): ?string
    {
        if (!is_valid_base64_png_data_uri($dataUri)) return null;
        if (!function_exists('imagecreatefromstring')) return $dataUri;
        static $cache = array();
        $key = hash('sha256', $dataUri);
        if (isset($cache[$key])) return $cache[$key];
        $source = @imagecreatefromstring(base64_decode(substr($dataUri, strpos($dataUri, ',') + 1)));
        if ($source === false) return $dataUri;
        $w = imagesx($source); $h = imagesy($source);
        $left = $w; $top = $h; $right = -1; $bottom = -1;
        for ($y = 0; $y < $h; $y++) for ($x = 0; $x < $w; $x++) {
            $c = imagecolorsforindex($source, imagecolorat($source, $x, $y));
            if ($c['alpha'] < 96 && min($c['red'], $c['green'], $c['blue']) < 190) {
                $left = min($left, $x); $right = max($right, $x);
                $top = min($top, $y); $bottom = max($bottom, $y);
            }
        }
        if ($right < $left || $bottom < $top) { imagedestroy($source); return $dataUri; }
        $cropW = $right - $left + 1; $cropH = $bottom - $top + 1;
        $scale = min(160 / $cropW, 80 / $cropH);
        $drawW = max(1, (int)round($cropW * $scale)); $drawH = max(1, (int)round($cropH * $scale));
        $resized = imagecreatetruecolor($drawW + 6, $drawH + 6);
        imagefill($resized, 0, 0, imagecolorallocate($resized, 255, 255, 255));
        imagecopyresampled($resized, $source, 3, 3, $left, $top, $drawW, $drawH, $cropW, $cropH);
        imagedestroy($source);
        $bold = imagecreatetruecolor($drawW + 6, $drawH + 6);
        imagefill($bold, 0, 0, imagecolorallocate($bold, 255, 255, 255));
        $black = imagecolorallocate($bold, 0, 0, 0);
        for ($y = 1; $y < imagesy($resized)-1; $y++) for ($x = 1; $x < imagesx($resized)-1; $x++) {
            $rgb = imagecolorat($resized, $x, $y);
            if ((($rgb >> 16) & 255) * .299 + (($rgb >> 8) & 255) * .587 + ($rgb & 255) * .114 < 190) {
                imagefilledellipse($bold, $x, $y, 3, 3, $black);
            }
        }
        ob_start(); imagepng($bold); $bytes = ob_get_clean();
        imagedestroy($resized); imagedestroy($bold);
        return $cache[$key] = 'data:image/png;base64,' . base64_encode($bytes);
    }
}
