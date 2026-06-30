$ErrorActionPreference = 'Stop'

$ProjectRoot = Split-Path -Parent $PSScriptRoot
$BackendRoot = Join-Path $ProjectRoot 'backend'
$FrontendRoot = Join-Path $ProjectRoot 'frontend'
$LogRoot = Join-Path $PSScriptRoot 'logs'
$StartupLog = Join-Path $LogRoot 'startup.log'

New-Item -ItemType Directory -Force -Path $LogRoot | Out-Null

function Write-Log {
    param([string]$Message)

    $line = "[{0}] {1}" -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Message
    Add-Content -Path $StartupLog -Value $line
}

function Test-LocalPort {
    param([int]$Port)

    $client = $null
    try {
        $client = [System.Net.Sockets.TcpClient]::new()
        $async = $client.BeginConnect('127.0.0.1', $Port, $null, $null)
        if (-not $async.AsyncWaitHandle.WaitOne(700, $false)) {
            return $false
        }
        $client.EndConnect($async)
        return $true
    } catch {
        return $false
    } finally {
        if ($client) {
            $client.Close()
        }
    }
}

function Resolve-PhpExe {
    $configured = Join-Path 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64' 'php.exe'
    if (Test-Path $configured) {
        return $configured
    }

    $laragonPhp = Get-ChildItem -Directory 'C:\laragon\bin\php' -ErrorAction SilentlyContinue |
        Sort-Object Name -Descending |
        ForEach-Object { Join-Path $_.FullName 'php.exe' } |
        Where-Object { Test-Path $_ } |
        Select-Object -First 1

    if ($laragonPhp) {
        return $laragonPhp
    }

    $pathPhp = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($pathPhp) {
        return $pathPhp.Source
    }

    return $null
}

function Resolve-NpmCmd {
    $npm = Get-Command npm.cmd -ErrorAction SilentlyContinue
    if ($npm) {
        return $npm.Source
    }

    $npm = Get-Command npm -ErrorAction SilentlyContinue
    if ($npm) {
        return $npm.Source
    }

    return $null
}

function Start-ProcessIfPortClosed {
    param(
        [string]$Name,
        [int]$Port,
        [string]$FilePath,
        [string[]]$ArgumentList,
        [string]$WorkingDirectory,
        [string]$OutputLog,
        [string]$ErrorLog
    )

    if (Test-LocalPort -Port $Port) {
        Write-Log "$Name already listening on 127.0.0.1:$Port"
        return
    }

    if (-not (Test-Path $FilePath)) {
        Write-Log "$Name cannot start. Missing executable: $FilePath"
        return
    }

    Write-Log "Starting $Name on 127.0.0.1:$Port"
    Start-Process `
        -FilePath $FilePath `
        -ArgumentList $ArgumentList `
        -WorkingDirectory $WorkingDirectory `
        -RedirectStandardOutput $OutputLog `
        -RedirectStandardError $ErrorLog `
        -WindowStyle Hidden | Out-Null
}

Write-Log 'Arcade FixIt startup begin'

$laragonExe = 'C:\laragon\laragon.exe'
$laragonRunning = Get-Process laragon -ErrorAction SilentlyContinue
if ($laragonRunning) {
    Write-Log 'Laragon UI already running'
} elseif (Test-Path $laragonExe) {
    Write-Log 'Laragon UI is not running. Start Laragon manually if local MySQL/database demo is needed.'
} else {
    Write-Log 'Laragon executable not found at C:\laragon\laragon.exe'
}

$phpExe = Resolve-PhpExe
if ($phpExe) {
    Start-ProcessIfPortClosed `
        -Name 'Slim backend' `
        -Port 8000 `
        -FilePath $phpExe `
        -ArgumentList @('-S', '127.0.0.1:8000', '-t', 'public', 'public/index.php') `
        -WorkingDirectory $BackendRoot `
        -OutputLog (Join-Path $LogRoot 'backend.out.log') `
        -ErrorLog (Join-Path $LogRoot 'backend.err.log')
} else {
    Write-Log 'PHP executable not found. Backend was not started.'
}

$npmCmd = Resolve-NpmCmd
if ($npmCmd) {
    Start-ProcessIfPortClosed `
        -Name 'Vite frontend' `
        -Port 5173 `
        -FilePath $npmCmd `
        -ArgumentList @('run', 'dev', '--', '--host', '127.0.0.1', '--port', '5173') `
        -WorkingDirectory $FrontendRoot `
        -OutputLog (Join-Path $LogRoot 'frontend.out.log') `
        -ErrorLog (Join-Path $LogRoot 'frontend.err.log')
} else {
    Write-Log 'npm executable not found. Frontend was not started.'
}

Start-Sleep -Seconds 3
Write-Log ("Port check: backend={0}, frontend={1}, mysql={2}" -f (Test-LocalPort 8000), (Test-LocalPort 5173), (Test-LocalPort 3306))
Write-Log 'Arcade FixIt startup end'
