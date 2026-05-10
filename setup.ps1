$base = "C:\Users\mihriban.demircan\Downloads\champions-league-app"
Set-Location "$base\backend"
composer create-project laravel/laravel .
php artisan key:generate
Set-Location "$base\frontend"
npm create vue@latest .
npm install
Set-Location $base
Write-Output "Kurulum komutlari tamamlandi."
