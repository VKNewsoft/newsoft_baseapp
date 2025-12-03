@echo off
REM ====================================================================
REM Database Installer - Manual Installation
REM Author: VKNewsoft - Newsoft Developer, 2025
REM ====================================================================

color 0A
echo.
echo ========================================
echo   DATABASE INSTALLER - MANUAL MODE
echo ========================================
echo.

:menu
echo Pilih opsi instalasi:
echo.
echo [1] Install Database (Import newsoft_base.sql)
echo [2] Verify Import (Cek hasil instalasi)
echo [3] Check Tables (Detail checklist 34 tabel)
echo [4] Exit
echo.
set /p choice="Masukkan pilihan (1-4): "

if "%choice%"=="1" goto install
if "%choice%"=="2" goto verify
if "%choice%"=="3" goto check
if "%choice%"=="4" goto exit

echo.
echo Pilihan tidak valid! Silakan pilih 1-4.
echo.
goto menu

:install
cls
echo.
echo ========================================
echo   INSTALLING DATABASE...
echo ========================================
echo.
echo Proses import database akan dimulai.
echo Ini akan memakan waktu 1-2 menit.
echo.
php import_sql.php
echo.
pause
goto menu

:verify
cls
echo.
echo ========================================
echo   VERIFYING INSTALLATION...
echo ========================================
echo.
php verify_import.php
echo.
pause
goto menu

:check
cls
echo.
echo ========================================
echo   CHECKING TABLES...
echo ========================================
echo.
php check_tables.php
echo.
pause
goto menu

:exit
cls
echo.
echo Terima kasih telah menggunakan Database Installer!
echo.
timeout /t 2 /nobreak >nul
exit
