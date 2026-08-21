<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * Regresi guard buat 3 temuan nyata dari bug-hunting manual (21 Agustus 2026)
 * di FilehelperController::uploadfile() / libs/Uploader.php:
 *
 * 1. FilehelperController extends BaseController (bukan SecureController) --
 *    endpoint upload-nya gak lewat cek login sama sekali secara default.
 *    'pict' SENGAJA dibiarkan gitu (dipakai upload foto profil di halaman
 *    Register, sebelum akun ada). Tapi 'part_image' (Master Data Part,
 *    cuma buat Administrator) ke-ikut kebuka juga buat guest -- fixed
 *    dengan guard eksplisit di FilehelperController::uploadfile().
 *
 * 2. libs/Uploader.php::validate() -- `stripos($extensions_whitelist, '')`
 *    SELALU return 0 (needle kosong "ketemu" di mana aja), jadi file TANPA
 *    ekstensi (nama file gak ada titik) ketembus whitelist ekstensi apapun.
 *    File hasil upload ke-serve Apache TANPA Content-Type (karena gak ada
 *    ekstensi buat di-map), jadi browser modern bisa MIME-sniff isinya
 *    jadi text/html -> stored XSS dari upload anonim, walau file-nya
 *    sendiri gak bisa dieksekusi sebagai PHP (defense-in-depth uploads/.htaccess
 *    tetap nahan RCE-nya).
 *
 * 3. Ditambahin X-Content-Type-Options: nosniff di uploads/.htaccess sebagai
 *    lapis tambahan (independen dari fix #2 -- jaga-jaga kalau ada bypass
 *    ekstensi lain ke depan).
 */
class FileUploadSecurityTest extends TestCase
{
    private array $uploadedFiles = array();

    /** 1x1 PNG transparan asli -- dibutuhkan karena getimagesize() beneran ngecek isi file, bukan cuma nama/Content-Type. */
    private function onePixelPng(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    }

    protected function tearDown(): void
    {
        foreach ($this->uploadedFiles as $path) {
            $full = __DIR__ . '/../../' . $path;
            if (is_file($full)) { @unlink($full); }
        }
    }

    private function trackUpload(string $body): void
    {
        // body sukses = path relatif/absolut ke file yang barusan ke-upload.
        if (preg_match('#uploads/files/[^\s"\']+#', $body, $m)) {
            $this->uploadedFiles[] = $m[0];
        }
    }

    public function test_guest_bisa_upload_pict_demi_halaman_register(): void
    {
        // Ini SENGAJA jalan (bukan bug) -- kalau ini mulai 403, halaman
        // Register bakal rusak (gak bisa upload foto profil sebelum akun ada).
        $guest = new ApiClient();
        $resp = $guest->postMultipartUpload('', 'pict', 'test.png', $this->onePixelPng(), 'image/png');
        $this->assertSame(200, $resp->getStatusCode(), "Upload 'pict' oleh guest mestinya tetap jalan (dipakai halaman Register)");
        $this->trackUpload((string) $resp->getBody());
    }

    public function test_guest_dilarang_upload_part_image(): void
    {
        $guest = new ApiClient();
        $resp = $guest->postMultipartUpload('', 'part_image', 'test.png', 'fake-png-bytes', 'image/png');
        $this->assertSame(403, $resp->getStatusCode(), "'part_image' (Master Data Part, cuma buat Administrator) gak boleh bisa diupload guest");
        $this->trackUpload((string) $resp->getBody());
        $this->assertEmpty($this->uploadedFiles, 'Upload part_image oleh guest mestinya ditolak SEBELUM file kesimpen ke disk');
    }

    public function test_administrator_bisa_upload_part_image(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $resp = $admin->postMultipartUpload('master_part/add', 'part_image', 'test.png', $this->onePixelPng(), 'image/png');
        $this->assertSame(200, $resp->getStatusCode(), 'Administrator mestinya tetap bisa upload part_image seperti biasa');
        $this->trackUpload((string) $resp->getBody());
        $this->assertNotEmpty($this->uploadedFiles, 'Upload part_image oleh Administrator mestinya beneran kesimpen');
    }

    public function test_file_tanpa_ekstensi_ditolak(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $resp = $admin->postMultipartUpload('master_part/add', 'part_image', 'noextensionatall', '<?php echo "should_never_run"; ?>', 'application/octet-stream');
        $this->assertNotSame(200, $resp->getStatusCode(), 'File tanpa ekstensi sama sekali mestinya ditolak whitelist (regresi bypass stripos)');
        $this->trackUpload((string) $resp->getBody());
        $this->assertEmpty($this->uploadedFiles, 'File tanpa ekstensi gak boleh kesimpen ke disk sama sekali');
    }

    public function test_ekstensi_bahaya_ditolak(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $resp = $admin->postMultipartUpload('master_part/add', 'part_image', 'evil.php', '<?php echo "should_never_run"; ?>', 'application/x-php');
        $this->assertNotSame(200, $resp->getStatusCode(), 'File .php mestinya ditolak whitelist ekstensi part_image (cuma jpg/jpeg/png/gif/webp)');
        $this->trackUpload((string) $resp->getBody());
        $this->assertEmpty($this->uploadedFiles);
    }

    public function test_konten_bukan_gambar_ditolak_walau_ekstensi_png(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $resp = $admin->postMultipartUpload('master_part/add', 'part_image', 'fake.png', 'ini bukan gambar beneran, cuma teks biasa', 'image/png');
        $this->assertNotSame(200, $resp->getStatusCode(), 'Konten yang bukan gambar beneran mestinya ditolak getimagesize(), walau nama file .png');
        $this->trackUpload((string) $resp->getBody());
        $this->assertEmpty($this->uploadedFiles);
    }

    public function test_uploads_folder_kirim_nosniff_header(): void
    {
        // Lapis pertahanan tambahan (independen dari fix ekstensi) -- browser
        // gak boleh MIME-sniff isi folder uploads jadi HTML apapun yang terjadi.
        $guest = new ApiClient();
        $upload = $guest->postMultipartUpload('', 'pict', 'nosniff-check.png', $this->onePixelPng(), 'image/png');
        $body = (string) $upload->getBody();
        $this->trackUpload($body);
        if (preg_match('#uploads/files/[^\s"\']+#', $body, $m)) {
            $fileResp = $guest->get($m[0]);
            $this->assertSame('nosniff', $fileResp->getHeaderLine('X-Content-Type-Options'), 'Folder uploads/ mestinya kirim header X-Content-Type-Options: nosniff');
        } else {
            $this->fail('Gagal upload file buat test nosniff header');
        }
    }
}
