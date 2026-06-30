$ErrorActionPreference = 'Stop'

$ProjectRoot = Split-Path -Parent $PSScriptRoot
$StartScript = Join-Path $PSScriptRoot 'start-local-demo-server.ps1'
$TaskName = 'ArcadeFixItLocalDemoServer'
$UserId = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name

if (-not (Test-Path $StartScript)) {
    throw "Missing startup script: $StartScript"
}

$action = New-ScheduledTaskAction `
    -Execute 'powershell.exe' `
    -Argument "-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$StartScript`"" `
    -WorkingDirectory $ProjectRoot

$trigger = New-ScheduledTaskTrigger -AtLogOn -User $UserId
$trigger.Delay = 'PT30S'

$principal = New-ScheduledTaskPrincipal `
    -UserId $UserId `
    -LogonType Interactive `
    -RunLevel Limited

$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 15) `
    -MultipleInstances IgnoreNew

Register-ScheduledTask `
    -TaskName $TaskName `
    -Action $action `
    -Trigger $trigger `
    -Principal $principal `
    -Settings $settings `
    -Description 'Starts Laragon, Arcade FixIt Slim backend, and Vue/Vite frontend for local CPAD demo.' `
    -Force | Out-Null

Write-Host "Installed startup task: $TaskName"
Write-Host "User: $UserId"
Write-Host "Script: $StartScript"

Start-ScheduledTask -TaskName $TaskName
Write-Host 'Started task once for immediate verification.'
