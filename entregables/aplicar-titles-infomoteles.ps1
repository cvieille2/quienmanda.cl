# APLICAR TITLES Y META DESCRIPTIONS - infomoteles.cl
# Instrucciones:
#   1. Reemplaza TU_TOKEN con un Application Password de WordPress
#      (Usuarios > Perfil > Contraseña de Aplicación)
#   2. Ejecuta: .\entregables\aplicar-titles-infomoteles.ps1
#   3. Verifica: Los títulos deben aparecer actualizados en el sitio

$TOKEN = "TU_TOKEN"  # ← REEMPLAZA ESTO
$SITE = "https://infomoteles.cl/wp-json/wp/v2"

# Función para actualizar title + yoast meta
function Update-PostMeta {
    param([string]$Slug, [string]$NewTitle, [string]$NewMetaDesc)

    $body = @{
        title = $NewTitle
        meta   = @{
            _yoast_wpseo_title    = $NewTitle
            _yoast_wpseo_metadesc = $NewMetaDesc
        }
    } | ConvertTo-Json

    try {
        # Primero buscar el post por slug
        $search = Invoke-RestMethod -Uri "$SITE/posts?slug=$Slug" -Headers @{
            "Authorization" = "Bearer $TOKEN"
            "Content-Type"  = "application/json"
        } -Method Get

        if ($search.Count -eq 0) {
            Write-Host "✖ No se encontró post con slug '$Slug'" -ForegroundColor Red
            return
        }

        $postId = $search[0].id
        $result = Invoke-RestMethod -Uri "$SITE/posts/$postId" -Headers @{
            "Authorization" = "Bearer $TOKEN"
            "Content-Type"  = "application/json"
        } -Method Post -Body $body

        Write-Host "✔ $($result.title.rendered)" -ForegroundColor Green
    } catch {
        Write-Host "✖ Error en '$Slug': $_" -ForegroundColor Red
    }
}

Write-Host "=== APLICANDO CAMBIOS A INFOMOTELES.CL ===" -ForegroundColor Cyan
Write-Host ""

# 1. Motel Los Sauces - Copiapó
Update-PostMeta -Slug "motel-los-sauces" `
    -NewTitle "Motel Los Sauces Copiapó | Habitaciones, Precios y Reservas" `
    -NewMetaDesc "Reserva en Motel Los Sauces Copiapó. Habitaciones con jacuzzi, estacionamiento privado y tarifas por hora. Perfecto para una escapada en pareja."

# 2. Motel Yugos - Puerto Montt
Update-PostMeta -Slug "motel-yugos" `
    -NewTitle "Motel Yugos Puerto Montt | Habitaciones con Jacuzzi y Piedras Calientes" `
    -NewMetaDesc "Vive una experiencia única en Motel Yugos Puerto Montt. Habitaciones temáticas, jacuzzi, piedras calientes y tarifas accesibles. Reserva hoy."

# 3. Motel Los Gatitos - Macul
Update-PostMeta -Slug "motel-los-gatitos-macul" `
    -NewTitle "Motel Los Gatitos Macul | Habitaciones Temáticas y Jacuzzi" `
    -NewMetaDesc "Disfruta de la mejor experiencia en Motel Los Gatitos Macul. Habitaciones temáticas, jacuzzi privado y ambiente romántico. Ideal para parejas."

Write-Host ""
Write-Host "=== LISTO ===" -ForegroundColor Cyan
Write-Host "Verifica en: https://infomoteles.cl/wp-json/wp/v2/posts?slug=motel-los-sauces"
