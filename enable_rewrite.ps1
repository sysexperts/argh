# Enable mod_rewrite in XAMPP
$httpd_conf = "C:\xampp\apache\conf\httpd.conf"
$content = Get-Content $httpd_conf
$content = $content -replace '#LoadModule rewrite_module modules/mod_rewrite.so', 'LoadModule rewrite_module modules/mod_rewrite.so'
Set-Content $httpd_conf $content
Write-Host "mod_rewrite aktiviert! Bitte Apache in XAMPP neu starten."
