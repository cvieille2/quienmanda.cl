$pair = "cvieille:QCZg BNQs mcnf HMXE OvBm MXpz"
$bytes = [System.Text.Encoding]::ASCII.GetBytes($pair)
$b64 = [System.Convert]::ToBase64String($bytes)
$headers = @{ Authorization = "Basic " + $b64 }
$site = "https://infomoteles.cl/wp-json/wp/v2"

# Read the fichas list from CSV
$fichas = Import-Csv "entregables/todas-las-fichas.csv" -Encoding UTF8
Write-Host ("Fichas a procesar: " + $fichas.Count) -ForegroundColor Cyan

$results = @()
$processed = 0

foreach ($f in $fichas) {
    $processed++
    if ($processed % 20 -eq 0) { Write-Host ("Procesados: $processed / " + $fichas.Count) -ForegroundColor Yellow }

    # Get slug from the SC url
    $slug = $f.slug
    if (-not $slug) { continue }

    try {
        $post = Invoke-RestMethod -Uri ($site + "/posts?slug=" + $slug) -Headers $headers -Method Get -TimeoutSec 10
        if ($post.Count -eq 0) {
            # Try with last part of slug
            $slugParts = $slug.Split("/")
            $lastSlug = $slugParts[-1]
            $post = Invoke-RestMethod -Uri ($site + "/posts?slug=" + $lastSlug) -Headers $headers -Method Get -TimeoutSec 10
        }

        if ($post.Count -gt 0) {
            $content = $post[0].content.rendered
            $title = $post[0].title.rendered

            # Extract email from content
            $emailMatch = [regex]::Match($content, "[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}")
            $email = if ($emailMatch.Success) { $emailMatch.Value } else { "" }

            # Extract phone from content (Chilean phone patterns)
            $phoneMatch = [regex]::Match($content, "(\+56\s*[0-9\s\-\(\)]{7,20}|[0-9]{7,12})")
            $phone = if ($phoneMatch.Success) { $phoneMatch.Value.Trim() } else { "" }

            $results += [PSCustomObject]@{
                slug = $slug
                title = $title
                email = $email
                telefono = $phone
                clics = $f.clics
                impresiones = $f.impresiones
                ctr = $f.ctr
                posicion = $f.posicion
            }
        } else {
            $results += [PSCustomObject]@{
                slug = $slug
                title = ""
                email = ""
                telefono = ""
                clics = $f.clics
                impresiones = $f.impresiones
                ctr = $f.ctr
                posicion = $f.posicion
            }
        }
    } catch {
        $results += [PSCustomObject]@{
            slug = $slug
            title = ""
            email = "ERROR: " + $_.Exception.Message.Substring(0, [Math]::Min(50, $_.Exception.Message.Length))
            telefono = ""
            clics = $f.clics
            impresiones = $f.impresiones
            ctr = $f.ctr
            posicion = $f.posicion
        }
    }
}

$results | Sort-Object impresiones -Descending | Export-Csv "entregables/fichas-con-contactos.csv" -NoTypeInformation -Encoding UTF8

Write-Host "`nCompletado. Resultados:" -ForegroundColor Green
$conEmail = $results | Where-Object { $_.email -ne "" -and $_.email -notmatch "^ERROR" }
Write-Host ("Con email encontrado: " + $conEmail.Count) -ForegroundColor Green
$conTelefono = $results | Where-Object { $_.telefono -ne "" }
Write-Host ("Con telefono encontrado: " + $conTelefono.Count) -ForegroundColor Green

Write-Host "`nFichas CON EMAIL (top por impresiones):" -ForegroundColor Cyan
$conEmail | Sort-Object impresiones -Descending | Select-Object -First 30 | Format-Table slug, email, telefono, impresiones -AutoSize | Out-String -Width 120
