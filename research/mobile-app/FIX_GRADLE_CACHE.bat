@echo off
echo ========================================
echo FIXING GRADLE CACHE ERROR
echo ========================================
echo.
echo This will clear the corrupted Gradle cache.
echo Please close Android Studio and any running processes first.
echo.
pause

echo.
echo Step 1: Clearing local project Gradle cache...
if exist android\.gradle (
    echo Deleting android\.gradle...
    rmdir /s /q android\.gradle
    echo Done.
) else (
    echo android\.gradle not found.
)

if exist android\build (
    echo Deleting android\build...
    rmdir /s /q android\build
    echo Done.
) else (
    echo android\build not found.
)

if exist android\app\build (
    echo Deleting android\app\build...
    rmdir /s /q android\app\build
    echo Done.
) else (
    echo android\app\build not found.
)

echo.
echo Step 2: Clearing global Gradle cache (this may take a moment)...
echo.
echo IMPORTANT: You need to manually delete this folder:
echo C:\Users\%USERNAME%\.gradle\caches\transforms-3\4a46fc89ed5f9adfe3afebf74eb8bfeb
echo.
echo Or delete the entire transforms-3 folder:
echo C:\Users\%USERNAME%\.gradle\caches\transforms-3
echo.
echo After deleting, press any key to continue with Flutter clean...
pause

echo.
echo Step 3: Running Flutter clean...
flutter clean

echo.
echo Step 4: Getting dependencies...
flutter pub get

echo.
echo ========================================
echo DONE!
echo ========================================
echo.
echo Now try running: flutter run
echo.
pause

