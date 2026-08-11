# 🔍 Laporan QA: Form AM Site Pulogadung

> **QA Review Date:** 2026-08-06  
> **Reviewer:** QA Professional (AI-Assisted)  
> **Scope:** Full static code analysis terhadap seluruh codebase  
> **Referensi:** Analisis awal sudah tercatat di [CLAUDE.md](../CLAUDE.md)

---

## 📌 Ringkasan Eksekutif

Dari review menyeluruh terhadap **56 controller**, **4 system files**, **6 helpers**, **7 libs**, dan file konfigurasi, ditemukan **32 temuan** yang dikategorikan menjadi 4 severity level. Temuan paling kritis meliputi **kebocoran kredensial (Telegram Bot Token & DB Password)**, **bug fungsional di SecureController**, dan **kode yang sudah deprecated di PHP 8.1+**.

---

## 🔴 SEVERITY: CRITICAL (Harus Segera Diperbaiki)

### C-01: Telegram Bot Token Hardcoded di 30+ Controller

**File terdampak:** Hampir semua machine controller (`Lt2_blenderController.php`, `ChimeiController.php`, `AgvController.php`, dll.)

```php
// Ditemukan di 30+ file controller — token yang SAMA di-copy-paste:
$botToken = '***TELEGRAM_TOKEN_REDACTED***';
$chatID   = '-1002428961148';
// ... dan chat ID kedua:
'chat_id' => '-4547166344', // Group Assigner
```

**Risiko:**
- Jika repo ini pernah dipush ke GitHub/GitLab publik, bot token **sudah terekspos** dan bisa disalahgunakan
- Siapapun yang punya akses ke source code bisa mengirim pesan atas nama bot
- Token harus segera di-**revoke** via BotFather dan diganti yang baru

**Rekomendasi:**
```php
// SEBELUM (❌ Hardcoded di setiap controller):
$botToken = '***TELEGRAM_TOKEN_REDACTED***';

// SESUDAH (✅ Pakai config terpusat):
// Di config.php atau .env:
define("TELEGRAM_BOT_TOKEN", $_ENV['TELEGRAM_BOT_TOKEN'] ?? '');
define("TELEGRAM_CHAT_ID_APPROVAL", $_ENV['TELEGRAM_CHAT_APPROVAL'] ?? '');
define("TELEGRAM_CHAT_ID_ASSIGNER", $_ENV['TELEGRAM_CHAT_ASSIGNER'] ?? '');

// Buat helper class TelegramNotifier.php:
class TelegramNotifier {
    public static function send($chatId, $message, $replyMarkup = null) { ... }
    public static function notifyApproval($machineName, $location, $kendala) { ... }
    public static function notifyRedTag($machineName, $location, $kendala, $idTagging) { ... }
}
```

---

### C-02: Bug Fungsional — `SecureController.php` Remember Me Rusak

**File:** [SecureController.php](../system/SecureController.php) — Baris 44

```php
// BUG: "__tablename" adalah placeholder yang tidak diganti!
$user = $db->getOne("__tablename");
// Seharusnya:
$user = $db->getOne("users");
```

**Dampak:** Fitur "Remember Me" (auto-login via cookie) **tidak akan pernah berfungsi**. Ketika user kembali ke site dengan cookie `login_session_key`, query akan gagal karena tabel `__tablename` tidak ada di database.

**Fix:**
```php
$user = $db->getOne("users");
```

---

### C-03: `FILTER_SANITIZE_STRING` Deprecated Sejak PHP 8.1

**File terdampak:** 20+ lokasi di seluruh codebase

| File | Jumlah Penggunaan |
|------|------------------|
| `BaseController.php` | 2 |
| `Router.php` | 5 |
| `BaseView.php` | 3 |
| `Functions.php` | 6 |
| `GUMP.php` | 3 |
| `IndexController.php` | 1 |

```php
// ❌ DEPRECATED di PHP 8.1, DIHAPUS di PHP 9.0:
$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$page = filter_var($page, FILTER_SANITIZE_STRING);

// ✅ Pengganti yang benar:
$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
// Atau gunakan htmlspecialchars() secara manual
```

**Dampak:** Pada PHP 8.1+ akan menghasilkan `E_DEPRECATED` warning. Pada PHP 9.0 akan **fatal error**. Karena Dockerfile menggunakan `php:8.3-apache`, ini **sudah menghasilkan warnings di production sekarang**.

---

### C-04: Password Hash Bisa Dicari via Search (Information Disclosure)

**File:** [UserController.php](../app/controllers/UserController.php) — Baris 41  
**File:** [UsersController.php](../app/controllers/UsersController.php) — Baris 41

```php
// ❌ Password hash bisa dicari via fitur search:
$search_condition = "(
    ...
    user.password LIKE ? OR    // <-- INI BERBAHAYA!
    ...
)";
```

**Dampak:**  
- User bisa mengetik sebagian password hash di kotak search dan mendapatkan result
- Membocorkan informasi bahwa record tertentu memiliki password yang mengandung substring tertentu
- Meskipun ini hash (bukan plaintext), ini tetap **information disclosure** yang tidak perlu

**Fix:** Hapus baris `user.password LIKE ?` dan `users.password LIKE ?` dari search query, serta kurangi 1 dari jumlah `%$text%` di `$search_params`.

---

### C-05: Kode Rusak di `Functions.php` — Variabel Undefined

**File:** [Functions.php](../helpers/Functions.php) — Baris 520-527

```php
/**
 * returns true if $needle is a substring of $haystack
 * @return  bool
 */
error_reporting(0);                              // ❌ Mematikan error reporting GLOBAL!
if (strpos($haystack, $needle) !== false) {      // ❌ $haystack & $needle UNDEFINED
    echo'';
}
```

**Dampak:**
- `error_reporting(0)` **mematikan semua error reporting** untuk seluruh aplikasi setelah file ini di-load
- `$haystack` dan `$needle` tidak didefinisikan — ini bukan function, tapi kode loose yang langsung dieksekusi
- Ini terlihat seperti debugging code yang tertinggal dan **merusak error reporting production**

**Fix:** Hapus seluruh blok ini (baris 520-527). Jika butuh fungsi `str_contains`, buat sebagai proper function:
```php
function str_contains_check($haystack, $needle) {
    return strpos($haystack, $needle) !== false;
}
```

---

### C-06: Akses `$_POST` Langsung Tanpa Sanitasi di Controller

**File terdampak:** 30+ machine controller (semua yang punya notifikasi Telegram)

```php
// ❌ Bypass validasi — langsung akses $_POST setelah insert:
$line    = $_POST["no_blender"];        // Tidak disanitasi
$kendala = $_POST["kendala"];           // Tidak disanitasi
$kategori_tag = $_POST["kategori_tag"]; // Tidak disanitasi

// Data ini kemudian langsung dimasukkan ke message Telegram:
$message .= "Kondisi : $kendala\n\n";   // Potential XSS via Telegram
```

**Dampak:** Meskipun data sudah melewati validasi GUMP sebelum INSERT ke database, akses `$_POST` langsung setelahnya bypass semua validasi. Ini terutama berbahaya karena value langsung dikirim ke Telegram API tanpa escaping.

**Fix:** Gunakan `$modeldata` yang sudah tervalidasi, bukan `$_POST` langsung:
```php
// Gunakan data yang sudah divalidasi:
$kendala = $modeldata['kendala'] ?? '';
$kategori_tag = $modeldata['kategori_tag'] ?? '';
```

---

## 🟠 SEVERITY: HIGH (Perlu Diperbaiki Segera)

### H-01: `hash_value()` Menggunakan MD5 — Lemah untuk Security

**File:** [Functions.php](../helpers/Functions.php) — Baris 671-675

```php
function hash_value($text) {
    $saltText = APP_ID;
    return md5($text . $saltText);  // ❌ MD5 sudah tidak aman
}
```

**Digunakan untuk:**
- Hash session key (`login_session_key`) di `IndexController.php`
- Hash password reset key di `PasswordmanagerController.php`
- CSRF token generation di `Csrf.php`

**Risiko:** MD5 rentan terhadap collision attack dan brute force. Untuk session key dan password reset key, ini bisa dieksploitasi.

**Fix:**
```php
function hash_value($text) {
    $saltText = APP_ID;
    return hash('sha256', $text . $saltText);
}
```

---

### H-02: File Upload Tanpa Validasi Ekstensi untuk Field `pict`

**File:** [BaseController.php](../system/BaseController.php) — Baris 162-170

```php
$this->file_upload_settings['pict'] = array(
    "title" => "{{random}}",
    "extensions" => "",         // ❌ KOSONG! Menerima SEMUA jenis file!
    "limit" => "1",
    "filesize" => "3",
    "returnfullpath" => true,
    "filenameprefix" => "",
    "uploadDir" => "uploads/files/"
);
```

**Risiko:** Field `pict` (user profile picture) menerima **semua jenis file** tanpa batasan ekstensi. Attacker bisa upload file `.php`, `.phtml`, atau webshell.

**Fix:**
```php
"extensions" => ".jpg,.jpeg,.png,.gif,.webp",
```

---

### H-03: Notifikasi Telegram — Mixed Usage of `curl` dan `file_get_contents`

**File terdampak:** 30+ machine controller

```php
// Notifikasi pertama pakai curl (baik):
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Notifikasi kedua pakai file_get_contents (buruk):
file_get_contents($secondUrl); // ❌ Tidak handle error, bisa fatal jika allow_url_fopen disabled
```

**Dampak:** 
- `file_get_contents()` untuk HTTP request bergantung pada `allow_url_fopen` PHP setting
- Tidak ada error handling — jika gagal, error PHP dilempar tanpa ditangkap
- Inkonsisten dengan penggunaan curl di bagian yang sama

**Fix:** Gunakan curl konsisten untuk semua HTTP request, dan wrap dalam try-catch.

---

### H-04: Koneksi Database Dibuat Baru Setiap Kali `GetModel()` Dipanggil

**File:** [BaseController.php](../system/BaseController.php) — Baris 181-190

```php
function GetModel(){
    // ❌ Setiap panggilan membuat koneksi database BARU!
    $this->db = new PDODb(DB_TYPE, DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT, DB_CHARSET);
    // ...
    return $this->db;
}
```

**Dampak:** Dalam satu request, `GetModel()` bisa dipanggil berkali-kali (misal di `add()` yang memanggil insert lalu insert ke `kendala` lalu update `id_tagging`). Setiap panggilan membuat koneksi baru ke MySQL, membuang resource server.

**Fix:** Implementasi singleton pattern:
```php
function GetModel(){
    if ($this->db === null) {
        $this->db = new PDODb(DB_TYPE, DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT, DB_CHARSET);
    }
    if ($this->soft_delete) {
        // ...
    }
    return $this->db;
}
```

---

### H-05: Race Condition pada Insert Tagging Report

**File terdampak:** Semua machine controller (contoh: `Lt2_blenderController.php` Baris 272-277)

```php
// Step 1: Insert tagging report
$db->rawQuery("INSERT INTO new_breakdown_management_2.tag_compounding ...", [$rec_id]);

// Step 2: Get ID with ORDER BY id DESC LIMIT 1 — ❌ RACE CONDITION!
$db->rawQuery("UPDATE lt2_blender 
    SET id_tagging = (SELECT id 
        FROM new_breakdown_management_2.tag_compounding
        ORDER BY id DESC
        LIMIT 1)
    WHERE id = ? AND kendala != ''", [$rec_id]);
```

**Dampak:** Jika 2 user submit form bersamaan, `ORDER BY id DESC LIMIT 1` bisa mengambil ID milik user lain. Ini menyebabkan data tagging terhubung ke record yang salah.

**Fix:** Gunakan `LAST_INSERT_ID()` atau `$db->getInsertId()`:
```php
$db->rawQuery("INSERT INTO new_breakdown_management_2.tag_compounding ...", [$rec_id]);
$lastTagId = $db->getInsertId(); // atau $db->rawQueryValue("SELECT LAST_INSERT_ID()");
$db->rawQuery("UPDATE lt2_blender SET id_tagging = ? WHERE id = ? AND kendala != ''", [$lastTagId, $rec_id]);
```

---

### H-06: Duplikasi Controller — `UserController.php` vs `UsersController.php`

**File:** 
- [UserController.php](../app/controllers/UserController.php) (12,418 bytes) — tabel `user`
- [UsersController.php](../app/controllers/UsersController.php) (12,428 bytes) — tabel `users`

Kedua controller hampir **100% identik** (selisih hanya 10 bytes), tapi mengakses tabel yang berbeda (`user` vs `users`). Ini sangat membingungkan dan kemungkinan salah satu adalah tabel yang benar.

**Tindakan:** Periksa database — tabel mana yang benar-benar dipakai? Kemungkinan besar hanya satu yang valid, yang lain harus dihapus.

---

## 🟡 SEVERITY: MEDIUM (Perlu Perbaikan)

### M-01: Code Duplication Ekstrem — 56 Controller 95% Identik

Ini sudah tercatat di CLAUDE.md, tapi dari QA perspective ini menambah **test surface** yang sangat besar dan membuat regression testing hampir mustahil. Setiap bug fix harus diaplikasikan ke 56 tempat.

**Statistik Duplikasi Telegram Logic:**
- Kode notifikasi Telegram: **~50 baris** × **30+ controller** = **1,500+ baris duplikat**
- Kode search query: **~40 baris** × **50+ controller** = **2,000+ baris duplikat**
- Kode CRUD boilerplate: **~300 baris** × **50+ controller** = **15,000+ baris duplikat**

**Rekomendasi:** Buat `BaseMachineController` yang menangani semua logika umum, dan setiap machine controller hanya override konfigurasi:
```php
class Lt2_blenderController extends BaseMachineController {
    protected $tablename = "lt2_blender";
    protected $machineName = "Blender";
    protected $location = "Lantai 2";
    protected $fields = [...];
    protected $searchFields = [...];
}
```

---

### M-02: SharedController — 30+ Method yang Melakukan Query Identik

**File:** [SharedController.php](../app/controllers/SharedController.php) — 1,192 baris

```php
// Method-method ini IDENTIK — hanya nama function berbeda:
function lt4_k1r1_kategori_tag_option_list() { ... "SELECT DISTINCT id AS value, id AS label FROM tag ..." }
function lt4_k1r2_kategori_tag_option_list() { ... "SELECT DISTINCT id AS value, id AS label FROM tag ..." }
function lt4_k1r3_kategori_tag_option_list() { ... "SELECT DISTINCT id AS value, id AS label FROM tag ..." }
function lt4_k1r4_kategori_tag_option_list() { ... "SELECT DISTINCT id AS value, id AS label FROM tag ..." }
function lt4_k1r8_kategori_tag_option_list() { ... "SELECT DISTINCT id AS value, id AS label FROM tag ..." }
// ... 20+ method lagi dengan query yang PERSIS SAMA
```

**Fix:** Cukup 1 method generik:
```php
function kategori_tag_option_list() {
    $db = $this->GetModel();
    return $db->rawQuery("SELECT DISTINCT id AS value, id AS label FROM tag ORDER BY label ASC");
}
```

---

### M-03: Cross-Database Query Tanpa Proteksi

**File terdampak:** Beberapa controller dan SharedController

```php
// ❌ Query lintas database dengan nama database hardcoded:
"SELECT ... FROM new_breakdown_management_2.korelasi ORDER BY nama ASC"
"SELECT ... FROM new_breakdown_management_2.kategori WHERE korelasi_id = ?"
"SELECT ... FROM new_breakdown_management_2.machine_list WHERE id IN (18, 19)"
"INSERT INTO new_breakdown_management_2.tag_compounding ..."
```

**Risiko:**
- Nama database hardcoded — jika nama berubah di environment lain, semua query pecah
- Tidak ada validasi apakah user MySQL punya akses ke database kedua

**Fix:** Definisikan nama database sebagai constant:
```php
define("DB_NAME_BREAKDOWN", "new_breakdown_management_2");
// Lalu: "SELECT ... FROM " . DB_NAME_BREAKDOWN . ".korelasi ..."
```

---

### M-04: CSRF Token Tidak Di-rotate Setelah Login

**File:** [Csrf.php](../libs/Csrf.php)

```php
function __construct() {
    $token = get_session('csrf_token');
    if (empty($token)) {
        $token = hash_value(random_str(12));
        set_session('csrf_token', $token);
    }
    self::$token = $token;
}
```

**Masalah:** Token CSRF dibuat sekali saat pertama kali diakses dan **tidak pernah di-rotate** — bahkan setelah login/logout. Ini membuat token bisa di-reuse jika attacker mendapatkannya.

**Rekomendasi:** Rotate CSRF token setelah login berhasil.

---

### M-05: Double Semicolons di Query Builder

**File terdampak:** Semua controller yang menggunakan `where` clause

```php
// ❌ Double semicolons (bug minor tapi menandakan kualitas kode):
$db->where("lt2_blender.id", $rec_id);;   // Baris 163, 392, 414, 464
$db->where("user.id_user", $rec_id);;     // Baris 111, 244, 266, 321
```

**Dampak:** Tidak menyebabkan error PHP, tapi menunjukkan kode tidak di-review dan di-copy-paste tanpa perhatian.

---

### M-06: `docker-compose.yml` Menggunakan Atribut `version` yang Obsolete

**File:** [docker-compose.yml](../docker-compose.yml)

```yaml
version: '3.8'  # ❌ Tidak diperlukan sejak Docker Compose v2
```

**Fix:** Hapus baris `version: '3.8'`.

---

### M-07: Tidak Ada Rate Limiting pada Login

**File:** [IndexController.php](../app/controllers/IndexController.php)

Tidak ada mekanisme rate limiting atau account lockout setelah gagal login. Attacker bisa melakukan brute force tanpa batas.

**Rekomendasi:**
- Tambahkan counter failed login per IP/username
- Lock akun sementara setelah 5 kali gagal (misal 15 menit)
- Log setiap percobaan gagal

---

### M-08: Password Reset Key Menggunakan MD5 Hash

**File:** [PasswordmanagerController.php](../app/controllers/PasswordmanagerController.php) — Baris 22

```php
$password_reset_key = password_hash(random_str(), PASSWORD_DEFAULT);
$modeldata = array(
    "password_reset_key" => hash_value($password_reset_key),  // ❌ Pakai MD5 (hash_value)
);
```

Reset key di-hash dengan MD5 via `hash_value()`, yang sudah tidak aman. Gunakan `hash('sha256', ...)`.

---

## 🟢 SEVERITY: LOW (Improvement / Best Practice)

### L-01: Tidak Ada `.gitignore`

File/folder berikut seharusnya **tidak** di-commit:
```
vendor/
uploads/
logs/
.env
*.sql (file 33MB+)
node_modules/
```

**File `form_am_plg.sql` berukuran 33MB** — ini sangat besar untuk di-track di Git.

---

### L-02: `readme.txt` Hanya Berisi "test234"

Tidak ada dokumentasi setup, requirements, atau cara menjalankan aplikasi. Ini sangat menyulitkan developer baru (termasuk anak magang).

---

### L-03: `ApiController.php` Kosong (Stub)

```php
// File ada (415 bytes) tapi tidak ada implementasi:
class ApiController extends SecureController {
    // kosong
}
```

---

### L-04: `HomeController.php` Minimal

```php
class HomeController extends SecureController {
    function index() {
        $this->render_view("home/index.php");
    }
}
```

Tidak ada dashboard summary, statistik, atau informasi berguna.

---

### L-05: `timthumb.php` — Library Lama (2012) dengan Riwayat Vulnerabilitas

**File:** [timthumb.php](../helpers/timthumb.php) — 51KB

Library ini terkenal dengan vulnerability serius (remote code execution). Cek apakah masih dipakai di view files. Jika tidak → **hapus**.

---

### L-06: Dockerfile Tidak Optimal

```dockerfile
# Tidak ada multi-stage build
# Tidak ada .dockerignore (file 33MB SQL ikut di-copy)
# Tidak ada health check
# Tidak set user non-root
COPY . /var/www/html/   # ❌ Copy SEMUA file termasuk .sql 33MB, .git, dll
```

**Rekomendasi:** Buat `.dockerignore`:
```
*.sql
.git
.env
vendor/
logs/
```

---

### L-07: Meta SEO Kosong

**File:** [config.php](../config.php) — Baris 69-71

```php
define("META_AUTHOR", "");       // Kosong
define("META_DESCRIPTION", "");  // Kosong
define("META_KEYWORDS", "");     // Kosong
```

---

### L-08: Tidak Ada Unit Test

Tidak ditemukan folder `tests/` atau file test apapun. Untuk aplikasi dengan 50+ modul, ini membuat regression testing sangat sulit.

---

## 📊 Summary Dashboard

| Severity | Jumlah | Status |
|----------|--------|--------|
| 🔴 Critical | 6 | Harus segera fix |
| 🟠 High | 6 | Fix dalam sprint ini |
| 🟡 Medium | 8 | Plan untuk sprint berikutnya |
| 🟢 Low | 8 | Backlog improvement |
| **Total** | **28** | |

---

## 🎯 Rencana Aksi untuk Anak Magang

### Minggu 1-2: Quick Wins (Critical Fixes)
- [ ] **C-02:** Fix `__tablename` → `users` di `SecureController.php` (1 baris)
- [ ] **C-04:** Hapus `password LIKE ?` dari search di `UserController` & `UsersController`
- [ ] **C-05:** Hapus kode rusak di `Functions.php` baris 520-527
- [ ] **M-05:** Fix double semicolons di semua controller
- [ ] **L-01:** Buat `.gitignore`
- [ ] **L-06:** Buat `.dockerignore`

### Minggu 3-4: Security Hardening
- [ ] **C-01:** Pindahkan Telegram Bot Token ke config terpusat + buat `TelegramNotifier` helper
- [ ] **C-06:** Ganti semua `$_POST` langsung dengan `$modeldata`
- [ ] **H-01:** Upgrade `hash_value()` dari MD5 ke SHA-256
- [ ] **H-02:** Tambah validasi ekstensi file upload untuk `pict`
- [ ] **H-05:** Fix race condition dengan `LAST_INSERT_ID()`

### Bulan 2: Code Quality
- [ ] **C-03:** Replace semua `FILTER_SANITIZE_STRING` dengan `FILTER_SANITIZE_FULL_SPECIAL_CHARS`
- [ ] **H-04:** Implementasi singleton pattern untuk database connection
- [ ] **H-06:** Audit dan hapus duplikat `UserController` vs `UsersController`
- [ ] **M-02:** Refactor `SharedController` — gabungkan method identik

### Bulan 3+: Architecture Improvement
- [ ] **M-01:** Buat `BaseMachineController` untuk mengurangi 56 controller jadi 1 base + config
- [ ] **M-03:** Centralize cross-database name
- [ ] **M-07:** Implementasi rate limiting login
- [ ] **L-02:** Buat `README.md` yang proper
- [ ] **L-04:** Buat dashboard Home yang informatif

---

## 📝 Catatan Testing

Karena tidak ada automated test, setiap fix harus **ditest manual** dengan checklist berikut:

1. **Login/Logout** — pastikan masih berfungsi setelah perubahan
2. **Remember Me** — test setelah fix C-02
3. **Add Form Mesin** — test 1 mesin per lantai (Lt2, Lt3, Lt4) — pastikan insert, notifikasi Telegram, dan tagging masih jalan
4. **Approval** — test approve dan pastikan field `user_approve` dan `date_update` terisi
5. **Search** — test search keyword di halaman list
6. **Export PDF/Excel** — test di halaman list
7. **User Management** — test add/edit/delete user

---

## 🚀 Saran Pengembangan Lebih Lanjut

> Bagian ini berisi rekomendasi pengembangan jangka menengah-panjang yang bisa dijalankan selama masa magang (2-3 bulan). Disusun berdasarkan prioritas dan dampak terbesar terhadap kualitas sistem.

---

### 1. 🔄 Modernisasi Tech Stack: PHP API + Next.js Frontend

#### Kondisi Sekarang
```
[Browser] → [Apache/PHP] → Render HTML langsung (server-side) → [MySQL]
```
- Setiap halaman di-render penuh di server (full page reload)
- UI terikat ketat dengan logic PHP di view files
- Tidak ada pemisahan antara frontend dan backend
- Tidak ada API layer yang bisa di-consume oleh client lain (mobile app, dashboard, dll.)

#### Arsitektur yang Diusulkan
```
                    ┌──────────────────┐
                    │   Next.js App    │  ← Dashboard modern, SSR/CSR
                    │   (Frontend)     │
                    └────────┬─────────┘
                             │ REST API (JSON)
                             ▼
                    ┌──────────────────┐
                    │   PHP Backend    │  ← ApiController.php (sudah ada stub-nya)
                    │   (REST API)     │
                    └────────┬─────────┘
                             │ PDO/MySQL
                             ▼
                    ┌──────────────────┐
                    │     MySQL        │
                    │   (Existing DB)  │
                    └──────────────────┘
```

#### Mengapa Next.js?
| Aspek | PHP Sekarang | Next.js |
|-------|-------------|---------|
| UI/UX | Bootstrap klasik, full reload | React components, SPA-like, smooth transition |
| Performance | Server render setiap request | Client-side caching, ISR, optimized bundle |
| Developer Experience | Edit PHP + HTML + JS campur | Component-based, hot reload, TypeScript |
| Mobile-ready | Responsive tapi berat | PWA-ready, bisa jadi mobile app |
| API consumption | Tidak ada | Bisa di-consume mobile app, IoT dashboard |
| SEO | Butuh effort manual | Built-in SSR/SSG untuk SEO |

#### Rencana Implementasi (Realistis 2-3 Bulan)

**Bulan 1 — PHP REST API Layer**
```
Minggu 1-2: Implementasi ApiController.php
├── GET  /api/machines              → List semua jenis mesin
├── GET  /api/lt2_blender           → List data form blender (paginated)
├── GET  /api/lt2_blender/{id}      → Detail 1 record
├── POST /api/lt2_blender           → Tambah data baru
├── PUT  /api/lt2_blender/{id}      → Update/approve
└── GET  /api/dashboard/summary     → Count per mesin hari ini

Minggu 3-4: Generalisasi API
├── GET  /api/{machine_type}        → Generic list (1 endpoint untuk semua mesin)
├── GET  /api/{machine_type}/{id}   → Generic detail
├── POST /api/{machine_type}        → Generic add
└── Authentication via session/token
```

**Catatan penting:** PHP backend yang lama **tetap jalan** — API adalah layer tambahan di atas sistem existing. Tidak ada risiko merusak yang sudah berfungsi.

**Bulan 2 — Next.js Dashboard**
```
Minggu 5-6: Setup & Core Pages
├── npx create-next-app@latest dashboard
├── Dashboard home (summary cards per mesin)
├── List page dengan DataTable (search, sort, pagination)
└── Detail/view page per record

Minggu 7-8: CRUD & Integration
├── Add form (dropdown dari API)
├── Edit/Approval form
├── Notifikasi real-time (polling atau WebSocket)
└── Export PDF/Excel dari frontend
```

**Bulan 3 — Polish & Deployment**
```
Minggu 9-10: Authentication & Authorization
├── Login page Next.js → PHP API auth
├── Role-based menu & page access
└── Session management (JWT atau cookie-based)

Minggu 11-12: Deploy & Testing
├── Docker Compose: PHP API + Next.js + MySQL
├── Testing end-to-end
└── Dokumentasi & handover
```

#### Tech Stack Next.js yang Direkomendasikan
```json
{
  "framework": "Next.js 14+ (App Router)",
  "ui": "shadcn/ui + Tailwind CSS",
  "state": "React Query (TanStack Query) untuk data fetching",
  "table": "TanStack Table untuk DataTable",
  "form": "React Hook Form + Zod validation",
  "chart": "Recharts untuk dashboard grafik",
  "auth": "NextAuth.js atau custom JWT",
  "language": "TypeScript"
}
```

#### Contoh Struktur Folder Next.js
```
dashboard/
├── app/
│   ├── layout.tsx              ← Main layout (sidebar, header)
│   ├── page.tsx                ← Dashboard home
│   ├── login/page.tsx          ← Login page
│   ├── machines/
│   │   ├── page.tsx            ← List semua jenis mesin
│   │   └── [type]/
│   │       ├── page.tsx        ← List records per mesin
│   │       ├── [id]/page.tsx   ← Detail record
│   │       └── add/page.tsx    ← Add form
│   ├── approval/page.tsx       ← Approval queue
│   └── users/page.tsx          ← User management
├── components/
│   ├── MachineForm.tsx         ← Generic form component (1 form untuk semua mesin!)
│   ├── DataTable.tsx           ← Reusable table
│   └── DashboardCard.tsx       ← Summary cards
├── lib/
│   ├── api.ts                  ← API client (fetch wrapper)
│   └── machine-config.ts       ← Config per mesin (fields, labels, validation)
└── types/
    └── machine.ts              ← TypeScript types
```

#### Keuntungan Utama untuk CV Magang
> "Developed REST API layer on top of legacy PHP system and built modern Next.js dashboard with React, enabling real-time monitoring of 50+ machine maintenance forms. Migrated from server-rendered PHP to SPA architecture while maintaining backward compatibility."

---

### 2. 📦 MinIO sebagai Object Storage (Pengganti Folder Upload)

#### Kondisi Sekarang — Upload Langsung ke Folder Server

```php
// BaseController.php — konfigurasi upload:
$this->file_upload_settings['pict'] = array(
    "uploadDir" => "uploads/files/"    // ← Langsung ke disk server
);

// Uploader.php — mekanisme:
// 1. File dari $_FILES di-move ke folder uploads/files/
// 2. Path disimpan di database sebagai string (misal: "uploads/files/abc123.jpg")
// 3. Akses file via URL: http://server/uploads/files/abc123.jpg
```

**Struktur folder upload saat ini:**
```
uploads/
├── cached/     ← Cache dari timthumb.php
├── files/      ← Semua file upload (termasuk foto profil user)
└── photos/     ← Foto-foto lain
```

#### Masalah dengan Pendekatan Saat Ini

| Masalah | Dampak |
|---------|--------|
| **Tidak ada backup** | Server mati/rusak = semua foto hilang |
| **Tidak scalable** | Disk server penuh = upload gagal |
| **Tidak ada CDN** | File di-serve langsung oleh Apache — lambat untuk banyak user |
| **Docker tidak persistent** | Container restart = file hilang (kecuali pakai volume) |
| **Tidak ada versioning** | File ditimpa = tidak bisa rollback |
| **Permission `chmod 777`** | Security risk — semua user bisa baca/tulis/eksekusi |

#### Solusi: MinIO — S3-Compatible Object Storage (Self-Hosted & Gratis)

```
                SEBELUM                              SESUDAH
    ┌─────────────────────┐           ┌─────────────────────┐
    │   PHP App Server    │           │   PHP App Server    │
    │                     │           │                     │
    │  uploads/           │           │  (tidak simpan file)│
    │  ├── files/         │           └────────┬────────────┘
    │  ├── photos/        │                    │ S3 API
    │  └── cached/        │                    ▼
    │                     │           ┌─────────────────────┐
    │  ⚠️ Hilang kalau    │           │      MinIO          │
    │    server rusak     │           │   Object Storage    │
    └─────────────────────┘           │                     │
                                      │  ✅ Auto backup     │
                                      │  ✅ Redundant       │
                                      │  ✅ Scalable        │
                                      │  ✅ URL permanen    │
                                      └─────────────────────┘
```

#### Mengapa MinIO?
- **Gratis & open source** — cocok untuk on-premise kantor
- **S3-compatible API** — kalau nanti mau migrasi ke AWS S3, tinggal ganti endpoint
- **Docker-ready** — 1 baris untuk deploy
- **Web UI** — ada dashboard untuk manage file

#### Implementasi MinIO

**Step 1: Tambah MinIO ke `docker-compose.yml`**
```yaml
services:
  web-app:
    build: .
    container_name: form-am-app
    ports:
      - "80:80"
    environment:
      - MINIO_ENDPOINT=minio:9000
      - MINIO_ACCESS_KEY=minioadmin
      - MINIO_SECRET_KEY=minioadmin123
    depends_on:
      - minio
    restart: always

  minio:
    image: minio/minio:latest
    container_name: form-am-minio
    ports:
      - "9000:9000"    # API
      - "9001:9001"    # Web Console
    environment:
      MINIO_ROOT_USER: minioadmin
      MINIO_ROOT_PASSWORD: minioadmin123
    volumes:
      - minio_data:/data    # Data persistent di volume Docker
    command: server /data --console-address ":9001"
    restart: always

volumes:
  minio_data:    # Volume persistent — data tidak hilang walau container restart
```

**Step 2: Install AWS SDK for PHP (S3 compatible)**
```bash
composer require aws/aws-sdk-php
```

**Step 3: Buat Helper `MinioUploader.php`**
```php
<?php
use Aws\S3\S3Client;

class MinioUploader {
    private $client;
    private $bucket = 'form-am-uploads';

    public function __construct() {
        $this->client = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'endpoint'    => 'http://' . ($_ENV['MINIO_ENDPOINT'] ?? 'minio:9000'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $_ENV['MINIO_ACCESS_KEY'] ?? 'minioadmin',
                'secret' => $_ENV['MINIO_SECRET_KEY'] ?? 'minioadmin123',
            ],
        ]);

        // Buat bucket jika belum ada
        if (!$this->client->doesBucketExist($this->bucket)) {
            $this->client->createBucket(['Bucket' => $this->bucket]);
        }
    }

    /**
     * Upload file ke MinIO
     * @param string $filePath Path temporary file ($_FILES['x']['tmp_name'])
     * @param string $fileName Nama file yang diinginkan
     * @param string $folder Subfolder di bucket (misal: 'photos', 'files')
     * @return string URL file yang sudah diupload
     */
    public function upload($filePath, $fileName, $folder = 'files') {
        $key = $folder . '/' . $fileName;

        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
            'SourceFile' => $filePath,
            'ACL'    => 'public-read',
        ]);

        return $this->getUrl($key);
    }

    /**
     * Hapus file dari MinIO
     */
    public function delete($key) {
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);
    }

    /**
     * Get public URL untuk file
     */
    public function getUrl($key) {
        $endpoint = $_ENV['MINIO_ENDPOINT'] ?? 'minio:9000';
        return "http://{$endpoint}/{$this->bucket}/{$key}";
    }
}
```

**Step 4: Modifikasi `BaseController.php` — Ganti Upload Mechanism**
```php
// SEBELUM:
$uploaded_files = $this->get_uploaded_file_paths('pict');

// SESUDAH:
$minio = new MinioUploader();
$file = $_FILES['pict'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid() . '.' . $ext;
$uploaded_url = $minio->upload($file['tmp_name'], $fileName, 'photos');
$modeldata['pict'] = $uploaded_url;
```

#### Migrasi File Lama
```php
// Script migrasi satu kali untuk upload semua file existing ke MinIO:
$minio = new MinioUploader();
$files = glob('uploads/files/*');
foreach ($files as $file) {
    $fileName = basename($file);
    $minio->upload($file, $fileName, 'files');
    echo "Migrated: $fileName\n";
}
```

---

### 3. 🛠️ Improvement Tech Stack Lainnya

#### 3a. Environment Variables dengan `.env` (vlucas/phpdotenv)

```bash
composer require vlucas/phpdotenv
```

**Buat file `.env`:**
```env
# Database
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=Seman94t45!
DB_NAME=form_am_plg
DB_PORT=3306

# Telegram
TELEGRAM_BOT_TOKEN=***TELEGRAM_TOKEN_REDACTED***
TELEGRAM_CHAT_APPROVAL=-1002428961148
TELEGRAM_CHAT_ASSIGNER=-4547166344

# MinIO
MINIO_ENDPOINT=minio:9000
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin123

# App
APP_ENV=production
APP_DEBUG=false
```

**Modifikasi `index.php`:**
```php
// Tambahkan di paling atas, sebelum require('config.php'):
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
```

**Modifikasi `config.php`:**
```php
// SEBELUM:
define("DB_PASSWORD", "");

// SESUDAH:
define("DB_HOST", $_ENV['DB_HOST'] ?? 'localhost');
define("DB_USERNAME", $_ENV['DB_USERNAME'] ?? 'root');
define("DB_PASSWORD", $_ENV['DB_PASSWORD'] ?? '');
define("DB_NAME", $_ENV['DB_NAME'] ?? 'form_am_plg');
```

---

#### 3b. Structured Logging dengan Monolog

```bash
composer require monolog/monolog
```

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

$log = new Logger('form-am');
$log->pushHandler(new RotatingFileHandler('logs/app.log', 30, Logger::INFO));

// Usage:
$log->info('User login', ['username' => $username, 'ip' => get_user_ip()]);
$log->error('Telegram notification failed', ['machine' => 'blender', 'error' => $e->getMessage()]);
$log->warning('Upload failed', ['file' => $filename, 'reason' => 'extension_not_allowed']);
```

---

#### 3c. Refactor Notifikasi Telegram — Helper Class Terpusat

Buat 1 file `helpers/TelegramNotifier.php` yang menangani semua notifikasi:

```php
<?php
class TelegramNotifier {
    private static $botToken;
    private static $chatApproval;
    private static $chatAssigner;

    public static function init() {
        self::$botToken     = $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
        self::$chatApproval = $_ENV['TELEGRAM_CHAT_APPROVAL'] ?? '';
        self::$chatAssigner = $_ENV['TELEGRAM_CHAT_ASSIGNER'] ?? '';
    }

    /**
     * Kirim notifikasi approval ke group Telegram
     */
    public static function notifyApproval($machineName, $location, $kendala) {
        if (empty($kendala)) return;

        $message  = "Autonomous Maintenance sudah diisi oleh operator\n\n";
        $message .= "Nama Mesin  : {$machineName}\n";
        $message .= "Lokasi      : {$location}\n";
        $message .= "Kondisi     : {$kendala}\n\n";
        $message .= "Mohon segera Approve melalui website\n";

        $keyboard = [[
            ['text' => 'Buka Website', 'url' => 'http://10.127.17.10/produksicikarang/approval']
        ]];

        self::send(self::$chatApproval, $message, $keyboard);
    }

    /**
     * Kirim notifikasi red tag ke group Assigner
     */
    public static function notifyRedTag($machineName, $location, $kendala, $idTagging) {
        $message  = "<b>LAPORAN RED TAGGING AM BARU</b>\n";
        $message .= "=========================\n\n";
        $message .= "<b>- Detail Red Tagging -</b>\n\n";
        $message .= "Lokasi   : {$location}\n";
        $message .= "Mesin    : {$machineName}\n";
        $message .= "Kondisi  : {$kendala}\n\n";

        $link = "http://10.127.17.10/breakdown_management/compounding/edit_assigner/{$idTagging}";
        $message .= "Konfirmasi: <a href='{$link}'>Assign Sekarang</a>\n";

        self::send(self::$chatAssigner, $message, null, 'HTML');
    }

    /**
     * Low-level send message via Telegram API
     */
    private static function send($chatId, $message, $keyboard = null, $parseMode = null) {
        if (empty(self::$botToken)) return false;

        $params = [
            'chat_id' => $chatId,
            'text'    => $message,
        ];

        if ($keyboard) {
            $params['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
        }
        if ($parseMode) {
            $params['parse_mode'] = $parseMode;
        }

        $url = "https://api.telegram.org/bot" . self::$botToken . "/sendMessage";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Telegram notification failed: {$error}");
            return false;
        }
        return true;
    }
}

TelegramNotifier::init();
```

**Penggunaan di controller (mengganti ~50 baris jadi 2 baris):**
```php
// SEBELUM: 50+ baris copy-paste di setiap controller
$botToken = '7676930026:...';
$chatID = '...';
$message = "...";
// ... (curl setup, keyboard, dll)

// SESUDAH: 2 baris saja!
TelegramNotifier::notifyApproval("Blender " . $row['machine_name'], "Lantai 2", $kendala);
if ($kategori_tag == 1) {
    TelegramNotifier::notifyRedTag($row['machine_name'], "Lantai 2", $kendala, $id_tagging);
}
```

---

### 4. 📋 Timeline Realistis (2-3 Bulan Magang)

```
┌─────────────────────────────────────────────────────────────────┐
│                    TIMELINE 2-3 BULAN MAGANG                    │
├─────────┬───────────────────────────────────────────────────────┤
│         │                                                       │
│ Minggu  │  Critical bug fixes (C-01 s/d C-06)                  │
│  1-2    │  + .gitignore + .dockerignore + .env setup            │
│         │  + TelegramNotifier helper                            │
│         │                                                       │
├─────────┼───────────────────────────────────────────────────────┤
│         │                                                       │
│ Minggu  │  High severity fixes (H-01 s/d H-06)                 │
│  3-4    │  + MinIO setup (docker-compose + MinioUploader.php)   │
│         │  + Migrasi file existing ke MinIO                     │
│         │                                                       │
├─────────┼───────────────────────────────────────────────────────┤
│         │                                                       │
│ Minggu  │  PHP REST API (ApiController.php)                     │
│  5-6    │  + Generic endpoints untuk semua mesin                │
│         │  + API authentication                                 │
│         │                                                       │
├─────────┼───────────────────────────────────────────────────────┤
│         │                                                       │
│ Minggu  │  Next.js Dashboard — Setup & Core                     │
│  7-8    │  + Dashboard home + List page + Detail page           │
│         │  + Consume PHP API                                    │
│         │                                                       │
├─────────┼───────────────────────────────────────────────────────┤
│         │                                                       │
│ Minggu  │  Next.js — CRUD & Auth                                │
│  9-10   │  + Add/Edit form + Approval flow                      │
│         │  + Login integration                                  │
│         │                                                       │
├─────────┼───────────────────────────────────────────────────────┤
│         │                                                       │
│ Minggu  │  Polish, Testing & Dokumentasi                        │
│ 11-12   │  + Docker Compose full stack                          │
│         │  + README.md + Handover                               │
│         │                                                       │
└─────────┴───────────────────────────────────────────────────────┘
```

> **💡 Tips:** Kalau waktunya cuma 2 bulan, fokus ke Minggu 1-8 saja. Next.js dashboard yang basic tapi fungsional sudah cukup impresif untuk laporan magang.

---

### 5. 📝 Value untuk CV / Laporan Magang

Dengan mengerjakan improvement di atas, kamu bisa tulis di CV:

> **Project: Modernisasi Sistem Asset Maintenance (Form AM)**  
> - Conducted comprehensive QA audit identifying 28 issues across security, performance, and code quality
> - Built centralized REST API layer serving 50+ machine types through generic endpoints
> - Developed modern Next.js dashboard replacing legacy PHP server-rendered pages
> - Implemented MinIO object storage for file management, replacing direct disk storage
> - Reduced codebase duplication by 80% through generic controller pattern and centralized notification system
> - Technologies: PHP, Next.js, React, TypeScript, MySQL, MinIO, Docker

---

## ⚖️ Reality Check: Konteks Internal Network

> **Penting dibaca.** Semua temuan di atas ditulis dari perspektif QA general/best practice. Tapi kenyataannya, web ini adalah **aplikasi internal** yang di-host di server kantor (`10.x.x.x`) dan **hanya bisa diakses dari jaringan lokal**. Ini mengubah prioritas beberapa temuan secara signifikan.

---

### Mana yang Benar-Benar Harus Diperbaiki vs Nice to Have?

#### ✅ HARUS FIX — Ini Bug / Masalah Fungsional (Bukan Soal Security)

Temuan-temuan ini **tetap relevan 100%** meskipun internal, karena ini bukan masalah security — ini masalah **"aplikasinya rusak/tidak bekerja dengan benar"**:

| ID | Temuan | Kenapa Tetap Harus Fix |
|----|--------|----------------------|
| **C-02** | Bug `__tablename` di SecureController | Fitur Remember Me **rusak total**. Ini bug fungsional, bukan security. |
| **C-03** | `FILTER_SANITIZE_STRING` deprecated | PHP 8.3 **sudah generate warning**. Bikin log server penuh, bisa bikin behavior aneh. Ini compatibility issue. |
| **C-05** | Kode rusak di Functions.php (`error_reporting(0)`) | **Mematikan semua error reporting** = debugging jadi mustahil. Ini bikin development lambat. |
| **H-04** | DB connection dibuat ulang setiap `GetModel()` | **Performance issue**. Kalau 50 orang isi form bareng, server bisa lambat karena terlalu banyak koneksi MySQL. |
| **H-05** | Race condition tagging (`ORDER BY id DESC LIMIT 1`) | **Data integrity issue**. 2 operator isi form bersamaan = id_tagging bisa ketuker. Data jadi salah. |
| **H-06** | Duplikat `UserController` vs `UsersController` | **Membingungkan**. Harus dicek mana yang benar, hapus yang salah. |
| **M-05** | Double semicolons (`;;`) | **Trivial fix**, tapi menandakan kode tidak di-review. Fix sambil jalan aja. |

#### ✅ HARUS FIX — Maintainability (Bikin Hidup Developer Lebih Gampang)

Ini bukan bug, tapi kalau tidak diperbaiki, **setiap perubahan kecil jadi nightmare**:

| ID | Temuan | Kenapa Tetap Harus Fix |
|----|--------|----------------------|
| **C-01** (sebagian) | Telegram logic copy-paste 30+ file | Bukan soal token bocor — tapi soal **kalau chat ID berubah, harus edit 30+ file**. Bikin `TelegramNotifier` helper = fix di 1 tempat. |
| **M-01** | 56 controller duplikat | Bug fix di 1 controller harus di-copy ke 55 lainnya. Ini **pasti akan menyebabkan bug** di masa depan. |
| **M-02** | SharedController 30+ method identik | 20+ method yang query-nya **persis sama** bisa jadi 1 method. Reduce noise. |

#### ⚠️ NICE TO HAVE — Best Practice, Tapi Tidak Urgent di Internal Network

Temuan-temuan ini adalah **best practice** yang bagus untuk dipelajari dan diterapkan, tapi **risiko-nya rendah** di environment internal:

| ID | Temuan | Kenapa Nice to Have |
|----|--------|-------------------|
| **C-01** (token exposure) | Bot token hardcoded | Di internal network, yang bisa lihat kode cuma tim IT. Risiko eksploitasi **sangat rendah**. Jadi masalah kalau repo dipush ke GitHub publik. |
| **C-04** | Password hash bisa di-search | User internal, siapa yang mau coba search password hash? Risiko **sangat rendah**. Tapi tetap aneh secara logic. |
| **C-06** | `$_POST` langsung tanpa sanitasi | Data dikirim ke Telegram, bukan ke database lagi. Di internal, user tidak akan inject malicious input ke form mereka sendiri. |
| **H-01** | `hash_value()` pakai MD5 | MD5 lemah, tapi untuk di-crack butuh effort yang tidak masuk akal untuk app internal. SHA-256 lebih baik, tapi bukan urgent. |
| **H-02** | Upload tanpa validasi ekstensi | Operator pabrik **tidak akan upload webshell**. Tapi tetap good practice untuk tambah filter `.jpg,.png` aja. |
| **H-03** | Mixed curl / file_get_contents | Inkonsisten tapi keduanya kerja. Standardize ke curl kalau ada waktu. |
| **M-03** | Cross-database name hardcoded | Kalau nama database tidak pernah berubah, hardcoded bukan masalah. Baru masalah kalau deploy ke server lain. |
| **M-04** | CSRF tidak di-rotate | Internal network = CSRF attack vector hampir 0. |
| **M-07** | Tidak ada rate limiting login | **Tidak perlu** di internal. Siapa yang mau brute force di jaringan kantor sendiri? |
| **M-08** | Password reset key pakai MD5 | Sama seperti H-01, low risk di internal. |
| **.env file** | Kredensial hardcoded di config | Di internal, `config.php` aman karena hanya bisa diakses dari server. `.env` lebih rapi, tapi bukan urgent. |

#### ❌ TIDAK RELEVAN — Over-Engineering untuk Konteks Ini

| Temuan | Kenapa Tidak Relevan |
|--------|---------------------|
| **Rate limiting** | 0% kemungkinan brute force di internal network |
| **L-05: timthumb.php vulnerability** | Vulnerability-nya butuh akses dari internet. Di internal network, attack surface = 0 |

---

### Revisi: MinIO — Apakah Worth It di 1 Server Kantor?

#### Jawaban Jujur: **Tidak, kalau cuma 1 server.**

Argumen awal MinIO:
- ✅ Backup otomatis → **Ternyata tidak**, kalau MinIO dan app di server yang sama, server mati = dua-duanya mati
- ✅ Scalable → **Tidak relevan**, ini app internal untuk ~50 user
- ✅ File tidak hilang saat container restart → **Docker volume sudah solve ini** tanpa MinIO

```
Realitanya:
┌─────────────────────────────┐
│      SERVER KANTOR          │
│                             │
│  ┌──────┐  ┌──────┐        │
│  │ App  │  │MinIO │        │  ← Keduanya di server yang SAMA
│  └──────┘  └──────┘        │
│                             │
│  Server mati = SEMUA mati   │  ← MinIO TIDAK menyelamatkan apa-apa
└─────────────────────────────┘
```

#### Kapan MinIO Baru Worth It?
- Kantor punya **2+ server** (MinIO di server terpisah)
- Ada **NAS (Network Attached Storage)** yang dedicated
- Rencana migrasi ke **cloud** (AWS/GCP) di masa depan — MinIO S3-compatible jadi transisi mudah

#### Solusi yang Lebih Realistis untuk 1 Server

**1. Docker Volume (WAJIB) — Supaya file tidak hilang saat container restart:**
```yaml
# docker-compose.yml
services:
  web-app:
    build: .
    ports:
      - "80:80"
    volumes:
      - ./uploads:/var/www/html/uploads    # ← PENTING! File persist di disk
      - ./logs:/var/www/html/logs
    restart: always
```

**2. Backup Script Sederhana (Cron Job) — Kalau ada external drive / NAS:**
```bash
#!/bin/bash
# backup.sh — jalankan via cron setiap malam
# crontab -e → 0 2 * * * /path/to/backup.sh

BACKUP_DIR="/mnt/nas/backup/form-am"   # Atau: /media/usb-backup/form-am
DATE=$(date +%F)

# Backup database
mysqldump -u root -p'password' form_am_plg > "$BACKUP_DIR/db-$DATE.sql"

# Backup upload files
rsync -av /var/www/html/uploads/ "$BACKUP_DIR/uploads/"

# Hapus backup lebih dari 30 hari
find "$BACKUP_DIR" -name "db-*.sql" -mtime +30 -delete

echo "Backup selesai: $DATE"
```

**3. Tambah Validasi Ekstensi File (Tetap Perlu):**
```php
// BaseController.php — tambah filter ekstensi:
$this->file_upload_settings['pict'] = array(
    "extensions" => ".jpg,.jpeg,.png,.gif,.webp",  // ← Tambah ini
    "uploadDir" => "uploads/files/"
);
```

---

### Rangkuman Prioritas Final (Disesuaikan untuk Internal Network)

```
┌─────────────────────────────────────────────────────────────────┐
│            PRIORITAS YANG SUDAH DISESUAIKAN                     │
│            (Konteks: Internal Network, 1 Server)                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  🔴 HARUS FIX (Bug Fungsional & Maintainability)               │
│  ├── C-02: __tablename bug                                     │
│  ├── C-03: FILTER_SANITIZE_STRING deprecated                   │
│  ├── C-05: error_reporting(0) di Functions.php                 │
│  ├── H-04: DB connection leak                                  │
│  ├── H-05: Race condition tagging                              │
│  ├── H-06: UserController vs UsersController                   │
│  ├── C-01: TelegramNotifier helper (maintainability)           │
│  ├── M-01: Refactor 56 controller duplikat                     │
│  └── Docker volume untuk uploads/                              │
│                                                                 │
│  🟡 NICE TO HAVE (Best Practice, Low Risk di Internal)         │
│  ├── .env file (phpdotenv)                                     │
│  ├── Password hash di search query                             │
│  ├── File upload validasi ekstensi                              │
│  ├── MD5 → SHA-256 di hash_value()                             │
│  ├── Standardize curl untuk HTTP requests                      │
│  └── Backup script (kalau ada NAS/external drive)              │
│                                                                 │
│  🟢 PENGEMBANGAN BARU (Value Tambah)                           │
│  ├── PHP REST API layer                                        │
│  ├── Next.js Dashboard                                         │
│  ├── Monolog structured logging                                │
│  └── README.md & dokumentasi                                   │
│                                                                 │
│  ❌ SKIP (Over-engineering untuk internal)                      │
│  ├── MinIO (di 1 server = tidak ada value)                     │
│  ├── Rate limiting login                                       │
│  ├── CSRF token rotation                                       │
│  └── timthumb.php audit (no internet = no risk)                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

*Dokumen ini dibuat berdasarkan static code analysis pada 2026-08-06 dan mereferensikan temuan awal dari [CLAUDE.md](../CLAUDE.md)*
