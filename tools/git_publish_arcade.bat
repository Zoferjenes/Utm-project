@echo off
setlocal EnableDelayedExpansion

set "GIT_USER_NAME=exant9"
set "GIT_USER_EMAIL=fcfgts@gmail.com"
set "COMMIT_MESSAGE=Add provider distance filtering"
set "COMMIT_BODY=Adds provider latitude/longitude/service radius fields, nearby distance calculation on provider browse, distance controls in the Vue services UI, provider profile geo inputs, seeded coordinates, and docs updates to cover the Problem 5 distance-filtering requirement."
set "NEEDS_FORCE_PUSH=0"

cd /d "%~dp0\.."

echo.
echo Arcade FixIt Git publish helper
echo Working folder: %CD%
echo.

git --version >nul 2>&1
if errorlevel 1 (
  echo Git is not available in this terminal.
  echo Open a normal CMD or Git Bash where git works, then run this file again.
  goto fail
)

if not exist ".git" (
  echo Initializing Git repository inside Utm-project...
  git init
  if errorlevel 1 goto fail
) else (
  echo Git repository already exists.
)

echo Marking this project folder as safe for Git...
git config --global --add safe.directory "%CD%"
if errorlevel 1 goto fail

echo Configuring Git identity for this repository...
git config user.name "%GIT_USER_NAME%"
if errorlevel 1 goto fail
git config user.email "%GIT_USER_EMAIL%"
if errorlevel 1 goto fail

git branch -M main
if errorlevel 1 goto fail

git remote get-url origin >nul 2>&1
if errorlevel 1 (
  git remote add origin https://github.com/Zoferjenes/Utm-project.git
) else (
  git remote set-url origin https://github.com/Zoferjenes/Utm-project.git
)
if errorlevel 1 goto fail

echo.
echo Staging project files...
git add .
if errorlevel 1 goto fail

git rev-parse --verify HEAD >nul 2>&1
if errorlevel 1 (
  echo Creating first commit...
  git commit -m "%COMMIT_MESSAGE%" -m "%COMMIT_BODY%"
  if errorlevel 1 (
    echo.
    echo Commit failed. If Git asks for identity, run:
    echo git config --global user.name "%GIT_USER_NAME%"
    echo git config --global user.email "%GIT_USER_EMAIL%"
    goto fail
  )
) else (
  for /f "usebackq delims=" %%A in (`git log -1 --format^=%%ae`) do set "HEAD_EMAIL=%%A"

  if /i not "!HEAD_EMAIL!"=="%GIT_USER_EMAIL%" (
    echo Fixing last commit author to %GIT_USER_NAME% ^<%GIT_USER_EMAIL%^>...
    git commit --amend --no-edit --author="%GIT_USER_NAME% <%GIT_USER_EMAIL%>"
    if errorlevel 1 goto fail
    set "NEEDS_FORCE_PUSH=1"
  ) else (
    git diff --cached --quiet
    if errorlevel 1 (
      echo Creating commit...
      git commit -m "%COMMIT_MESSAGE%" -m "%COMMIT_BODY%"
      if errorlevel 1 goto fail
    ) else (
      echo No staged changes to commit.
    )
  )
)

echo.
echo Checking remote main branch...
if "%NEEDS_FORCE_PUSH%"=="1" (
  echo Last commit was amended, so skipping pull and using force-with-lease.
) else (
  git ls-remote --exit-code --heads origin main >nul 2>&1
  if not errorlevel 1 (
    echo Remote main exists. Pulling with unrelated histories allowed...
    git pull origin main --allow-unrelated-histories --no-edit
    if errorlevel 1 (
      echo.
      echo Pull stopped, probably because of a merge conflict.
      echo Resolve conflicts in Utm-project, then run:
      echo git add .
      echo git commit
      echo git push -u origin main
      goto fail
    )
  ) else (
    echo Remote main not found or remote is empty. Continuing to push.
  )
)

echo.
echo Pushing to GitHub...
if "%NEEDS_FORCE_PUSH%"=="1" (
  git push --force-with-lease -u origin main
  if errorlevel 1 goto fail
) else (
  git push -u origin main
  if errorlevel 1 goto fail
)

echo.
echo Done. Arcade FixIt has been pushed.
goto end

:fail
echo.
echo Publish helper stopped before completion.

:end
pause
