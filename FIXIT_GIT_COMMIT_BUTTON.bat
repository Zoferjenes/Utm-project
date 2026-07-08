@echo off
setlocal EnableExtensions EnableDelayedExpansion

REM ============================================================
REM  FIXIT_GIT_COMMIT_BUTTON.bat
REM  Put this file inside your FixIt / Utm-project folder.
REM  Double-click it, type commit message, and it will git commit.
REM ============================================================

cd /d "%~dp0"
title FixIt Git Commit Button

cls
echo ============================================================
echo                 FixIt Git Commit Button
echo ============================================================
echo.
echo Current folder:
echo %CD%
echo.

echo Checking Git...
git --version >nul 2>&1
if errorlevel 1 (
    echo.
    echo [ERROR] Git is not installed or not added to PATH.
    echo Install Git for Windows first, then run this again.
    echo.
    pause
    exit /b 1
)

echo Git found.
echo.

REM Basic project safety check
if not exist "package.json" (
    echo [WARNING] No package.json found in this folder.
    echo Make sure this BAT file is inside your FixIt / Utm-project root folder.
    echo.
    choice /C YN /M "Continue anyway?"
    if errorlevel 2 exit /b 1
)

REM Initialize git if needed
if not exist ".git" (
    echo Initializing Git repository...
    git init
    git branch -M main >nul 2>&1
) else (
    echo Git repository already exists.
)

echo.
echo Updating .gitignore...
if not exist ".gitignore" type nul > ".gitignore"

findstr /X /C:"node_modules/" ".gitignore" >nul 2>&1 || echo node_modules/>> ".gitignore"
findstr /X /C:"vendor/" ".gitignore" >nul 2>&1 || echo vendor/>> ".gitignore"
findstr /X /C:".env" ".gitignore" >nul 2>&1 || echo .env>> ".gitignore"
findstr /X /C:".env.*" ".gitignore" >nul 2>&1 || echo .env.*>> ".gitignore"
findstr /X /C:"*.log" ".gitignore" >nul 2>&1 || echo *.log>> ".gitignore"
findstr /X /C:"dist/" ".gitignore" >nul 2>&1 || echo dist/>> ".gitignore"
findstr /X /C:".vite/" ".gitignore" >nul 2>&1 || echo .vite/>> ".gitignore"
findstr /X /C:".gradle/" ".gitignore" >nul 2>&1 || echo .gradle/>> ".gitignore"
findstr /X /C:"build/" ".gitignore" >nul 2>&1 || echo build/>> ".gitignore"
findstr /X /C:".DS_Store" ".gitignore" >nul 2>&1 || echo .DS_Store>> ".gitignore"

REM Ask for commit message
set "COMMIT_MSG="
echo.
set /p COMMIT_MSG=Type commit message then press ENTER [finalize FixIt project submission]: 
if "%COMMIT_MSG%"=="" set "COMMIT_MSG=finalize FixIt project submission"

echo.
echo Adding files...
git add .
if errorlevel 1 (
    echo.
    echo [ERROR] git add failed.
    echo.
    pause
    exit /b 1
)

echo.
echo Checking if there are changes to commit...
git diff --cached --quiet
if not errorlevel 1 (
    echo.
    echo No new changes to commit.
    echo.
    git status --short
    echo.
    pause
    exit /b 0
)

echo.
echo Committing with message:
echo "%COMMIT_MSG%"
echo.
git commit -m "%COMMIT_MSG%"
if errorlevel 1 (
    echo.
    echo [ERROR] Commit failed.
    echo Common reason: Git username/email is not set.
    echo.
    echo Run these once in PowerShell or CMD:
    echo git config --global user.name "Your Name"
    echo git config --global user.email "your-email@example.com"
    echo.
    pause
    exit /b 1
)

echo.
echo ============================================================
echo Commit done.
echo ============================================================
echo.
git status --short

echo.
choice /C YN /M "Do you want to push to GitHub now?"
if errorlevel 2 goto END

echo.
echo Checking remote...
git remote get-url origin >nul 2>&1
if errorlevel 1 (
    echo No GitHub remote found.
    set "REMOTE_URL="
    set /p REMOTE_URL=Paste GitHub repo URL, or leave blank to skip push: 
    if "!REMOTE_URL!"=="" goto END
    git remote add origin "!REMOTE_URL!"
)

echo.
echo Pushing to origin main...
git push -u origin main
if errorlevel 1 (
    echo.
    echo [ERROR] Push failed. Check GitHub login/remote/branch.
    echo.
    pause
    exit /b 1
)

echo.
echo Push done.

:END
echo.
echo Finished.
pause
exit /b 0
