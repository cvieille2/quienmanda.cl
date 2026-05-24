$scDir = "C:\Users\Usuario\Desktop\multiplicar-dinero\input\gsc_2026-05-17"
$files = Get-ChildItem $scDir
$scFile = $files[6]

$scRaw = Import-Csv $scFile.FullName -Encoding UTF8
Write-Host ("Filas: " + $scRaw.Count) -ForegroundColor Cyan

$motelFichas = @()

foreach ($row in $scRaw) {
    $props = $row.PSObject.Properties
    $url = $props.Name[0]
    $val = $row.$url
    if (-not $val) { continue }

    $clics = $val
    # Oops, need to get by index approach
    break
}

# Better approach: iterate and access by property index
$urlPropName = $scRaw[0].PSObject.Properties.Name[0]
$clicsPropName = $scRaw[0].PSObject.Properties.Name[1]
$impPropName = $scRaw[0].PSObject.Properties.Name[2]
$ctrPropName = $scRaw[0].PSObject.Properties.Name[3]
$posPropName = $scRaw[0].PSObject.Properties.Name[4]

Write-Host ("Props: " + $urlPropName + " | " + $clicsPropName + " | " + $impPropName + " | " + $ctrPropName + " | " + $posPropName) -ForegroundColor Cyan

foreach ($row in $scRaw) {
    $url = $row.$urlPropName
    if (-not $url) { continue }

    $cleanUrl = $url -replace "#.*$", ""
    $slug = $cleanUrl -replace "https://infomoteles.cl/", "" -replace "/$", ""

    $parts = $slug.Split("/")
    $lastPart = $parts[-1]

    $isMotel = $lastPart -match "^(motel-|hostal-|cabanas-|alojamiento-|lux-suites|la-luna|la-gata)"
    if (-not $isMotel) {
        $isMotel = $slug -match "^(motel-|hostal-|cabanas-)"
    }

    if ($isMotel) {
        $clics = [int]::Parse($row.$clicsPropName)
        $imp = [int]::Parse($row.$impPropName)
        $ctr = $row.$ctrPropName
        $posStr = $row.$posPropName -replace ",", "."
        $pos = [double]::Parse($posStr)

        $motelFichas += [PSCustomObject]@{
            slug = $slug
            clics = $clics
            impresiones = $imp
            ctr = $ctr
            posicion = $pos
        }
    }
}

Write-Host ("`nFichas de motel (con duplicados): " + $motelFichas.Count) -ForegroundColor Green

$deduped = $motelFichas | Sort-Object slug, impresiones -Descending | Group-Object slug | ForEach-Object { $_.Group[0] }
Write-Host ("Fichas unicas: " + $deduped.Count) -ForegroundColor Green

$deduped | Sort-Object impresiones -Descending | Export-Csv "entregables/todas-las-fichas.csv" -NoTypeInformation -Encoding UTF8

Write-Host "`n=== TODAS LAS FICHAS POR IMPRESIONES ===" -ForegroundColor Cyan
$deduped | Sort-Object impresiones -Descending | Format-Table slug, clics, impresiones, ctr, posicion -AutoSize | Out-String -Width 140
