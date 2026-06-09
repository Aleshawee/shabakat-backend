@echo off
cd /d "C:\laragon\www\Shabakat_Rewards\backend"
start "Laravel" /B "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan serve --port=8000

timeout /t 5 /nobreak >nul

cd /d "C:\laragon\www\Shabakat_Rewards\admin-panel"
start "Admin" /B node node_modules\vite\bin\vite.js

cd /d "C:\laragon\www\Shabakat_Rewards\user-app"
start "User" /B node node_modules\vite\bin\vite.js

echo -------------------------------------------
echo All servers started:
echo   Laravel  - http://localhost:8000
echo   Admin    - http://localhost:5173
echo   User     - http://localhost:5174
echo -------------------------------------------
pause
