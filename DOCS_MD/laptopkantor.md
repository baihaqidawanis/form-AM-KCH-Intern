# Deploy ke Server dari Laptop Kantor (Network Share)

Panduan buat laptop kantor yang konek langsung ke folder server lewat network
share (`\\ip-server\form-am-v2`), dipakai buat narik update kode terbaru dari
GitHub ke server. **Laptop ini cuma butuh Git ke-install** — gak perlu PHP,
Composer, atau XAMPP, karena:
- `vendor/` (dependency PHP) udah ikut di-commit ke GitHub, jadi tinggal ke-tarik pas `git pull`, gak perlu `composer install`.
- Program PHP-nya sendiri gak pernah dijalanin dari laptop ini — yang beneran ngejalanin Apache/PHP itu **server**-nya. Laptop cuma numpang nulis file lewat network, git-nya jalan di laptop tapi hasil tulisannya nyantol ke server.

---

## 1. Konek ke folder server (`pushd`)

```cmd
pushd \\ip-server\form-am-v2
```

**Kenapa gak bisa langsung `cd \\ip-server\...`?** Karena `cmd.exe` gak
support UNC path (`\\...`) sebagai current directory — bakal error "CMD does
not support UNC paths as current directory". `pushd` akalin ini dengan cara
otomatis mapping path itu ke drive letter sementara (`Z:`, `Y:`, dst — huruf
apa yang dikasih itu random/tergantung yang lagi nganggur, gak masalah beda-beda tiap sesi).

Setelah `pushd`, kamu otomatis pindah ke drive itu (misal `Z:\`). Itu **BUKAN**
folder lokal di laptop — itu representasi/alias ke folder di server. Semua
yang kamu tulis/hapus di situ langsung ngefek ke server, bukan disimpen lokal.

### Cek drive itu beneran nunjuk ke server yang bener
```cmd
net use Z:
```
(ganti `Z:` sesuai huruf yang kamu dapet) — nunjukin `\\ip-server\form-am-v2`
kalau bener.

### Balik ke folder awal / lepasin drive
```cmd
popd
```
Cuma jalan di sesi CMD yang SAMA yang tadi jalanin `pushd`. Kalau CMD-nya
udah ketutup duluan, `popd` di sesi baru gak akan "inget" drive lama itu —
gak masalah, biarin aja (gak bahaya), atau bersihin manual:
```cmd
net use Z: /delete
```
(kerja dari sesi CMD manapun, gak perlu connect ulang dulu)

---

## 2. Update kode terbaru (`git pull`)

Kalau folder server itu **udah pernah** di-clone sebelumnya (ada folder
`.git` di dalemnya), cukup:
```cmd
git pull
```
Ini narik semua perubahan terbaru dari GitHub, termasuk `vendor/` (gak perlu
`composer install` lagi).

`.env` (kredensial database) **gak akan pernah ke-timpa/conflict** oleh
`git pull` — file itu sengaja gak di-track git (`.gitignore`), jadi Git
sama sekali gak "lihat"/ngurusin file itu.

---

## 3. Kalau perlu clone dari nol (folder kosong / mau install baru)

```cmd
git clone https://github.com/baihaqidawanis/form-AM-KCH-Intern.git .
```

**⚠️ Titik `.` di akhir WAJIB ada (dengan spasi sebelumnya)** — itu artinya
"clone LANGSUNG ke folder yang lagi saya pijak sekarang". Kalau titiknya
kelupaan, Git malah bikin folder BARU di dalam situ (namanya sesuai nama
repo, `form-AM-KCH-Intern`) — jadinya nested/folder di dalam folder.

**Kalau folder tujuannya belum kosong, `git clone` bakal GAGAL** dengan
error:
```cmd
fatal: destination path '.' already exists and is not an empty directory
```
Harus dikosongin dulu SEBELUM clone.

### Cara ngosongin folder dengan benar
```cmd
dir /a
```
Pakai `/a` (bukan `dir` polos) karena itu nampilin SEMUA file termasuk yang
hidden — sering ketinggalan folder `.git` sisa percobaan sebelumnya, atau
`$RECYCLE.BIN` (muncul otomatis abis delete lewat File Explorer), yang bikin
folder keliatan kosong padahal enggak (di mata Git).

Hapus semuanya (boleh manual lewat File Explorer, boleh command):
```cmd
del /Q *.*
for /D %i in (*) do rmdir /S /Q "%i"
```

Kalau masih ada folder `.git` nyangkut abis itu, hapus manual:
```cmd
rmdir /S /Q .git
```

Ulangin `dir /a` sampai bener-bener kosong (gak ada satu baris pun), baru
clone ulang.

**⚠️ Sebelum hapus semua, amankan dulu 2 hal yang BUKAN dari git** (gak
bisa didapetin lagi kalau kehapus):
```cmd
mkdir C:\backup-form-am
copy .env C:\backup-form-am\
xcopy uploads C:\backup-form-am\uploads\ /E /I
```
Balikin lagi setelah clone selesai:
```cmd
copy C:\backup-form-am\.env .
xcopy C:\backup-form-am\uploads uploads\ /E /I
```

---

## 4. Setup `.env` (kalau belum ada / abis clone fresh)

```cmd
copy .env.example .env
```
Terus edit `.env` (notepad) isi kredensial database yang bener
(`DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_NAME`, `DB_PORT`).

---

## 5. Cara cek isi folder (`dir`)

CMD gak punya `ls` (itu perintah Linux/Mac) — padanannya:
```cmd
dir
```
Cek posisi kamu sekarang (path lengkap):
```cmd
cd
```
(ketik `cd` doang tanpa argumen apa-apa)

---

## 🚀 Ringkasan alur lengkap (Update Rutin)

```cmd
pushd \\ip-server\form-am-v2
git pull
popd
```
Itu doang buat update rutin — 3 baris, gak perlu mikirin composer/PHP sama sekali.

---

## 🚀 Ringkasan alur lengkap (Setup Pertama Kali / Clone Fresh)

```cmd
pushd \\ip-server\form-am-v2
dir /a
REM (kosongin folder dulu kalau masih ada isi lama -- lihat bagian 3)
git clone https://github.com/baihaqidawanis/form-AM-KCH-Intern.git .
copy .env.example .env
REM edit .env pakai notepad, isi kredensial DB
popd
```
