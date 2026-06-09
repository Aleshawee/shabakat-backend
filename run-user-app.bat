@echo off
cd /d "C:\laragon\www\Shabakat_Rewards\user-app"
:loop
echo [%date% %time%] Starting Vite dev server...
node node_modules\vite\bin\vite.js
echo [%date% %time%] Server crashed! Restarting in 3 seconds...
timeout /t 3 /nobreak >nul
goto loop
