param(
    [string]$Port = "8080",
    [string]$BasePath = "/buffet-chay"
)

$ErrorActionPreference = "Stop"

$localNgrok = Join-Path (Get-Location) "ngrok.exe"
$ngrok = Get-Command ngrok -ErrorAction SilentlyContinue
if ((-not $ngrok) -and (Test-Path $localNgrok)) {
    $ngrok = Get-Item $localNgrok
}
if (-not $ngrok) {
    Write-Host "Khong tim thay ngrok trong PATH."
    Write-Host "Cai ngrok hoac dat ngrok.exe vao thu muc project, dang nhap token, roi chay lai script nay."
    exit 1
}

$ngrokPath = if ($ngrok.Source) { $ngrok.Source } else { $ngrok.FullName }

docker compose up -d

$existing = Get-Process ngrok -ErrorAction SilentlyContinue
if (-not $existing) {
    Start-Process -FilePath $ngrokPath -ArgumentList @("http", $Port) -WindowStyle Hidden
}

$publicUrl = ""
for ($i = 0; $i -lt 20; $i++) {
    try {
        $api = Invoke-RestMethod -Uri "http://127.0.0.1:4040/api/tunnels" -TimeoutSec 2
        $httpsTunnel = $api.tunnels | Where-Object { $_.public_url -like "https://*" } | Select-Object -First 1
        if ($httpsTunnel) {
            $publicUrl = $httpsTunnel.public_url
            break
        }
    } catch {
    }
    Start-Sleep -Milliseconds 700
}

if ($publicUrl -eq "") {
    Write-Host "Da mo ngrok nhung chua lay duoc public URL."
    Write-Host "Mo http://127.0.0.1:4040 de xem tunnel."
    exit 1
}

$basePath = "/" + $BasePath.Trim("/")
$publicBaseUrl = $publicUrl.TrimEnd("/") + $basePath
@(
    "BASE_URL=$publicBaseUrl",
    "PUBLIC_BASE_URL=$publicBaseUrl"
) | Set-Content -Path ".env" -Encoding ASCII

docker compose up -d web

Write-Host ""
Write-Host "Ngrok URL cho demo:"
Write-Host $publicBaseUrl
Write-Host ""
Write-Host "QR goi mon se dung URL nay sau khi in lai phieu."
