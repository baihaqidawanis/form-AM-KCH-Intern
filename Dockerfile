# 1. Gunakan "Bahan Baku" image PHP resmi yang sudah ada Apache-nya
FROM php:8.3-apache

# 2. Instal ekstensi PHP untuk koneksi ke Database (PENTING!)
# Karena kamu pakai DB eksternal, container tetap butuh driver untuk ngobrol ke DB-nya.
# Driver MySQL *dan* Postgres dua-duanya di-install -- app sekarang default-nya
# Postgres (DB_TYPE=pgsql di .env), tapi production belum dimigrasi dan masih
# MySQL, jadi image ini harus bisa jalan ke DB tipe manapun sesuai .env
# (lihat DOCS_MD/DEPLOYMENT.md). `zip` wajib buat export Excel (system/BaseView.php).
RUN apt-get update && apt-get install -y libpq-dev libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql pgsql zip \
    && a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 3. Copy semua file project PHP kamu dari laptop ke dalam container
# Folder /var/www/html/ adalah folder standar Apache untuk menaruh web
COPY . /var/www/html/

# 4. Beri tahu Docker bahwa container ini akan mendengarkan port 80 (port web)
EXPOSE 80