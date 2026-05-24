$files = Get-ChildItem "C:\Users\Usuario\Desktop\multiplicar-dinero\input\gsc_2026-05-17"
$scFile = $files[6]
$raw = Import-Csv $scFile.FullName -Encoding UTF8

$firstRow = $raw[0]
$props = $firstRow.PSObject.Properties
$i = 0
foreach ($p in $props) {
    Write-Host ("$i : Name=[" + $p.Name + "] Value=[" + $p.Value + "]")
    $i++
}

# Access by index
Write-Host "`nAcceso por indice:" -ForegroundColor Yellow
$val = $firstRow.($props.Name[0])
Write-Host ("Prop[0] = " + $val)
