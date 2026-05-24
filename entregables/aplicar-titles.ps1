$user = "cvieille"
$pass = "QCZg BNQs mcnf HMXE OvBm MXpz"
$pair = $user + ":" + $pass
$bytes = [System.Text.Encoding]::ASCII.GetBytes($pair)
$base64 = [System.Convert]::ToBase64String($bytes)
$headers = @{ Authorization = "Basic " + $base64 }
$site = "https://infomoteles.cl/wp-json/wp/v2"

function Update-Post($Slug, $NewTitle, $NewMeta) {
    Write-Host ("`nBuscando slug: " + $Slug) -ForegroundColor Cyan
    $found = Invoke-RestMethod -Uri ($site + "/posts?slug=" + $Slug) -Headers $headers -Method Get
    if ($found.Count -eq 0) {
        Write-Host "  - No encontrado" -ForegroundColor Red
        return
    }
    $id = $found[0].id
    Write-Host ("  ID: " + $id)
    Write-Host ("  Title actual: " + $found[0].title.rendered)

    $body = @{
        title = $NewTitle
        meta  = @{
            _yoast_wpseo_title    = $NewTitle
            _yoast_wpseo_metadesc = $NewMeta
        }
    } | ConvertTo-Json

    $result = Invoke-RestMethod -Uri ($site + "/posts/" + $id) -Headers $headers -Method Post -Body $body -ContentType "application/json"
    Write-Host ("  OK: " + $result.title.rendered) -ForegroundColor Green
}

Update-Post "motel-los-sauces" "Motel Los Sauces Copiapo | Habitaciones, Precios y Reservas" "Reserva en Motel Los Sauces Copiapo. Habitaciones con jacuzzi, estacionamiento privado y tarifas por hora. Perfecto para una escapada en pareja."

Update-Post "motel-yugos" "Motel Yugos Puerto Montt | Habitaciones con Jacuzzi y Piedras Calientes" "Vive una experiencia unica en Motel Yugos Puerto Montt. Habitaciones tematicas, jacuzzi, piedras calientes y tarifas accesibles. Reserva hoy."

Update-Post "motel-los-gatitos-macul" "Motel Los Gatitos Macul | Habitaciones Tematicas y Jacuzzi" "Disfruta de la mejor experiencia en Motel Los Gatitos Macul. Habitaciones tematicas, jacuzzi privado y ambiente romantico. Ideal para parejas."

Write-Host "`n=== LISTO ===" -ForegroundColor Green
