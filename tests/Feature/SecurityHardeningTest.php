<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

class SecurityHardeningTest extends TestCase
{
    private function database(): \PDO
    {
        return new \PDO('pgsql:host=' . \DB_HOST . ';port=' . \DB_PORT . ';dbname=' . \DB_NAME, \DB_USERNAME, \DB_PASSWORD, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
    }

    public function test_nok_tanpa_uraian_dan_klasifikasi_ditolak_tanpa_parent_record(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $html = (string)$admin->get('chimei/add')->getBody();
        $field = FormScraper::partFieldNames($html)[0];
        $payload = FormScraper::buildOneNokPayload($html, $field, 'akan dihapus');
        foreach (array('kendala_', 'kategori_tag_', 'korelasi_tag_', 'klasifikasi_tag_', 'kategori_ketidaksesuaian_') as $prefix) {
            unset($payload[$prefix . $field]);
        }
        $before = (int)$this->database()->query('SELECT COUNT(*) FROM tb_mesin_chimei')->fetchColumn();

        $response = $admin->postWithCsrf('chimei/add', $payload);

        $this->assertStringContainsString('wajib diisi untuk part NOK', (string)$response->getBody());
        $after = (int)$this->database()->query('SELECT COUNT(*) FROM tb_mesin_chimei')->fetchColumn();
        $this->assertSame($before, $after, 'Parent form tidak boleh tersimpan bila detail NOK tidak lengkap.');
    }

    public function test_editfield_menolak_perubahan_kolom_atribusi(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $html = (string)$admin->get('chimei/add')->getBody();
        $payload = FormScraper::buildOneNokPayload($html, FormScraper::partFieldNames($html)[0], 'fixture whitelist editfield');
        $submit = $admin->postWithCsrf('chimei/add', $payload);
        $id = FormScraper::firstViewId((string)$submit->getBody(), 'chimei');
        $this->assertNotNull($id);

        try {
            $spv = (new ApiClient())->loginAs('supervisor');
            $response = $spv->postWithCsrfFrom('home', "chimei/editfield/$id", array('name' => 'user_approve', 'value' => 'ATTACKER'));
            $this->assertSame(403, $response->getStatusCode());
            $stmt = $this->database()->prepare('SELECT user_approve FROM tb_mesin_chimei WHERE id_chimei = ?');
            $stmt->execute(array($id));
            $this->assertNull($stmt->fetchColumn());
        } finally {
            $admin->deleteWithCsrf("chimei/view/$id", "chimei/delete/$id");
        }
    }
}
