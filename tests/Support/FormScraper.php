<?php

namespace Tests\Support;

/**
 * Ekstrak nama field part (OK/NOK/N-A radio) & nilai mesin dari HTML halaman
 * add.php — dipakai biar test gak perlu hardcode daftar part per modul (fragile,
 * gampang basi kalau $parts di controller berubah). Polanya: radio kondisi part
 * SELALU punya class "part-kondisi" (dipasang seragam di 17 modul, termasuk SIG
 * hasil Round 30/33), jadi cukup di-grep dari situ.
 */
class FormScraper
{
    /** @return string[] nama field part unik (misal ['conveyor_produk','roller_opp',...]) */
    public static function partFieldNames(string $html): array
    {
        preg_match_all('/<input\b[^>]*>/i', $html, $tags);
        $names = array();
        foreach ($tags[0] as $tag) {
            if (strpos($tag, 'part-kondisi') !== false && preg_match('/name="([a-z0-9_]+)"/', $tag, $m)) {
                $names[$m[1]] = true;
            }
        }
        return array_keys($names);
    }

    /** Nilai mesin dari hidden input ATAU pilihan pertama dropdown <select name="mesin">. */
    public static function firstMesinValue(string $html): ?string
    {
        if (preg_match('/name="mesin"\s+value="([0-9]+)"/', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/<select[^>]*name="mesin"[^>]*>(.*?)<\/select>/is', $html, $sel)) {
            if (preg_match_all('/<option\s+value="([0-9]+)"/', $sel[1], $opts)) {
                return $opts[1][0] ?? null;
            }
        }
        return null;
    }

    /** Bangun payload "semua part OK" siap kirim sebagai form_params, plus mesin & extra fields yang dikasih. */
    public static function buildAllOkPayload(string $html, array $extraFields = array()): array
    {
        $payload = $extraFields;
        $mesin = self::firstMesinValue($html);
        if ($mesin !== null) {
            $payload['mesin'] = $mesin;
        }
        foreach (self::partFieldNames($html) as $field) {
            $payload[$field] = 'OK';
        }
        return $payload;
    }

    /** Sama seperti buildAllOkPayload tapi 1 part tertentu di-set NOK + kendala. */
    public static function buildOneNokPayload(string $html, string $nokField, string $kendalaText, array $extraFields = array()): array
    {
        $payload = self::buildAllOkPayload($html, $extraFields);
        $payload[$nokField] = 'NOK';
        $payload['kendala_' . $nokField] = $kendalaText;
        $payload['kategori_tag_' . $nokField] = '1';
        $payload['korelasi_tag_' . $nokField] = '1';
        $payload['klasifikasi_tag_' . $nokField] = '1';
        $payload['kategori_ketidaksesuaian_' . $nokField] = '1';
        return $payload;
    }

    /** Ambil id record dari redirect location / body setelah submit sukses, ambil id tertinggi (terbaru). */
    public static function firstViewId(string $html, string $machineKey): ?string
    {
        if (preg_match_all('#/' . preg_quote($machineKey, '#') . '/view/(\d+)#', $html, $m) && !empty($m[1])) {
            return (string) max(array_map('intval', $m[1]));
        }
        return null;
    }
}
