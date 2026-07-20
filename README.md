# SupplyGuard

SupplyGuard adalah aplikasi **Global Supply Chain Risk Intelligence Platform** berbasis Laravel yang digunakan untuk memantau risiko rantai pasok global. Sistem ini menggabungkan data negara, cuaca, ekonomi, nilai tukar, berita, dan pelabuhan untuk membantu analisis risiko aktivitas impor dan logistik internasional.

## Deskripsi Project

Project ini dibuat untuk memenuhi tugas UAS Pemrograman Web dengan studi kasus pemantauan risiko rantai pasok global. SupplyGuard menyediakan dashboard analitik yang menampilkan data negara, risiko cuaca, risiko inflasi, dampak nilai tukar, berita global, lokasi pelabuhan, perbandingan negara, daftar monitoring favorit, serta dashboard admin.

Sistem ini menggunakan beberapa API eksternal dan juga menyediakan REST API internal agar data dapat diakses dalam format JSON.

## Fitur Utama

- Dashboard utama monitoring risiko rantai pasok global
- Data negara global dari REST Countries API
- Risk Scoring Engine
- Weather Monitoring menggunakan Open-Meteo
- Currency Impact menggunakan Exchange Rate API
- News Intelligence menggunakan GNews API
- Port Location Dashboard
- Data Visualization Dashboard
- Country Comparison Engine
- Favorite Monitoring List
- Admin Dashboard
- Manajemen user
- Manajemen dataset pelabuhan
- Manajemen artikel analisis
- Manajemen kamus sentimen positif dan negatif
- Log pemanggilan API
- REST API internal

## API Eksternal yang Digunakan

- REST Countries API
- Open-Meteo API
- World Bank API
- Exchange Rate API
- GNews API
- OpenStreetMap / Leaflet

## REST API Internal

Aplikasi ini menyediakan beberapa endpoint API internal:

- `GET /api/countries`
- `GET /api/risk`
- `GET /api/ports`
- `GET /api/news`
- `GET /api/currency`

## Teknologi yang Digunakan

- Laravel 12
- PHP 8.2
- MySQL
- Bootstrap 5
- Bootstrap Icons
- Chart.js
- Leaflet.js
- REST API Integration

## Akun Default

### Admin

Email:

```text
admin@gmail.com
```

Password:

```text
Admin@12345
```

### User

Email:

```text
user@gmail.com
```

Password:

```text
User@12345
```

## Cara Menjalankan Project

Clone repository:

```bash
git clone https://github.com/saidmirzafakhrullah/supplyguard-uas.git
```

Masuk ke folder project:

```bash
cd supplyguard-uas
```

Install dependency PHP:

```bash
composer install
```

Install dependency frontend:

```bash
npm install
```

Salin file environment:

```bash
copy .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Atur konfigurasi database di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supplyguard_uas
DB_USERNAME=root
DB_PASSWORD=
```

Isi API key yang diperlukan pada file `.env`:

```env
REST_COUNTRIES_API_KEY=
REST_COUNTRIES_BASE_URL=https://api.restcountries.com/countries/v5

GNEWS_API_KEY=
GNEWS_BASE_URL=https://gnews.io/api/v4
GNEWS_LANGUAGE=en
GNEWS_MAX_ARTICLES=10
```

Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

Jalankan server Laravel:

```bash
php artisan serve
```

Jalankan Vite:

```bash
npm run dev
```

Buka aplikasi melalui browser:

```text
http://127.0.0.1:8000
```

## Struktur Fitur

### User

User dapat mengakses fitur:

- Dashboard utama
- Negara global
- Risk scoring
- Weather monitoring
- Currency impact
- News intelligence
- Port location
- Data visualization
- Country comparison
- Favorite monitoring

### Admin

Admin dapat mengakses fitur:

- Manajemen user
- Manajemen pelabuhan
- Manajemen artikel analisis
- Manajemen kamus sentimen
- Log pemanggilan API

## Database

Project ini menggunakan MySQL dengan beberapa tabel utama, seperti:

- users
- ports
- articles
- sentiment_words
- api_logs
- watchlists
- countries
- risk_scores
- news_cache
- sessions
- cache
- jobs

Total tabel project berada pada skala 15–20 tabel sesuai kebutuhan project.

## Catatan Penting

File berikut tidak perlu dimasukkan ke ZIP pengumpulan:

- `.env`
- `vendor`
- `node_modules`
- `.git`
- `database/database.sqlite`

File `.env.example` boleh dikirim karena tidak berisi API key asli.

## Status Project

Project SupplyGuard sudah memiliki fitur utama, dashboard analitik, integrasi API eksternal, REST API internal, sistem risk scoring, sentiment analysis sederhana, peta interaktif, serta dashboard admin.