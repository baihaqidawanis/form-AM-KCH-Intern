<?php

namespace Tests\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

/**
 * Wrapper HTTP buat integration test — pola yang sama persis dipakai manual
 * lewat curl sepanjang sesi refactor BaseMachineController (login, ambil
 * csrf_token dari halaman, submit form, cek response). Lihat
 * DOCS_MD/AUTOMATED_TESTING.md buat alasan kenapa test level HTTP, bukan unit.
 */
class ApiClient
{
    /** @var array<string,array{username:string,password:string}> */
    public const ACCOUNTS = array(
        'administrator' => array('username' => 'superadmin', 'password' => 'Admin@123'),
        'manager' => array('username' => 'MANAGE01', 'password' => 'Test@1234'),
        'supervisor' => array('username' => 'SUPERV01', 'password' => 'Test@1234'),
        'operator' => array('username' => 'STAFOP01', 'password' => 'Test@1234'),
    );

    private Client $client;
    private CookieJar $jar;
    private ?string $lastBody = null;

    public function __construct(?string $baseUrl = null)
    {
        $baseUrl = $baseUrl ?: (getenv('APP_BASE_URL') ?: 'http://localhost/form-am');
        $this->jar = new CookieJar();
        $this->client = new Client(array(
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'cookies' => $this->jar,
            'http_errors' => false,
            'allow_redirects' => true,
        ));
    }

    /** Login sebagai salah satu role di self::ACCOUNTS (administrator/manager/supervisor/operator). */
    public function loginAs(string $role): self
    {
        if (!isset(self::ACCOUNTS[$role])) {
            throw new \InvalidArgumentException("Role gak dikenal: $role");
        }
        $creds = self::ACCOUNTS[$role];
        $root = $this->get('');
        $token = $this->extractCsrfToken((string) $root->getBody());
        $this->client->post('index/login/?csrf_token=' . $token, array(
            'form_params' => array('username' => $creds['username'], 'password' => $creds['password']),
        ));
        return $this;
    }

    public function get(string $path)
    {
        $resp = $this->client->get(ltrim($path, '/'));
        $this->lastBody = (string) $resp->getBody();
        return $resp;
    }

    public function postWithCsrf(string $path, array $formParams)
    {
        // Ambil token dari halaman GET yang sesuai (pola: form action-nya sendiri
        // punya ?csrf_token=... — sama kayak yang saya extract manual via curl+grep).
        $getPage = $this->get($path);
        $token = $this->extractCsrfToken((string) $getPage->getBody());
        $resp = $this->client->post(ltrim($path, '/') . '/?csrf_token=' . $token, array(
            'form_params' => $formParams,
        ));
        $this->lastBody = (string) $resp->getBody();
        return $resp;
    }

    /** Upload multipart (fieldname + file) ke filehelper/uploadfile, csrf_token diambil dari $tokenSourcePath. */
    public function postMultipartUpload(string $tokenSourcePath, string $fieldname, string $filename, string $content, string $mimeType)
    {
        $getPage = $this->get($tokenSourcePath);
        $token = $this->extractCsrfToken((string) $getPage->getBody());
        $resp = $this->client->post('filehelper/uploadfile?csrf_token=' . $token, array(
            'multipart' => array(
                array('name' => 'fieldname', 'contents' => $fieldname),
                array('name' => 'file', 'contents' => $content, 'filename' => $filename, 'headers' => array('Content-Type' => $mimeType)),
            ),
        ));
        $this->lastBody = (string) $resp->getBody();
        return $resp;
    }

    public function postWithCsrfFrom(string $tokenSourcePath, string $postPath, array $formParams)
    {
        $getPage = $this->get($tokenSourcePath);
        $token = $this->extractCsrfToken((string) $getPage->getBody());
        $resp = $this->client->post(ltrim($postPath, '/') . '/?csrf_token=' . $token, array(
            'form_params' => $formParams,
        ));
        $this->lastBody = (string) $resp->getBody();
        return $resp;
    }

    public function deleteWithCsrf(string $viewPath, string $deletePath): \Psr\Http\Message\ResponseInterface
    {
        $page = $this->get($viewPath);
        $token = $this->extractCsrfToken((string) $page->getBody());
        $resp = $this->client->get(ltrim($deletePath, '/') . '?csrf_token=' . $token);
        $this->lastBody = (string) $resp->getBody();
        return $resp;
    }

    public function extractCsrfToken(string $html): string
    {
        if (preg_match('/csrf_token=([a-f0-9]+)/', $html, $m)) {
            return $m[1];
        }
        throw new \RuntimeException('csrf_token gak ketemu di halaman — kemungkinan gak lagi login atau halaman error');
    }

    public function lastBody(): ?string
    {
        return $this->lastBody;
    }
}
