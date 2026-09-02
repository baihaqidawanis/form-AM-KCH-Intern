<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

class IllapakShiftTest extends TestCase
{
    private ApiClient $client;
    private ?string $createdDeletePath = null;
    private ?int $createdMasterPartId = null;

    protected function setUp(): void
    {
        $this->client = (new ApiClient())->loginAs('administrator');
    }

    protected function tearDown(): void
    {
        if ($this->createdDeletePath) {
            $this->client->deleteWithCsrf('illapak_1_2', $this->createdDeletePath);
        }
        if ($this->createdMasterPartId !== null) {
            $this->client->deleteWithCsrf('master_part/index/illapak_1_2', "master_part/delete/{$this->createdMasterPartId}");
        }
    }

    private function findRowId(string $html, string $needle): ?int
    {
        $rows = preg_split('/(?=<tr\b)/', $html);
        foreach ($rows as $row) {
            if (strpos($row, $needle) !== false && preg_match('/data-id="(\d+)"/', $row, $m)) {
                return (int) $m[1];
            }
        }
        return null;
    }

    public function test_shift_2_only_shows_and_saves_eligible_parts(): void
    {
        $add = $this->client->get('illapak_1_2/add?shift=2');
        $html = (string) $add->getBody();

        $this->assertSame(200, $add->getStatusCode());
        $fields = FormScraper::partFieldNames($html);
        $this->assertContains('position_indicator_sealing_vertical', $fields);
        $this->assertContains('vacum_sliter', $fields);
        $this->assertNotContains('sealing_horizontal', $fields);

        $payload = FormScraper::buildAllOkPayload($html, array('shift' => '2'));
        // Percobaan mengirim part Shift 1 secara manual harus diabaikan server.
        $payload['sealing_horizontal'] = 'NOK';
        $submit = $this->client->postWithCsrf('illapak_1_2/add', $payload);
        $body = (string) $submit->getBody();

        $this->assertSame(200, $submit->getStatusCode());
        $this->assertStringContainsString('Berhasil tambah AM Illapak 1 - 2', $body);
        $id = FormScraper::firstViewId($body, 'illapak_1_2');
        $this->assertNotNull($id);
        $this->createdDeletePath = "illapak_1_2/delete/$id";

        $view = $this->client->get("illapak_1_2/view/$id");
        $viewHtml = (string) $view->getBody();
        $this->assertStringContainsString('Shift 2', $viewHtml);
        $this->assertStringContainsString('Approved', $viewHtml);
    }

    public function test_add_without_shift_shows_shift_selector(): void
    {
        $add = $this->client->get('illapak_1_2/add');
        $this->assertSame(200, $add->getStatusCode());
        $this->assertStringContainsString('Pilih Shift Pemeriksaan', (string) $add->getBody());
    }

    public function test_new_master_part_automatically_follows_its_shift_schedule(): void
    {
        $suffix = substr(uniqid(), -8);
        $fieldName = 'phpunit_part_shift_' . $suffix;
        $label = 'PHPUnit Part Shift 2 dan 3 ' . $suffix;
        $submit = $this->client->postWithCsrf('master_part/add/illapak_1_2', array(
            'machine_key' => 'illapak_1_2',
            'field_name' => $fieldName,
            'label' => $label,
            'section' => 'TEST SHIFT',
            'metode' => 'Visual',
            'alat' => 'Mata',
            'standard' => 'OK',
            'durasi' => "1'",
            'pelaksanaan' => 'Harian Shift 2 dan 3',
            'shift_schedule' => '2,3',
            'highlight' => '',
        ));
        $body = (string) $submit->getBody();
        $this->assertStringContainsString('Part berhasil ditambahkan', $body);

        $list = $this->client->get('master_part/index/illapak_1_2');
        $this->createdMasterPartId = $this->findRowId((string) $list->getBody(), $label);
        $this->assertNotNull($this->createdMasterPartId, 'Gagal menemukan ID master part baru.');

        $this->assertStringNotContainsString($label, (string) $this->client->get('illapak_1_2/add?shift=1')->getBody());
        $this->assertStringContainsString($label, (string) $this->client->get('illapak_1_2/add?shift=2')->getBody());
        $this->assertStringContainsString($label, (string) $this->client->get('illapak_1_2/add?shift=3')->getBody());
    }
}
