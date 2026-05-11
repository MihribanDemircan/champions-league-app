# champions-league-app

Dört takımlı mini bir lig simülasyonu. Fikstürü kuruyorsun, haftaları oynatıyorsun,
puan tablosu güncelleniyor; sezonun son haftalarında şampiyonluk ihtimalleri Monte Carlo
ile hesaplanıyor. Skoru elle değiştirebiliyorsun, tablo anında yeniden hesaplanıyor.

Arka uç Laravel (REST API), ön uç Vue 3 + Vite + TypeScript.

## Gereksinimler

PHP 8.3+, Composer, Node 20+.

## Çalıştırma

### Arka uç

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

API adresi: `http://127.0.0.1:8000`

### Ön uç (ayrı bir terminalde)

```bash
cd frontend
npm install
npm run dev
```

Arayüz: `http://localhost:5173`

Geliştirme sırasında Vite, istekleri API’ye otomatik yönlendirir (vekil ayarı).

## Test

```bash
cd backend && php artisan test
cd frontend && npm run lint && npm run test
```

## Klasör yapısı

```
backend/   Laravel API
  app/Services/LeagueSimulationService.php   ana lig kuralları
  app/Services/MatchScoreSimulator.php       Poisson tabanlı skor
  app/Repositories/                          repository deseni
  app/Http/Controllers/Api/                  REST uç noktaları
  config/league.php                          tahmin yinelemesi + önbellek
  routes/api.php                             /api/league/*

frontend/  Vue 3 + Pinia
  src/views/         takımlar, fikstür, simülasyon sayfaları
  src/components/    puan tablosu, hafta paneli, sonuç düzenleme
  src/stores/        leagueFlow (akış + API)
  src/api/           istek yardımcıları
```

## API özeti

| Yöntem | Adres | Açıklama |
|--------|--------|----------|
| GET    | `/api/league/state` | Güncel durum (takımlar, fikstür, tablo, tahmin) |
| POST   | `/api/league/reset` | Ligi sıfırla / takımları ayarla |
| POST   | `/api/league/simulate/week` | Sonraki haftayı oynat |
| POST   | `/api/league/simulate/all`  | Sezonu baştan sona oynat |
| PUT    | `/api/league/fixtures/{id}` | Bir maçın skorunu güncelle |

