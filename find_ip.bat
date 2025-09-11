@echo off
echo ========================================
echo    PO Management System - IP Finder
echo ========================================
echo.
echo Finding your computer's IP address...
echo.

ipconfig | findstr "IPv4"

echo.
echo ========================================
echo Instructions:
echo ========================================
echo 1. Note the IP address above (e.g., 192.168.1.100)
echo 2. On other devices, go to: http://[IP_ADDRESS]/PO-Management/
echo 3. Make sure XAMPP is running (Apache + MySQL)
echo 4. All devices must be on the same WiFi network
echo.
echo Example: http://192.168.1.100/PO-Management/
echo.
pause
