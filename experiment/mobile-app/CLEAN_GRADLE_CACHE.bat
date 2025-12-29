@echo off
echo Clearing Gradle cache to fix JDK image transformation error...
echo.
echo Please close Android Studio and any running Gradle processes before continuing.
pause

echo Deleting local .gradle cache...
if exist android\.gradle rmdir /s /q android\.gradle
if exist android\app\.gradle rmdir /s /q android\app\.gradle

echo Deleting build folders...
if exist android\build rmdir /s /q android\build
if exist android\app\build rmdir /s /q android\app\build

echo.
echo IMPORTANT: You may also need to delete the global Gradle cache:
echo Delete this folder: C:\Users\%USERNAME%\.gradle\caches\transforms-3
echo.
echo Or delete the entire cache: C:\Users\%USERNAME%\.gradle\caches
echo.
echo After deleting, run: flutter clean && flutter pub get && flutter run
echo.
pause

