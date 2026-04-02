# 🚀 PANDUAN DEPLOY KE VPS — Step by Step (Untuk Pemula)

Panduan ini dibuat **sangat detail** agar kamu yang masih awam bisa mengikuti dari nol.

---

## 📋 DAFTAR ISI

0. [Persiapan di Laptop (Laragon)](#0-persiapan-di-laptop-laragon)
1. [Beli VPS & Domain](#1-beli-vps--domain)
2. [Login ke VPS via SSH](#2-login-ke-vps-via-ssh)
3. [Install Software di VPS](#3-install-software-di-vps)
4. [Setup Database MySQL](#4-setup-database-mysql)
5. [Upload Project ke VPS via Git](#5-upload-project-ke-vps-via-git)
6. [Konfigurasi Laravel di VPS](#6-konfigurasi-laravel-di-vps)
7. [Setup Nginx (Web Server)](#7-setup-nginx-web-server)
8. [Setup SSL (HTTPS Gratis)](#8-setup-ssl-https-gratis)
9. [Setup Queue Worker (Supervisor)](#9-setup-queue-worker-supervisor)
10. [Tips Maintenance & Update](#10-tips-maintenance--update)
11. [Troubleshooting](#11-troubleshooting)

---

## 0. PERSIAPAN DI LAPTOP (LARAGON)

> **Ini yang pertama kali kamu lakukan. Buka Laragon → klik tombol "Terminal".**

### 0.1. Buka Terminal Laragon

1. Buka aplikasi **Laragon**
2. Klik tombol **"Terminal"** (di pojok kanan bawah Laragon)
3. Akan muncul jendela terminal (Cmder/CMD)

### 0.2. Masuk ke Folder Project

Ketik perintah ini di terminal Laragon (tekan Enter setiap baris):

```bash
cd C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12
```

> **💡 Tip:** Kamu bisa copy-paste perintah di atas. Klik kanan di terminal untuk paste.

### 0.3. Build Assets (CSS/JS untuk Production)

```bash
npm install
npm run build
```

**Tunggu sampai selesai.** Ini akan membuat folder `public/build/` berisi CSS dan JS yang sudah siap.

Kalau ada error `npm not found`, ketik dulu:
```bash
node -v
npm -v
```
Kalau tidak ada, install Node.js dulu dari https://nodejs.org/

### 0.4. Push Kode Terbaru ke GitHub

```bash
git add .
git commit -m "Build assets dan siap deploy"
git push origin copilot-worktree-2026-03-10T06-54-12
```

Kalau diminta login GitHub:
- **Username:** username GitHub kamu (`aldotris45-hash`)
- **Password:** bukan password GitHub, tapi **Personal Access Token** (lihat langkah 0.5)

### 0.5. Cara Buat Personal Access Token GitHub (Jika Diminta Password)

1. Buka browser → https://github.com/settings/tokens
2. Klik **"Generate new token (classic)"**
3. Isi:
   - Note: `deploy-pos`
   - Expiration: `90 days`
   - Centang: `repo` (semua sub-checkbox-nya)
4. Klik **"Generate token"**
5. **COPY token yang muncul** (hanya muncul sekali!)
6. Pakai token ini sebagai "password" saat git push minta password

### 0.6. (Opsional) Merge ke Branch Main

Kalau mau semua kode masuk ke branch `main`:

**Cara 1 — Via Browser (paling mudah):**
1. Buka: https://github.com/aldotris45-hash/point_of_sale_minimarket
2. Akan muncul banner kuning "copilot-worktree... had recent pushes" → klik **"Compare & pull request"**
3. Klik **"Create pull request"**
4. Klik **"Merge pull request"** → **"Confirm merge"**

**Cara 2 — Via Terminal Laragon:**
```bash
git checkout main
git merge copilot-worktree-2026-03-10T06-54-12
git push origin main
```

### ✅ Selesai di Laptop!

Sekarang kode kamu sudah aman di GitHub. Lanjut ke langkah berikutnya: beli VPS.

---

## 1. BELI VPS & DOMAIN

### Rekomendasi Provider VPS Indonesia:
| Provider | Harga | Keterangan |
|----------|-------|-----------|
| **IDCloudHost** | Rp 35.000/bulan | Murah, server Jakarta |
| **Niagahoster VPS** | Rp 50.000/bulan | Mudah dipakai |
| **DigitalOcean** | $6/bulan (~Rp 95.000) | Populer, pakai kartu kredit |
| **Contabo** | $4.99/bulan (~Rp 80.000) | Murah, server Jerman |

### Spesifikasi Minimal:
- **OS:** Pilih **Ubuntu 22.04 LTS** atau **24.04 LTS**
- **RAM:** 1 GB (2 GB lebih baik)
- **Storage:** 20 GB SSD
- **Bandwidth:** Unlimited

### Beli Domain (Opsional):
- Harga mulai Rp 15.000/tahun untuk `.my.id` di Niagahoster/Domainesia
- Untuk belajar, pakai IP VPS langsung dulu juga bisa

### Setelah Beli VPS, Kamu Akan Dapat:
| Info | Contoh |
|------|--------|
| IP Address | `103.150.100.50` |
| Username | `root` |
| Password | `aBcD1234xxxx` |

**📝 Catat ketiga info di atas!**

---

## 2. LOGIN KE VPS VIA SSH

### 2.1. Buka CMD/PowerShell di Laptop

Tekan `Win+R` → ketik `cmd` → Enter

ATAU

Tekan `Win+X` → pilih **"Terminal"** atau **"PowerShell"**

### 2.2. Ketik Perintah SSH

```bash
ssh root@103.150.100.50
```

> ⚠️ Ganti `103.150.100.50` dengan IP VPS kamu!

- Kalau muncul pertanyaan `Are you sure you want to continue connecting?` → ketik `yes` → Enter
- Masukkan password VPS (ketika ketik password, hurufnya tidak muncul — itu normal!) → Enter

### 2.3. Buat User Baru (Lebih Aman daripada Root)

Sekarang kamu sudah di dalam VPS. Ketik:

```bash
adduser deploy
```

- Isi password baru (catat!)
- Pertanyaan lainnya tekan Enter saja

```bash
usermod -aG sudo deploy
```

Sekarang pindah ke user baru:
```bash
su - deploy
```

> Mulai sekarang, semua perintah ketik sebagai user `deploy`.

---

## 3. INSTALL SOFTWARE DI VPS

Ketik semua perintah ini satu per satu di terminal SSH:

### 3.1. Update Sistem

```bash
sudo apt update && sudo apt upgrade -y
```

(Tunggu sampai selesai, mungkin 1-2 menit)

### 3.2. Install PHP 8.3

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-intl php8.3-readline php8.3-tokenizer php8.3-cli
```

Cek berhasil:
```bash
php -v
```
Harus muncul `PHP 8.3.x`

### 3.3. Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 3.4. Install MySQL

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

Saat ditanya:
- `VALIDATE PASSWORD component?` → ketik **n**
- `New password:` → masukkan password baru untuk MySQL (CATAT!)
- Sisanya ketik **y** semua

### 3.5. Install Nginx

```bash
sudo apt install -y nginx
```

**Test:** Buka browser di laptop, ketik IP VPS kamu (contoh: `http://103.150.100.50`). Kalau muncul "Welcome to nginx!" → berhasil! 🎉

### 3.6. Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

### 3.7. Install Git & Supervisor

```bash
sudo apt install -y git supervisor
```

---

## 4. SETUP DATABASE MYSQL

### 4.1. Login ke MySQL

```bash
sudo mysql
```

### 4.2. Buat Database dan User

Ketik perintah ini satu per satu (perhatikan titik koma di akhir!):

```sql
CREATE DATABASE point_of_sale_minimarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pos_user'@'localhost' IDENTIFIED BY 'aldo2304';
GRANT ALL PRIVILEGES ON point_of_sale_minimarket.* TO 'pos_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> ⚠️ **GANTI** `aldo2304` dengan password kamu sendiri! Catat!

---

## 5. UPLOAD PROJECT KE VPS VIA GIT

### 5.1. Buat Folder Project

```bash
sudo mkdir -p /var/www/pos-minimarket
sudo chown deploy:deploy /var/www/pos-minimarket
cd /var/www/pos-minimarket
```

### 5.2. Clone dari GitHub

```bash
git clone https://github.com/aldotris45-hash/point_of_sale_minimarket.git .
```

> Titik (`.`) di akhir penting! Artinya clone langsung ke folder ini.

Jika repo private dan minta password:
```bash
git clone https://aldotris45-hash:TOKEN_KAMU@github.com/aldotris45-hash/point_of_sale_minimarket.git .
```
(Ganti `TOKEN_KAMU` dengan Personal Access Token dari langkah 0.5)

### 5.3. Pindah ke Branch yang Benar

```bash
git checkout main
```

Atau kalau belum merge ke main:
```bash
git checkout copilot-worktree-2026-03-10T06-54-12
```

---

## 6. KONFIGURASI LARAVEL DI VPS

### 6.1. Install Dependencies PHP

```bash
cd /var/www/pos-minimarket
composer install --no-dev --optimize-autoloader
```

(Tunggu 1-3 menit)

### 6.2. Buat & Edit File .env

```bash
cp .env.example .env
nano .env
```

**Editor nano akan terbuka.** Navigasi pakai tombol panah. Edit baris-baris berikut:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=http://103.150.100.50

DB_DATABASE=point_of_sale_minimarket
DB_USERNAME=pos_user
DB_PASSWORD=GantiDenganPasswordKuat123!

MIDTRANS_CLIENT_KEY=isi_client_key_kamu
MIDTRANS_SERVER_KEY=isi_server_key_kamu
MIDTRANS_IS_PRODUCTION=true
```

**Cara simpan:** Tekan `Ctrl+X` → `Y` → `Enter`

> ⚠️ Ganti IP, password DB, dan Midtrans keys sesuai punya kamu!

### 6.3. Generate App Key

```bash
php artisan key:generate
```

### 6.4. Jalankan Migration (Buat Tabel)

```bash
php artisan migrate --force
```

### 6.5. Jalankan Seeder (Data Awal)

```bash
php artisan db:seed --force
```

### 6.6. Build Assets

```bash
npm install
npm run build
```

### 6.7. Set Permission

```bash
sudo chown -R deploy:www-data /var/www/pos-minimarket
sudo chmod -R 775 /var/www/pos-minimarket/storage
sudo chmod -R 775 /var/www/pos-minimarket/bootstrap/cache
sudo chmod -R 775 /var/www/pos-minimarket/public
```

### 6.8. Optimize & Storage Link

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

---

## 7. SETUP NGINX (WEB SERVER)

### 7.1. Buat Konfigurasi

```bash
sudo nano /etc/nginx/sites-available/pos-minimarket
```

**Paste semua ini** (klik kanan untuk paste di terminal):

```nginx
server {
    listen 80;
    server_name 103.150.100.50;

    root /var/www/pos-minimarket/public;
    index index.php index.html;

    client_max_body_size 10M;

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

    location ~ /\.ht { deny all; }
    location ~ /\.env { deny all; }
    location ~ /\.git { deny all; }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

> ⚠️ Ganti `103.150.100.50` dengan IP VPS atau domain kamu!

**Simpan:** `Ctrl+X` → `Y` → `Enter`

### 7.2. Aktifkan & Restart

```bash
sudo ln -s /etc/nginx/sites-available/pos-minimarket /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

### 7.3. TEST!

Buka browser di laptop → ketik `http://103.150.100.50` (IP VPS kamu)

**Kalau muncul halaman login POS → BERHASIL! 🎉🎉🎉**

---

## 8. SETUP SSL (HTTPS GRATIS)

> Butuh domain! Kalau belum punya domain, skip dulu.

### 8.1. Arahkan Domain ke VPS

Di panel domain (Niagahoster/Domainesia):
- Buat **A Record**: Host `@`, Value = IP VPS kamu
- Buat **A Record**: Host `www`, Value = IP VPS kamu

Tunggu 5-30 menit.

### 8.2. Install SSL

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d namadomainkamu.com -d www.namadomainkamu.com
```

- Masukkan email → Enter
- Setuju terms → `Y`
- Share email → `N`
- Redirect → pilih `2`

### 8.3. Update .env

```bash
nano /var/www/pos-minimarket/.env
```
Ganti `APP_URL=https://namadomainkamu.com`

```bash
php artisan config:cache
```

---

## 9. SETUP QUEUE WORKER (SUPERVISOR)

```bash
sudo nano /etc/supervisor/conf.d/pos-queue.conf
```

Paste:
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

Simpan, lalu:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pos-queue:*
sudo supervisorctl status
```

---

## 10. TIPS MAINTENANCE & UPDATE

### 🔄 Setiap Ada Perubahan Kode

**Di laptop (Terminal Laragon):**
```bash
cd C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12
git add .
git commit -m "Perubahan yang dilakukan"
git push origin main
```

**Di VPS (Terminal SSH):**
```bash
cd /var/www/pos-minimarket
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart pos-queue:*
sudo systemctl restart php8.3-fpm
```

### 📝 Script Update 1-Klik

Buat script di VPS:
```bash
nano /var/www/pos-minimarket/deploy.sh
```

Paste:
```bash
#!/bin/bash
echo "🚀 Mulai deploy..."
cd /var/www/pos-minimarket
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
sudo supervisorctl restart pos-queue:*
sudo systemctl restart php8.3-fpm
echo "✅ Deploy selesai!"
```

```bash
chmod +x /var/www/pos-minimarket/deploy.sh
```

Sekarang setiap update cukup:
```bash
./deploy.sh
```

### 📊 Backup Database

```bash
mkdir -p /home/deploy/backups
mysqldump -u pos_user -p point_of_sale_minimarket > /home/deploy/backups/backup-$(date +%Y%m%d).sql
```

### 🔐 Setup Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

---

## 11. TROUBLESHOOTING

| Masalah | Solusi |
|---------|--------|
| Halaman blank / Error 500 | `tail -100 /var/www/pos-minimarket/storage/logs/laravel.log` |
| 502 Bad Gateway | `sudo systemctl restart php8.3-fpm` |
| Logo/gambar tidak muncul | `php artisan storage:link` |
| Permission error | `sudo chmod -R 775 /var/www/pos-minimarket/storage` |
| npm error di VPS | `sudo npm install` atau hapus `node_modules` lalu `npm install` ulang |

---

**Selamat! Website POS Minimarket kamu sekarang online! 🎉**
