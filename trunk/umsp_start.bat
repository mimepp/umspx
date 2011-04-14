@echo off
echo starting umsp ...
goto start
:start
cls
color 1f
echo.
echo.
wget.exe http://192.168.1.3:7703/umsp/send-ssdp.php?btnSend=Submit -O - -q
ping -n 5 127.0.0.1 >nul 2>nul
goto start