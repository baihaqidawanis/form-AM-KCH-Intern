<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Roles & Tag -- CRUD generik (scaffold PHPRad standar) yang belum pernah
 * ada test sama sekali. Dua-duanya cuma boleh diakses Administrator +
 * Supervisor (lihat libs/ACL.php role 1 & 3); Manager/Operator harus 403.
 */
class RolesTagCrudTest extends TestCase
{
    private ApiClient $client;
    private ?int $createdRoleId = null;
    private ?int $createdTagId = null;

    protected function setUp(): void
    {
        $this->client = (new ApiClient())->loginAs('administrator');
    }

    protected function tearDown(): void
    {
        if ($this->createdRoleId !== null) {
            // delete endpoint pakai GET dengan csrf_token di URL (deleteWithCsrf sudah benar)
            $this->client->deleteWithCsrf('roles', 'roles/delete/' . $this->createdRoleId);
            $this->createdRoleId = null;
        }
        if ($this->createdTagId !== null) {
            $this->client->deleteWithCsrf('tag', 'tag/delete/' . $this->createdTagId);
            $this->createdTagId = null;
        }
    }

    public function test_roles_full_lifecycle_add_edit_delete(): void
    {
        $submit = $this->client->postWithCsrf('roles/add', array('role_name' => 'PHPUnit Test Role'));
        $this->assertSame(200, $submit->getStatusCode());
        $body = (string) $submit->getBody();
        $this->assertStringContainsString('Record added successfully', $body);
        $this->assertStringContainsString('PHPUnit Test Role', $body);

        $id = FormScraper::firstViewId($body, 'roles');
        $this->assertNotNull($id, 'Gagal nemu id role baru');
        $this->createdRoleId = (int) $id;

        $edit = $this->client->postWithCsrf("roles/edit/$id", array('role_name' => 'PHPUnit Test Role Diedit'));
        $this->assertSame(200, $edit->getStatusCode());
        $editBody = (string) $edit->getBody();
        $this->assertStringContainsString('PHPUnit Test Role Diedit', $editBody);
        $this->assertStringNotContainsString('PHPUnit Test Role<', $editBody, 'Nama role lama mestinya udah ke-replace');

        $delete = $this->client->deleteWithCsrf('roles', 'roles/delete/' . $id);
        $this->assertSame(200, $delete->getStatusCode());
        $this->assertStringNotContainsString('PHPUnit Test Role Diedit', (string) $delete->getBody());
        $this->createdRoleId = null;
    }

    public function test_tag_full_lifecycle_add_edit_delete(): void
    {
        $submit = $this->client->postWithCsrf('tag/add', array('kategori_tag' => 'PHPUnit Test Tag'));
        $this->assertSame(200, $submit->getStatusCode());
        $body = (string) $submit->getBody();
        $this->assertStringContainsString('PHPUnit Test Tag', $body);

        $id = FormScraper::firstViewId($body, 'tag');
        $this->assertNotNull($id, 'Gagal nemu id tag baru');
        $this->createdTagId = (int) $id;

        $edit = $this->client->postWithCsrf("tag/edit/$id", array('kategori_tag' => 'PHPUnit Test Tag Diedit'));
        $this->assertStringContainsString('PHPUnit Test Tag Diedit', (string) $edit->getBody());

        $delete = $this->client->deleteWithCsrf('tag', 'tag/delete/' . $id);
        $this->assertStringNotContainsString('PHPUnit Test Tag Diedit', (string) $delete->getBody());
        $this->createdTagId = null;
    }

    public function test_manager_dan_operator_dilarang_akses_roles_dan_tag(): void
    {
        foreach (array('manager', 'operator') as $role) {
            $client = (new ApiClient())->loginAs($role);
            $this->assertSame(403, $client->get('roles')->getStatusCode(), "$role harusnya 403 buka menu Roles");
            $this->assertSame(403, $client->get('tag')->getStatusCode(), "$role harusnya 403 buka menu Tag");
        }
    }

    public function test_supervisor_bisa_akses_roles_dan_tag(): void
    {
        $supervisor = (new ApiClient())->loginAs('supervisor');
        $this->assertSame(200, $supervisor->get('roles')->getStatusCode(), 'Supervisor mestinya bisa buka menu Roles (URS 2.2)');
        $this->assertSame(200, $supervisor->get('tag')->getStatusCode(), 'Supervisor mestinya bisa buka menu Tag (URS 2.2)');
    }
}
