$pair = "cvieille:QCZg BNQs mcnf HMXE OvBm MXpz"
$bytes = [System.Text.Encoding]::ASCII.GetBytes($pair)
$b64 = [System.Convert]::ToBase64String($bytes)
$headers = @{ Authorization = "Basic " + $b64 }
$site = "https://infomoteles.cl/wp-json/wp/v2"

# Try to get the count first
try {
    $resp = Invoke-WebRequest -Uri ($site + "/posts?per_page=1") -Headers $headers -Method Get -TimeoutSec 15
    $total = $resp.Headers["X-WP-Total"]
    $totalPages = $resp.Headers["X-WP-TotalPages"]
    Write-Host ("Total posts: " + $total) -ForegroundColor Cyan
    Write-Host ("Total pages: " + $totalPages) -ForegroundColor Cyan
} catch {
    Write-Host "Error getting count: $_" -ForegroundColor Red
}

# Get first 50 posts
try {
    $posts = Invoke-RestMethod -Uri ($site + "/posts?per_page=50") -Headers $headers -Method Get -TimeoutSec 30
    Write-Host ("`nPrimeros " + $posts.Count + " posts:") -ForegroundColor Green
    foreach ($p in $posts) {
        $title = $p.title.rendered
        if ($title.Length -gt 60) { $title = $title.Substring(0,60) + "..." }
        Write-Host ($p.id.ToString().PadRight(6) + " | " + $p.slug.PadRight(40) + " | " + $p.type.PadRight(10) + " | " + $title)
    }
} catch {
    Write-Host "Error getting posts: $_" -ForegroundColor Red
}

# Also list pages
try {
    $pages = Invoke-RestMethod -Uri ($site + "/pages?per_page=50") -Headers $headers -Method Get -TimeoutSec 30
    Write-Host ("`nPaginas: " + $pages.Count) -ForegroundColor Green
    foreach ($p in $pages) {
        $title = $p.title.rendered
        if ($title.Length -gt 60) { $title = $title.Substring(0,60) + "..." }
        Write-Host ($p.id.ToString().PadRight(6) + " | " + $p.slug.PadRight(40) + " | " + $title)
    }
} catch {
    Write-Host "Error getting pages: $_" -ForegroundColor Red
}
