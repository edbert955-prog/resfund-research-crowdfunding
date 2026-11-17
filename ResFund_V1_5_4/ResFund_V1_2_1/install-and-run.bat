@echo off
setlocal
cd /d "%~dp0"

echo ==============================================
echo ResFund Interactive Prototype V1.2.1 - Setup
echo ==============================================

where php >nul 2>nul
if errorlevel 1 (
  echo [ERROR] PHP tidak ditemukan. Aktifkan PHP 8.3+ pada PATH atau jalankan dari terminal Laragon.
  pause
  exit /b 1
)

if not exist .env (
  if exist .env.example copy .env.example .env >nul
)

if not exist vendor\autoload.php (
  where composer >nul 2>nul
  if errorlevel 1 (
    echo [ERROR] Folder vendor belum ada dan Composer tidak ditemukan.
    echo Jalankan composer install satu kali atau aktifkan Composer di PATH.
    pause
    exit /b 1
  )
  echo [1/2] Memasang dependensi Laravel untuk instalasi baru...
  composer install
  if errorlevel 1 goto failed
) else (
  echo Dependensi Laravel sudah tersedia. Melewati composer install.
)

findstr /R /C:"^APP_KEY=base64:" .env >nul
if errorlevel 1 (
  echo Membuat application key...
  php artisan key:generate
  if errorlevel 1 goto failed
)

php artisan optimize:clear

echo Menjalankan server lokal...
php artisan serve
if errorlevel 1 goto failed
goto end

:failed
echo.
echo Setup gagal. Periksa pesan error di atas dan README.md.
pause
exit /b 1
:end
endlocal
