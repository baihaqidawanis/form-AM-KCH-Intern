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
    /** @var array<string,string> Pilihan unit stabil selama satu proses PHPUnit. */
    private static array $selectedMachines = array();

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
                $values = $opts[1] ?? array();
                $available = self::firstUnusedMachineForOperationalDate($html, $values);
                return $available ?? ($values[0] ?? null);
            }
        }
        return null;
    }

    /**
     * Hindari benturan unique mesin/tanggal pada database integration-test yang
     * juga berisi data operasional. Jika modul tidak dapat dikenali, fallback ke
     * perilaku lama tanpa mengubah data yang sudah ada.
     */
    private static function firstUnusedMachineForOperationalDate(string $html, array $values): ?string
    {
        if (empty($values) || !preg_match('#action="[^"]*/([a-z0-9_]+)/add(?:[/"?])#i', $html, $match)) {
            return null;
        }
        $machineKey = strtolower($match[1]);
        if (!preg_match('/^[a-z0-9_]+$/', $machineKey)) {
            return null;
        }
		if (isset(self::$selectedMachines[$machineKey]) && in_array(self::$selectedMachines[$machineKey], array_map('strval', $values), true)) {
			return self::$selectedMachines[$machineKey];
		}

        try {
            require_once dirname(__DIR__, 2) . '/config.php';
            $pdo = new \PDO(
                'pgsql:host=' . \DB_HOST . ';port=' . \DB_PORT . ';dbname=' . \DB_NAME,
                \DB_USERNAME,
                \DB_PASSWORD,
                array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
            );
            $now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
            if ($now->format('H:i') < '06:45') {
                $now->modify('-1 day');
            }
            $table = 'tb_mesin_' . $machineKey;
            $stmt = $pdo->prepare('SELECT mesin FROM "' . $table . '" WHERE operational_date = ?');
            $stmt->execute(array($now->format('Y-m-d')));
            $used = array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
            foreach ($values as $value) {
                if (!in_array((string)$value, $used, true)) {
					self::$selectedMachines[$machineKey] = (string)$value;
					return self::$selectedMachines[$machineKey];
                }
            }
        } catch (\Throwable $e) {
            return null;
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
