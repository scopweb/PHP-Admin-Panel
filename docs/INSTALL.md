# Guia de Instalacion

Esta guia te ayudara a instalar y configurar el PHP Admin Panel en tu servidor.

---

## Requisitos del Sistema

### Servidor Web
- Apache 2.4+ con mod_rewrite habilitado
- Nginx 1.18+
- O cualquier servidor compatible con PHP

### PHP
- **Version**: PHP 8.0 o superior
- **Extensiones requeridas**:
  - PDO
  - pdo_mysql
  - mbstring
  - openssl (para generacion de tokens)

### Base de Datos
- MySQL 5.7+ o MariaDB 10.2+

---

## Instalacion Rapida

### 1. Clonar el repositorio

```bash
git clone https://github.com/scopweb/PHP-Admin-Panel.git
cd PHP-Admin-Panel
```

### 2. Crear la base de datos

```bash
mysql -u root -p
```

```sql
CREATE DATABASE your_db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE your_db_name;
SOURCE your_db_name.sql;
```

### 3. Configurar la conexion

Edita `Admin_config/connection.php` o usa variables de entorno:

#### Opcion A: Editar archivo directamente
```php
$db_config = [
    'host' => 'localhost',
    'user' => 'tu_usuario',
    'pass' => 'tu_contrasena',
    'name' => 'tu_base_de_datos',
    'charset' => 'utf8mb4',
];
```

#### Opcion B: Variables de entorno (recomendado)
```bash
export DB_HOST=localhost
export DB_USER=tu_usuario
export DB_PASS=tu_contrasena
export DB_NAME=tu_base_de_datos
```

### 4. Configurar permisos

```bash
# Asegurar permisos correctos
chmod 755 -R .
chmod 644 Admin_config/connection.php
```

### 5. Acceder al panel

Abre tu navegador y ve a:
```
http://tu-servidor/PHP-Admin-Panel/views/login.php
```

**Credenciales por defecto**:
- Usuario: `admin@google.com`
- Contrasena: `Pt123456789`

---

## Instalacion con Docker

### docker-compose.yml

```yaml
version: '3.8'

services:
  web:
    image: php:8.2-apache
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    environment:
      - DB_HOST=db
      - DB_USER=admin
      - DB_PASS=secretpassword
      - DB_NAME=admin_panel
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=rootpassword
      - MYSQL_DATABASE=admin_panel
      - MYSQL_USER=admin
      - MYSQL_PASSWORD=secretpassword
    volumes:
      - ./your_db_name.sql:/docker-entrypoint-initdb.d/init.sql
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

```bash
docker-compose up -d
```

---

## Configuracion de Apache

### Virtual Host

```apache
<VirtualHost *:80>
    ServerName admin.tudominio.com
    DocumentRoot /var/www/html/PHP-Admin-Panel/views

    <Directory /var/www/html/PHP-Admin-Panel>
        AllowOverride All
        Require all granted
    </Directory>

    # Redirigir a HTTPS (recomendado)
    # RewriteEngine On
    # RewriteCond %{HTTPS} off
    # RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    ErrorLog ${APACHE_LOG_DIR}/admin_error.log
    CustomLog ${APACHE_LOG_DIR}/admin_access.log combined
</VirtualHost>
```

### .htaccess (opcional)

Crea un archivo `.htaccess` en la raiz:

```apache
# Prevenir acceso directo a archivos de configuracion
<FilesMatch "^(connection|security)\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Headers de seguridad
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

---

## Configuracion de Nginx

```nginx
server {
    listen 80;
    server_name admin.tudominio.com;
    root /var/www/html/PHP-Admin-Panel/views;
    index index.php login.php;

    # Logs
    access_log /var/log/nginx/admin_access.log;
    error_log /var/log/nginx/admin_error.log;

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Denegar acceso a archivos de configuracion
    location ~ ^/Admin_config/ {
        deny all;
        return 404;
    }

    # Headers de seguridad
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

---

## Verificar Instalacion

### 1. Verificar version de PHP

```bash
php -v
# Debe mostrar PHP 8.0 o superior
```

### 2. Verificar extensiones

```bash
php -m | grep -E "pdo|mbstring|openssl"
# Debe mostrar: mbstring, openssl, pdo_mysql, PDO
```

### 3. Verificar conexion a base de datos

```bash
php -r "
require 'Admin_config/connection.php';
echo 'Conexion exitosa!';
"
```

### 4. Test de login

1. Accede a `/views/login.php`
2. Ingresa credenciales por defecto
3. Deberias ver el dashboard

---

## Solucion de Problemas

### Error: "PDO extension not found"

```bash
# Ubuntu/Debian
sudo apt install php8.2-mysql php8.2-pdo

# CentOS/RHEL
sudo yum install php-pdo php-mysqlnd

# Reiniciar servidor web
sudo systemctl restart apache2  # o nginx
```

### Error: "Database connection failed"

1. Verificar que MySQL esta corriendo:
   ```bash
   sudo systemctl status mysql
   ```

2. Verificar credenciales:
   ```bash
   mysql -u tu_usuario -p -h localhost tu_base_de_datos
   ```

3. Verificar que la base de datos existe:
   ```sql
   SHOW DATABASES;
   ```

### Error: "Permission denied"

```bash
# Corregir propietario
sudo chown -R www-data:www-data /var/www/html/PHP-Admin-Panel

# Corregir permisos
sudo chmod -R 755 /var/www/html/PHP-Admin-Panel
```

### Pagina en blanco

Habilitar errores temporalmente para debug:

```php
// Al inicio de cualquier archivo PHP
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

---

## Siguiente Paso

Una vez instalado, consulta la [Guia de Uso](USAGE.md) para aprender a usar el panel.
