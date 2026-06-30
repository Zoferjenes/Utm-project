$ErrorActionPreference = 'Continue'

$ProjectRoot = Split-Path -Parent $PSScriptRoot
$ApiUrl = 'https://fixit-api-production-cc68.up.railway.app'
$FrontendUrl = 'https://arcade-fixit-fiyandha.netlify.app'

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

Write-Host 'Arcade FixIt local demo status'
Write-Host "Project: $ProjectRoot"
Write-Host ''

try {
    $task = Get-ScheduledTask -TaskName 'ArcadeFixItLocalDemoServer' -ErrorAction Stop
    Write-Host "Startup task: installed ($($task.State))"
} catch {
    Write-Host 'Startup task: not installed'
}

Write-Host ("Laragon UI: {0}" -f [bool](Get-Process laragon -ErrorAction SilentlyContinue))
Write-Host ("Local MySQL 3306: {0}" -f (Test-LocalPort 3306))
Write-Host ("Local backend 8000: {0}" -f (Test-LocalPort 8000))
Write-Host ("Local frontend 5173: {0}" -f (Test-LocalPort 5173))
Write-Host ''

try {
    $health = Invoke-RestMethod -Uri "$ApiUrl/health" -TimeoutSec 15
    Write-Host "Railway API: $($health.status), database $($health.database)"
} catch {
    Write-Host "Railway API: failed ($($_.Exception.Message))"
}

try {
    $front = Invoke-WebRequest -Uri $FrontendUrl -UseBasicParsing -TimeoutSec 15
    Write-Host "Netlify frontend: HTTP $($front.StatusCode)"
} catch {
    Write-Host "Netlify frontend: failed ($($_.Exception.Message))"
}

Write-Host ''
Write-Host 'Local URLs:'
Write-Host '  http://127.0.0.1:5173'
Write-Host '  http://127.0.0.1:8000/health'
Write-Host ''
Write-Host 'Cloud URLs:'
Write-Host "  $FrontendUrl"
Write-Host "  $ApiUrl/health"
