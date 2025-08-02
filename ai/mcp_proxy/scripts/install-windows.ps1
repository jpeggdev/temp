# Installation script for Windows
# Run this script as Administrator

$ErrorActionPreference = "Stop"

$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectPath = Split-Path -Parent $scriptPath
$serviceName = "MCPProxyServer"
$displayName = "MCP Proxy Server"
$description = "Model Context Protocol Proxy Server"

Write-Host "Installing MCP Proxy Server for Windows..." -ForegroundColor Green

# Check if running as Administrator
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "This script must be run as Administrator. Exiting..." -ForegroundColor Red
    exit 1
}

# Check if Node.js is installed
try {
    $nodeVersion = node --version
    Write-Host "Node.js version: $nodeVersion" -ForegroundColor Cyan
} catch {
    Write-Host "Node.js is not installed. Please install Node.js first." -ForegroundColor Red
    exit 1
}

# Build the project
Write-Host "Building the project..." -ForegroundColor Yellow
Set-Location $projectPath
npm install
npm run build

# Create a Windows service using node-windows (install it first)
Write-Host "Installing node-windows..." -ForegroundColor Yellow
npm install -g node-windows

# Create service wrapper script
$serviceScript = @"
const Service = require('node-windows').Service;
const path = require('path');

// Create a new service object
const svc = new Service({
  name: '$serviceName',
  description: '$description',
  script: path.join(__dirname, 'dist', 'index.js'),
  nodeOptions: [
    '--harmony',
    '--max_old_space_size=4096'
  ],
  env: [
    {
      name: 'NODE_ENV',
      value: 'production'
    },
    {
      name: 'MCP_PROXY_WEB_PASSWORD',
      value: 'changeme'
    },
    {
      name: 'MCP_PROXY_SESSION_SECRET',
      value: 'change-this-secret-key'
    }
  ]
});

// Listen for the "install" event
svc.on('install', function() {
  console.log('Service installed successfully!');
  svc.start();
});

// Install the service
svc.install();
"@

$serviceScript | Out-File -FilePath "$projectPath\install-service.js" -Encoding UTF8

# Run the service installation
Write-Host "Installing Windows service..." -ForegroundColor Yellow
node "$projectPath\install-service.js"

# Create a scheduled task as an alternative
Write-Host "Creating scheduled task as backup..." -ForegroundColor Yellow
$action = New-ScheduledTaskAction -Execute "node.exe" -Argument "`"$projectPath\dist\index.js`"" -WorkingDirectory $projectPath
$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId "$env:USERNAME" -LogonType ServiceAccount -RunLevel Highest
$settings = New-ScheduledTaskSettingsSet -RestartInterval (New-TimeSpan -Minutes 1) -RestartCount 3 -StartWhenAvailable

Register-ScheduledTask -TaskName "MCPProxyServer" -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description $description

Write-Host ""
Write-Host "Installation complete!" -ForegroundColor Green
Write-Host ""
Write-Host "The MCP Proxy Server has been installed as a Windows service and scheduled task."
Write-Host "It will start automatically on system boot."
Write-Host ""
Write-Host "Useful commands:" -ForegroundColor Cyan
Write-Host "  Check service:     Get-Service $serviceName"
Write-Host "  Start service:     Start-Service $serviceName"
Write-Host "  Stop service:      Stop-Service $serviceName"
Write-Host "  Check task:        Get-ScheduledTask -TaskName MCPProxyServer"
Write-Host ""
Write-Host "Web interface: http://localhost:3001" -ForegroundColor Yellow
Write-Host "Default password: 'admin' (change the environment variables in the service configuration)" -ForegroundColor Yellow