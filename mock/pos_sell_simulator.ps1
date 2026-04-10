param(
    [Parameter(Mandatory=$false)]
    [string]$ProductName,
    [Parameter(Mandatory=$true)]
    [string]$Color,
    [Parameter(Mandatory=$true)]
    [string]$Size,    
    [Parameter(Mandatory=$false)]
    [int]$Quantity = 1
)

$apiUrl = "http://localhost/backend/api_ui.php/sell"

if (-not $ProductName) {
    $ProductName = Read-Host "Введите название товара для продажи"
}

if ($Quantity -le 0) {
    $Quantity = [int](Read-Host "Введите количество для продажи")
}

$body = @{
    product_name = $ProductName
    color        = $Color
    size         = $Size
    quantity     = $Quantity
}

try {
    Write-Host "Отправка запроса на продажу товара '$ProductName' в количестве $Quantity..." -ForegroundColor Cyan
    $response = Invoke-RestMethod -Uri $apiUrl -Method Post -Body $body -ContentType "application/x-www-form-urlencoded"
    
    Write-Host "`nОтвет сервера:" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Error "Ошибка при вызове API: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $errorBody = $reader.ReadToEnd()
        Write-Host "Тело ошибки: $errorBody" -ForegroundColor Red
    }
    exit 1
}