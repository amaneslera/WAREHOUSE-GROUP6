# WAREHOUSE-GROUP6 Setup Instructions

## Prerequisites
- XAMPP (Apache, MySQL, PHP 8.1+)
- Composer
- Git

## Installation Steps

### 1. Clone the Repository
```bash
git clone https://github.com/amaneslera/WAREHOUSE-GROUP6.git
cd WAREHOUSE-GROUP6
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
Copy the example environment file and configure it:
```bash
cp .env.example .env
```

Edit `.env` file and update the database settings:
```ini
database.default.hostname = localhost
database.default.database = warehouse_db
database.default.username = root
database.default.password = your_password_here
database.default.port = 3306
```

Also update the base URL to match your setup:
```ini
app.baseURL = 'http://localhost/WAREHOUSE-GROUP6/'
```

### 4. Create Database
Open phpMyAdmin or MySQL command line and create the database:
```sql
CREATE DATABASE warehouse_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### 5. Run Migrations
Execute the database migrations to create all tables:
```bash
php spark migrate
```

### 6. Seed Database (Optional)
Seed the database with sample users:
```bash
php spark db:seed UsersSeeder
```

This will create 8 test users with the following credentials:

| Role | Email | Password |
|------|-------|----------|
| Warehouse Manager | manager@example.com | manager123 |
| Warehouse Staff | staff@example.com | staff123 |
| Inventory Auditor | auditor@example.com | auditor123 |
| Procurement Officer | procurement@example.com | procure123 |
| AP Clerk | apclerk@example.com | apclerk123 |
| AR Clerk | arclerk@example.com | arclerk123 |
| IT Administrator | itadmin@example.com | itadmin123 |
| Top Management | topmanagement@example.com | topmanage123 |

### 7. Configure Apache (if needed)
Ensure `mod_rewrite` is enabled in Apache's `httpd.conf`:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

And ensure `AllowOverride All` is set for your htdocs directory:
```apache
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>
```

### 8. Start Apache and MySQL
Start Apache and MySQL from XAMPP Control Panel.

### 9. Access the Application
Open your browser and navigate to:
```
http://localhost/WAREHOUSE-GROUP6/
```

## Default Login Credentials
- Email: `itadmin@example.com`
- Password: `itadmin123`

## Troubleshooting

### 404 Not Found Error
1. Check that Apache's `mod_rewrite` is enabled
2. Verify `.htaccess` files have correct `RewriteBase` paths
3. Restart Apache after making changes

### Database Connection Error
1. Verify MySQL is running in XAMPP
2. Check database credentials in `.env` file
3. Ensure database exists

### Permission Errors
Make sure the `writable` folder has write permissions:
```bash
chmod -R 777 writable/
```

## Project Structure
```
WAREHOUSE-GROUP6/
├── app/
│   ├── Config/          # Configuration files
│   ├── Controllers/     # Application controllers
│   ├── Models/          # Database models
│   ├── Views/           # View templates
│   └── Database/        # Migrations and seeds
├── public/              # Web root (index.php, assets)
├── writable/            # Cache, logs, sessions
├── system/              # CodeIgniter 4 framework
└── .env                 # Environment configuration (create from .env.example)
```

## API Documentation
See [API_REFERENCE.md](API_REFERENCE.md) for complete API documentation.

## System Documentation
See [WITMS_README.md](WITMS_README.md) for detailed system documentation.
