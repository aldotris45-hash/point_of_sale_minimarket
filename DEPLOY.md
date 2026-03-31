# 🚀 PANDUAN DEPLOY KE VPS — Step by Step (Untuk Pemula)

Panduan ini dibuat **sangat detail** agar kamu yang masih awam bisa mengikuti dari nol sampai website Point of Sale Minimarket kamu bisa diakses online.

---

## 📋 DAFTAR ISI

1. [Persiapan Sebelum Deploy](#1-persiapan-sebelum-deploy)
2. [Beli VPS & Domain](#2-beli-vps--domain)
3. [Login ke VPS via SSH](#3-login-ke-vps-via-ssh)
4. [Install Software di VPS](#4-install-software-di-vps)
5. [Setup Database MySQL](#5-setup-database-mysql)
6. [Upload Project ke VPS via Git](#6-upload-project-ke-vps-via-git)
7. [Konfigurasi Laravel di VPS](#7-konfigurasi-laravel-di-vps)
8. [Setup Nginx (Web Server)](#8-setup-nginx-web-server)
9. [Setup SSL (HTTPS Gratis)](#9-setup-ssl-https-gratis)
10. [Setup Queue Worker (Supervisor)](#10-setup-queue-worker-supervisor)
11. [Tips Maintenance & Update](#11-tips-maintenance--update)
12. [Troubleshooting](#12-troubleshooting)

---

## 1. PERSIAPAN SEBELUM DEPLOY

### 1.1. Pastikan Kode Sudah di GitHub

Sebelum deploy, pastikan semua kode sudah di-push ke GitHub:

```bash
# Di laptop kamu (terminal Laragon)
cd C:\Users\nwlen\Documents\point_of_sale_minimarket

# Cek status
git status

# Tambah semua file baru
git add .

# Commit
git commit -m "Siap deploy ke VPS"

# Push ke GitHub
git push origin main
```

### 1.2. Build Assets (CSS/JS) di Laptop

```bash
# Di laptop kamu (terminal Laragon)
npm install
npm run build
```

Ini akan menghasilkan folder `public/build/` yang berisi file CSS dan JS yang sudah di-compile.

**PENTING:** Commit dan push folder `public/build/` juga:
```bash
git add public/build/
git commit -m "Build assets untuk production"
git push origin main
```

### 1.3. Yang Perlu Kamu Siapkan

| Item | Keterangan |
|------|-----------|
| VPS | Ubuntu 22.04/24.04 (minimal RAM 1GB) |
| Domain | Contoh: `pos-minimarket.com` (opsional, bisa pakai IP dulu) |
| Akun GitHub | Untuk menarik kode ke VPS |
| Midtrans Keys | Client Key & Server Key (jika pakai QRIS) |

---

## 2. BELI VPS & DOMAIN

### Rekomendasi Provider VPS Indonesia:
- **IDCloudHost** — mulai Rp 35.000/bulan
- **Niagahoster VPS** — mulai Rp 50.000/bulan
- **DigitalOcean** — mulai $6/bulan (pakai kartu kredit)
- **Contabo** — mulai $4.99/bulan (murah, dari Jerman)

### Spesifikasi Minimal:
- **OS:** Ubuntu 22.04 LTS atau 24.04 LTS
- **RAM:** 1 GB (2 GB lebih baik)
- **Storage:** 20 GB SSD
- **Bandwidth:** Unlimited

### Beli Domain (Opsional):
- **Niagahoster** / **Domainesia** / **Namecheap**
- Harga mulai Rp 15.000/tahun untuk `.my.id`

> **💡 Tip:** Untuk belajar, kamu bisa pakai IP VPS langsung tanpa domain dulu.

---

## 3. LOGIN KE VPS VIA SSH

### 3.1. Install Aplikasi SSH di Laptop

Download dan install **PuTTY** atau pakai **Windows Terminal**:
- PuTTY: https://www.putty.org/
- Atau langsung pakai CMD/PowerShell di Windows

### 3.2. Login ke VPS

Setelah beli VPS, kamu akan dapat:
- **IP Address** — contoh: `103.150.100.50`
- **Username** — biasanya `root`
- **Password** — dari provider

```bash
# Buka CMD atau PowerShell di laptop
ssh root@103.150.100.50
```

Ketik `yes` kalau ada pertanyaan, lalu masukkan password.

> **⚠️ PENTING:** Ganti `103.150.100.50` dengan IP VPS kamu yang asli!

### 3.3. Buat User Baru (Lebih Aman)

Jangan pakai `root` terus! Buat user baru:

```bash
# Masih login sebagai root
adduser deploy
# Isi password dan tekan Enter terus untuk pertanyaan lainnya

# Kasih akses sudo
usermod -aG sudo deploy

# Pindah ke user baru
su - deploy
```

Mulai sekarang, semua perintah pakai user `deploy`.

---

## 4. INSTALL SOFTWARE DI VPS

### 4.1. Update Sistem

```bash
sudo apt update && sudo apt upgrade -y
```

### 4.2. Install PHP 8.3 + Extension

```bash
# Tambah repository PHP
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 beserta extension yang dibutuhkan Laravel
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd \
  php8.3-intl php8.3-readline php8.3-tokenizer php8.3-cli
```

Cek PHP sudah terinstall:
```bash
php -v
# Harus muncul: PHP 8.3.x
```

### 4.3. Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Cek
composer --version
```

### 4.4. Install MySQL

```bash
sudo apt install -y mysql-server

# Amankan MySQL
sudo mysql_secure_installation
```

Saat ditanya:
- `VALIDATE PASSWORD component` → Ketik `n` (no)
- `New password` → Masukkan password untuk root MySQL (catat!)
- Pertanyaan lainnya → Ketik `y` (yes) semua

### 4.5. Install Nginx

```bash
sudo apt install -y nginx

# Cek Nginx jalan
sudo systemctl status nginx
```

Buka browser di laptop, ketik IP VPS kamu. Kalau muncul halaman "Welcome to nginx!" berarti berhasil! 🎉

### 4.6. Install Node.js & NPM (untuk build assets)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Cek
node -v
npm -v
```

### 4.7. Install Git

```bash
sudo apt install -y git

# Cek
git --version
```

### 4.8. Install Supervisor (untuk queue worker)

```bash
sudo apt install -y supervisor
```

---

## 5. SETUP DATABASE MYSQL

### 5.1. Login ke MySQL

```bash
sudo mysql
```

### 5.2. Buat Database dan User

```sql
-- Buat database
CREATE DATABASE point_of_sale_minimarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Buat user khusus (GANTI password_kamu_disini!)
CREATE USER 'pos_user'@'localhost' IDENTIFIED BY 'password_kamu_disini';

-- Kasih akses penuh ke database
GRANT ALL PRIVILEGES ON point_of_sale_minimarket.* TO 'pos_user'@'localhost';
FLUSH PRIVILEGES;

-- Keluar
EXIT;
```

> **📝 Catat:**
> - Database: `point_of_sale_minimarket`
> - User: `pos_user`
> - Password: `password_kamu_disini` (ganti dengan password kuat!)

---

## 6. UPLOAD PROJECT KE VPS VIA GIT

### 6.1. Buat Folder Project

```bash
# Login sebagai user deploy
sudo mkdir -p /var/www/pos-minimarket
sudo chown deploy:deploy /var/www/pos-minimarket
```

### 6.2. Clone dari GitHub

```bash
cd /var/www/pos-minimarket
git clone https://github.com/aldotris45-hash/point_of_sale_minimarket.git .
```

> **Catatan:** Titik (`.`) di akhir artinya clone langsung ke folder saat ini, bukan buat subfolder baru.

Jika repo private, kamu perlu setup SSH key atau pakai Personal Access Token:
```bash
# Kalau repo private, pakai token:
git clone https://TOKEN_KAMU@github.com/aldotris45-hash/point_of_sale_minimarket.git .
```

### 6.3. Pindah ke Branch yang Benar

```bash
# Lihat branch yang ada
git branch -a

# Pindah ke branch kamu (sesuaikan nama branch!)
git checkout main
# atau
git checkout copilot-worktree-2026-03-10T06-54-12
```

---

## 7. KONFIGURASI LARAVEL DI VPS

### 7.1. Install Dependencies PHP

```bash
cd /var/www/pos-minimarket

# Install tanpa dev dependencies (production mode)
composer install --no-dev --optimize-autoloader
```

### 7.2. Setup File .env

```bash
# Copy dari example
cp .env.example .env

# Edit file .env
nano .env
```

**Edit isi .env menjadi seperti ini:**

```env
APP_NAME="Point of Sale Minimarket"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://103.150.100.50

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=point_of_sale_minimarket
DB_USERNAME=pos_user
DB_PASSWORD=password_kamu_disini

SESSION_DRIVER=database
SESSION_LIFETIME=120

QUEUE_CONNECTION=database
CACHE_STORE=database

MIDTRANS_CLIENT_KEY=isi_client_key_midtrans
MIDTRANS_SERVER_KEY=isi_server_key_midtrans
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

**Cara simpan di nano:** Tekan `Ctrl+X` → `Y` → `Enter`

> **⚠️ PENTING:**
> - `APP_ENV=production` (bukan local!)
> - `APP_DEBUG=false` (bukan true! Kalau true, error bisa bocor ke user)
> - `APP_URL` ganti dengan domain kamu atau IP VPS
> - `DB_PASSWORD` ganti dengan password MySQL yang kamu buat tadi

### 7.3. Generate App Key

```bash
php artisan key:generate
```

### 7.4. Jalankan Migration (Buat Tabel Database)

```bash
php artisan migrate --force
```

> `--force` diperlukan karena environment production.

### 7.5. Jalankan Seeder (jika ada data awal)

```bash
php artisan db:seed --force
```

### 7.6. Build Assets (CSS/JS)

```bash
npm install
npm run build
```

### 7.7. Set Permission Folder

```bash
# Laravel butuh akses tulis ke folder-folder ini
sudo chown -R deploy:www-data /var/www/pos-minimarket
sudo chmod -R 775 /var/www/pos-minimarket/storage
sudo chmod -R 775 /var/www/pos-minimarket/bootstrap/cache
sudo chmod -R 775 /var/www/pos-minimarket/public
```

### 7.8. Optimize Laravel untuk Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Buat link storage (untuk upload gambar/logo)
php artisan storage:link
```

---

## 8. SETUP NGINX (WEB SERVER)

### 8.1. Buat Konfigurasi Nginx

```bash
sudo nano /etc/nginx/sites-available/pos-minimarket
```

**Paste isi berikut (GANTI IP/domain sesuai milikmu):**

```nginx
server {
    listen 80;
    server_name 103.150.100.50;
    # Kalau sudah punya domain, ganti jadi:
    # server_name pos-minimarket.com www.pos-minimarket.com;

    root /var/www/pos-minimarket/public;
    index index.php index.html;

    # Max upload file 10MB (untuk upload logo, dll)
    client_max_body_size 10M;

    # Gzip compression (bikin website lebih cepat)
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Blokir akses ke file sensitif
    location ~ /\.ht {
        deny all;
    }
    location ~ /\.env {
        deny all;
    }
    location ~ /\.git {
        deny all;
    }

    # Cache static files (bikin loading lebih cepat)
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

**Simpan:** `Ctrl+X` → `Y` → `Enter`

### 8.2. Aktifkan Konfigurasi

```bash
# Buat link
sudo ln -s /etc/nginx/sites-available/pos-minimarket /etc/nginx/sites-enabled/

# Hapus default nginx (opsional)
sudo rm /etc/nginx/sites-enabled/default

# Test konfigurasi
sudo nginx -t
# Harus muncul: syntax is ok / test is successful

# Restart Nginx
sudo systemctl restart nginx
```

### 8.3. Test!

Buka browser di laptop dan ketik: `http://103.150.100.50`

Kalau muncul halaman login POS kamu → **BERHASIL!** 🎉🎉🎉

---

## 9. SETUP SSL (HTTPS GRATIS)

> **Syarat:** Kamu harus punya domain yang sudah mengarah ke IP VPS.

### 9.1. Arahkan Domain ke IP VPS

Di panel domain (Niagahoster/Domainesia):
1. Buka **DNS Management**
2. Buat A Record:
   - Host: `@`
   - Value: `103.150.100.50` (IP VPS kamu)
3. Buat A Record lagi:
   - Host: `www`
   - Value: `103.150.100.50`

Tunggu 5-30 menit sampai DNS propagate.

### 9.2. Install Certbot (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx

# Generate SSL
sudo certbot --nginx -d pos-minimarket.com -d www.pos-minimarket.com
```

Ikuti petunjuknya:
- Masukkan email kamu
- Setuju terms → `Y`
- Share email → `N`
- Redirect HTTP ke HTTPS → pilih `2` (Redirect)

### 9.3. Update APP_URL

```bash
nano /var/www/pos-minimarket/.env
# Ganti:
# APP_URL=https://pos-minimarket.com
```

```bash
php artisan config:cache
```

### 9.4. Auto-Renew SSL

SSL Let's Encrypt expired 90 hari. Certbot sudah otomatis renew, tapi cek:

```bash
sudo certbot renew --dry-run
```

---

## 10. SETUP QUEUE WORKER (SUPERVISOR)

Project ini pakai queue (untuk notifikasi, dll). Kita perlu jalankan queue worker secara otomatis.

### 10.1. Buat Config Supervisor

```bash
sudo nano /etc/supervisor/conf.d/pos-queue.conf
```

**Paste:**

```ini
[program:pos-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pos-minimarket/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/pos-minimarket/storage/logs/queue.log
stopwaitsecs=3600
```

### 10.2. Aktifkan

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pos-queue:*

# Cek status
sudo supervisorctl status
# Harus muncul: pos-queue:pos-queue_00   RUNNING
```

---

## 11. TIPS MAINTENANCE & UPDATE

### 🔄 Cara Update Kode (Setelah Ada Perubahan)

Setiap kali kamu ada perubahan kode di laptop:

**Di laptop (Laragon):**
```bash
git add .
git commit -m "Perbaikan fitur XYZ"
git push origin main
```

**Di VPS:**
```bash
cd /var/www/pos-minimarket

# Tarik kode terbaru
git pull origin main

# Install dependency baru (jika ada)
composer install --no-dev --optimize-autoloader

# Jalankan migration baru (jika ada)
php artisan migrate --force

# Build assets (jika ada perubahan CSS/JS)
npm run build

# Clear cache lama
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue worker
sudo supervisorctl restart pos-queue:*

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

### 📝 Buat Script Update Otomatis (Opsional)

Biar gak ribet ketik satu-satu, buat script:

```bash
nano /var/www/pos-minimarket/deploy.sh
```

**Paste:**
```bash
#!/bin/bash
echo "🚀 Mulai deploy..."
cd /var/www/pos-minimarket

echo "📥 Pull kode terbaru..."
git pull origin main

echo "📦 Install dependencies..."
composer install --no-dev --optimize-autoloader

echo "🗃️ Jalankan migration..."
php artisan migrate --force

echo "🎨 Build assets..."
npm run build

echo "🔄 Clear & cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "♻️ Restart services..."
sudo supervisorctl restart pos-queue:*
sudo systemctl restart php8.3-fpm

echo "✅ Deploy selesai!"
```

```bash
chmod +x /var/www/pos-minimarket/deploy.sh
```

Sekarang setiap mau update, cukup jalankan:
```bash
cd /var/www/pos-minimarket
./deploy.sh
```

### 📊 Backup Database (Sangat Penting!)

```bash
# Backup manual
mysqldump -u pos_user -p point_of_sale_minimarket > ~/backup-$(date +%Y%m%d).sql

# Restore dari backup
mysql -u pos_user -p point_of_sale_minimarket < ~/backup-20260331.sql
```

**Setup backup otomatis harian:**
```bash
crontab -e
```
Tambah baris:
```
0 2 * * * mysqldump -u pos_user -ppassword_kamu point_of_sale_minimarket > /home/deploy/backups/db-$(date +\%Y\%m\%d).sql
```
(Backup setiap jam 2 pagi)

```bash
mkdir -p /home/deploy/backups
```

---

## 12. TROUBLESHOOTING

### ❌ Halaman Blank / Error 500
```bash
# Cek log Laravel
tail -100 /var/www/pos-minimarket/storage/logs/laravel.log

# Cek log Nginx
sudo tail -100 /var/log/nginx/error.log

# Fix permission
sudo chown -R deploy:www-data /var/www/pos-minimarket/storage
sudo chmod -R 775 /var/www/pos-minimarket/storage
```

### ❌ 502 Bad Gateway
```bash
# PHP-FPM tidak jalan
sudo systemctl restart php8.3-fpm
sudo systemctl status php8.3-fpm
```

### ❌ File Upload / Logo Tidak Muncul
```bash
php artisan storage:link
sudo chmod -R 775 /var/www/pos-minimarket/public
```

### ❌ Migration Error
```bash
# Cek status migration
php artisan migrate:status

# Reset dan migrate ulang (⚠️ HAPUS SEMUA DATA!)
php artisan migrate:fresh --seed --force
```

### ❌ Lupa Password VPS
- Hubungi provider VPS, minta reset password via console/VNC.

### ❌ Website Lambat
```bash
# Cek RAM tersisa
free -h

# Cek disk
df -h

# Restart semua
sudo systemctl restart nginx php8.3-fpm mysql
```

---

## 📁 STRUKTUR FILE DI VPS

```
/var/www/pos-minimarket/          ← Folder project utama
├── .env                          ← Konfigurasi (JANGAN commit ke Git!)
├── public/                       ← Nginx mengarah ke sini
│   ├── build/                    ← Hasil npm run build
│   └── index.php                 ← Entry point Laravel
├── storage/
│   ├── app/public/               ← Upload file (logo, dll)
│   └── logs/laravel.log          ← Log error
├── deploy.sh                     ← Script update otomatis
└── ...
```

---

## 🔐 CHECKLIST KEAMANAN

- [ ] `APP_DEBUG=false` di .env
- [ ] `APP_ENV=production` di .env
- [ ] Password database yang kuat (minimal 12 karakter)
- [ ] File `.env` tidak bisa diakses via browser
- [ ] Folder `.git` tidak bisa diakses via browser
- [ ] Firewall aktif (UFW)
- [ ] SSL (HTTPS) aktif
- [ ] Backup database otomatis

### Setup Firewall:
```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

---

## 🎯 RINGKASAN CEPAT

| Langkah | Perintah Utama |
|---------|---------------|
| 1. Login VPS | `ssh deploy@IP_VPS` |
| 2. Update kode | `cd /var/www/pos-minimarket && git pull` |
| 3. Install deps | `composer install --no-dev --optimize-autoloader` |
| 4. Migration | `php artisan migrate --force` |
| 5. Build assets | `npm run build` |
| 6. Clear cache | `php artisan config:cache && php artisan route:cache` |
| 7. Restart | `sudo systemctl restart php8.3-fpm` |

---

**Selamat! Website POS Minimarket kamu sekarang online! 🎉**

Jika ada masalah, cek log di `storage/logs/laravel.log` atau hubungi provider VPS-mu.
