# champions-league-app

Dort takimli bir mini lig simulasyonu. Fiksturu kuruyorsun, haftalari oynatiyorsun,
puan tablosi guncelleniyor, son haftalarda da sampiyonluk ihtimallerini Monte Carlo
ile hesapliyor. Skoru elle degistirebiliyorsun, tablo aninda yeniden hesaplaniyor.

Backend Laravel (REST API), frontend Vue 3 + Vite + TypeScript.

PHP 8.3+, Composer, Node 20+ gerekiyor.

```bash
# Backend
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

API: `http://127.0.0.1:8000`

```bash
# Frontend (yeni terminalde)
cd frontend
npm install
npm run dev
```

UI: `http://localhost:5173`

Vite dev sunucusu API'yi otomatik proxy ediyor.

## Test

```bash
cd backend && php artisan test
cd frontend && npm run lint && npm run test
```

## Yapi

```
backend/   Laravel API
  app/Services/LeagueSimulationService.php   ana lig kurallari
  app/Services/MatchScoreSimulator.php       Poisson tabanli skor
  app/Repositories/                          repository pattern
  app/Http/Controllers/Api/                  REST controller
  config/league.php                          tahmin iterasyon + cache
  routes/api.php                             /api/league/*

frontend/  Vue 3 + Pinia
  src/views/         Teams, Fixtures, Simulation sayfalari
  src/components/    standings tablosu, hafta paneli, sonuc duzenleme
  src/stores/        leagueFlow store (akis + API)
  src/api/           fetch wrapper
```

## API ozeti

| Method | URL | Aciklama |
|---|---|---|
| GET  | `/api/league/state` | Mevcut durum (takimlar, fikstur, tablo, tahmin) |
| POST | `/api/league/reset` | Ligi sifirla / takimlari ayarla |
| POST | `/api/league/simulate/week` | Sonraki haftayi oynat |
| POST | `/api/league/simulate/all`  | Sezonu sonuna kadar oynat |
| PUT  | `/api/league/fixtures/{id}` | Bir macin skorunu guncelle |

