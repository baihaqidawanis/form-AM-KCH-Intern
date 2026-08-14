<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Regression test buat fix stored-XSS Round 34: teks kendala harus selalu
 * ke-escape (htmlspecialchars) di view.php & edit_data.php, gak boleh
 * kececer lagi kalau ada yang nulis ulang salah satu view ke depan.
 */
class XssEscapingTest extends TestCase
{
    private const MACHINE = 'chimei';
    private const PAYLOAD = '<script>alert(1)</script> & "quotes"';

    public function test_kendala_text_ke_escape_di_view_dan_edit_data(): void
    {
        $client = (new ApiClient())->loginAs('administrator');

        $addPage = $client->get(self::MACHINE . '/add');
        $html = (string) $addPage->getBody();
        $partFields = \Tests\Support\FormScraper::partFieldNames($html);
        $this->assertNotEmpty($partFields);
        $nokField = $partFields[0];

        $payload = FormScraper::buildOneNokPayload($html, $nokField, self::PAYLOAD);
        $submit = $client->postWithCsrf(self::MACHINE . '/add', $payload);
        $id = FormScraper::firstViewId((string) $submit->getBody(), self::MACHINE);
        $this->assertNotNull($id);

        try {
            $view = (string) $client->get(self::MACHINE . "/view/$id")->getBody();
            $this->assertStringNotContainsString('<script>alert(1)</script>', $view, 'Payload XSS ke-render mentah di view.php — regresi Round 34!');
            $this->assertStringContainsString('&lt;script&gt;', $view);

            $editData = (string) $client->get(self::MACHINE . "/edit_data/$id")->getBody();
            $this->assertStringNotContainsString('<script>alert(1)</script>', $editData, 'Payload XSS ke-render mentah di edit_data.php — regresi Round 34!');
            $this->assertStringContainsString('&lt;script&gt;', $editData);
        } finally {
            $client->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        }
    }
}
