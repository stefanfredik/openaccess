1. Laravel ER Diagram Generator (Recommended)
   Package yang bisa generate ERD dari Eloquent models:

```bash
composer require beyondcode/laravel-er-diagram-generator --dev
```

Lalu jalankan:

```bash
php artisan generate:erd output.png
```

# atau format lain

```php
php artisan generate:erd output.svg --format=svg 2. MySQL Workbench (Reverse Engineering)
```

Buka MySQL Workbench
Database → Reverse Engineer
Connect ke database Anda
Pilih tabel yang ingin divisualisasikan
Akan generate ERD yang bisa di-export ke PNG/PDF 3. dbdiagram.io (Online Tool)
Export SQL schema lalu import:

# Export schema dari MySQL

```bash
mysqldump -u root -p --no-data openaccess > schema.sql
```

Lalu upload ke dbdiagram.io - bisa convert SQL ke visual diagram.

4. DBeaver (Free Database Tool)
   Connect ke database
   Right-click database → ER Diagram
   Export ke PNG/PDF

```

```

```

```
