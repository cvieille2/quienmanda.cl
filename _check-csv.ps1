$files = Get-ChildItem "C:\Users\Usuario\Desktop\multiplicar-dinero\input\gsc_2026-05-17"
$i = 0
foreach ($f in $files) {
    Write-Host ("$i : " + $f.Name)
    $i++
}
# Páginas.csv is usually index 6
$target = $files[6]
Write-Host "`nUsando: " $target.Name -ForegroundColor Green
$raw = Import-Csv $target.FullName -Encoding UTF8
Write-Host ("Filas: " + $raw.Count) -ForegroundColor Cyan
$names = $raw[0].PSObject.Properties.Name
$j = 0
foreach ($n in $names) {
    Write-Host ("$j : [" + $n + "]")
    $j++
}
Write-Host ("`nPrimera URL: " + $raw[0].$($names[0]))
Write-Host ("Primeros clics: " + $raw[0].$($names[1]))
