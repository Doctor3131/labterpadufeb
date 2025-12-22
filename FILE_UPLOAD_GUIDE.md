### Update PHP Upload Limits

Jika perlu menaikkan batas upload di server, edit file `php.ini`:

```ini
; Maximum allowed size for uploaded files.
upload_max_filesize = 2M

; Maximum size of POST data that PHP will accept.
post_max_size = 3M

; Maximum execution time of each script (in seconds)
max_execution_time = 60

; Maximum amount of memory a script may consume
memory_limit = 256M
```

Setelah edit, restart web server:
```bash
# Apache
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Update Laravel Upload Validation

Batas validasi diatur di `BookingController.php`:

```php
'document' => 'nullable|file|mimes:pdf|max:2048', // Max 2MB (2048 KB)
```

Ubah nilai `max:2048` sesuai kebutuhan (dalam KB).
