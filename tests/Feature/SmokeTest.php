<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * Regression sweep otomatis — nge-otomatisin "curl semua modul, cek 200 + nol
 * error" yang selama ini saya lakuin manual tiap abis ubah kode bersama.
 * Kalau ada 1 modul yang error setelah perubahan di BaseMachineController,
 * ini bakal ketauan dalam hitungan detik, bukan nunggu ketauan manual/user report.
 */
class SmokeTest extends TestCase
{
    private const MACHINES = array(
        'sig', 'joeya', 'illapak_1_2', 'illapak_3_12', 'unifill_b',
        'chimei', 'temach', 'jihcheng', 'jinsung_1_4', 'jinsung_5', 'best_pack',
        'cosmec', 'fbd_jaw_chuan', 'fbd_glatt', 'supermixer', 'storage_tank', 'mixing_tank',
    );

    private const INFRA_PAGES = array('Home', 'approval', 'users', 'roles', 'tag', 'audit_log', 'panduan_pengisian_am');

    private const ERROR_MARKERS = array(
        'Fatal error', 'Uncaught', 'SQLSTATE', 'Deprecated:', 'Was  Not Found', 'TypeError', 'ParseError',
    );

    public static function machineProvider(): array
    {
        $out = array();
        foreach (self::MACHINES as $m) {
            $out[$m] = array($m);
        }
        return $out;
    }

    /** @dataProvider machineProvider */
    public function test_list2_dan_add_page_bersih(string $machine): void
    {
        $client = (new ApiClient())->loginAs('administrator');

        $list = $client->get($machine);
        $this->assertSame(200, $list->getStatusCode(), "$machine: list2 gak 200");
        $this->assertNoErrorMarker((string) $list->getBody(), "$machine list2");

        $add = $client->get("$machine/add");
        $this->assertSame(200, $add->getStatusCode(), "$machine: add gak 200");
        $this->assertNoErrorMarker((string) $add->getBody(), "$machine add");
    }

    public function test_halaman_infra_bersih(): void
    {
        $client = (new ApiClient())->loginAs('administrator');
        foreach (self::INFRA_PAGES as $page) {
            $resp = $client->get($page);
            $this->assertSame(200, $resp->getStatusCode(), "$page gak 200");
            $this->assertNoErrorMarker((string) $resp->getBody(), $page);
        }
    }

    private function assertNoErrorMarker(string $html, string $context): void
    {
        foreach (self::ERROR_MARKERS as $marker) {
            $this->assertStringNotContainsString($marker, $html, "$context bocor error marker: $marker");
        }
    }
}
