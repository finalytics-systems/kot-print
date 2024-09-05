$script1 = @"
cd C:\xampp\htdocs\qt-print
php artisan serve --host localhost --port 8000
"@

$script2 = @"
ngrok http --domain=gratefully-native-caiman.ngrok-free.app 8000
"@

Start-Process powershell -ArgumentList "-NoExit", "-Command", $script1

Start-Process powershell -ArgumentList "-NoExit", "-Command", $script2
