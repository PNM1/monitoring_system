param(
    [Parameter(Mandatory=$true)]
    [string]$ProductName
)

$apiUrl = "http://localhost/backend/api_ui.php/restock"

$body = @{
    product_name = $ProductName
    quantity = -1
}

try {
    $response = Invoke-RestMethod -Uri $apiUrl -Method Post -Body $body -ContentType "application/x-www-form-urlencoded"
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Error "Ошибка: $($_.Exception.Message)"
    exit 1
}