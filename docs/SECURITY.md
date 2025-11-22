# Guia de Seguridad

Esta guia explica las medidas de seguridad implementadas y las mejores practicas para produccion.

---

## Seguridad Implementada

### 1. Prevencion de SQL Injection

**Como funciona:**
- Todas las consultas usan PDO con prepared statements
- Los parametros se pasan por separado, nunca concatenados

**Ejemplo:**
```php
// SEGURO - Usar siempre
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);

// INSEGURO - Nunca hacer esto
$sql = "SELECT * FROM users WHERE id = $userId"; // VULNERABLE!
```

### 2. Prevencion de XSS (Cross-Site Scripting)

**Como funciona:**
- Funcion `escape()` sanitiza todas las salidas HTML
- Usa `htmlspecialchars()` con ENT_QUOTES y UTF-8

**Uso:**
```php
// En vistas PHP
<p>Hola, <?= escape($nombre) ?></p>
<input value="<?= escape($valor) ?>">
```

### 3. Proteccion CSRF

**Como funciona:**
- Cada sesion tiene un token unico
- Los formularios POST incluyen el token
- El servidor valida el token antes de procesar

**Uso en formularios:**
```php
<form method="post">
    <?= csrf_field() ?>
    <!-- campos del formulario -->
</form>
```

**Validacion en backend:**
```php
if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
    die('Token CSRF invalido');
}
```

### 4. Hash de Contrasenas

**Como funciona:**
- Usa `password_hash()` con algoritmo bcrypt
- Cost factor automatico segun hardware
- Migracion automatica de MD5 legacy

**Funciones:**
```php
// Hashear nueva contrasena
$hash = hash_password($password);

// Verificar contrasena
if (verify_password($inputPassword, $storedHash)) {
    // Contrasena correcta
}
```

### 5. Gestion Segura de Sesiones

**Caracteristicas:**
- `session.use_strict_mode = 1`
- `session.use_only_cookies = 1`
- `session.cookie_httponly = 1`
- `session.cookie_samesite = Strict`
- Regeneracion de ID post-login

---

## Configuracion para Produccion

### 1. HTTPS Obligatorio

**Apache (.htaccess):**
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name tudominio.com;
    return 301 https://$server_name$request_uri;
}
```

**PHP (forzar cookies seguras):**
```php
// En security.php, modificar init_secure_session()
ini_set('session.cookie_secure', '1'); // Solo HTTPS
```

### 2. Headers de Seguridad

Agrega estos headers en tu servidor:

**Apache (.htaccess):**
```apache
<IfModule mod_headers.c>
    # Prevenir MIME sniffing
    Header set X-Content-Type-Options "nosniff"

    # Prevenir clickjacking
    Header set X-Frame-Options "SAMEORIGIN"

    # Filtro XSS del navegador
    Header set X-XSS-Protection "1; mode=block"

    # Content Security Policy
    Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;"

    # Referrer Policy
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

**Nginx:**
```nginx
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Content-Security-Policy "default-src 'self';" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

### 3. Variables de Entorno

Nunca guardes credenciales en el codigo. Usa variables de entorno:

**Linux/Apache:**
```bash
# /etc/apache2/envvars o .htaccess
SetEnv DB_HOST localhost
SetEnv DB_USER usuario_produccion
SetEnv DB_PASS contrasena_segura
SetEnv DB_NAME base_de_datos
```

**PHP-FPM:**
```ini
; /etc/php/8.2/fpm/pool.d/www.conf
env[DB_HOST] = localhost
env[DB_USER] = usuario_produccion
env[DB_PASS] = contrasena_segura
env[DB_NAME] = base_de_datos
```

**Docker:**
```yaml
environment:
  - DB_HOST=db
  - DB_USER=usuario
  - DB_PASS=contrasena
  - DB_NAME=base_datos
```

### 4. Permisos de Archivos

```bash
# Directorios: 755
find /var/www/html/admin -type d -exec chmod 755 {} \;

# Archivos PHP: 644
find /var/www/html/admin -type f -name "*.php" -exec chmod 644 {} \;

# Archivos de configuracion: 640
chmod 640 Admin_config/connection.php
chmod 640 Admin_config/security.php

# Propietario: www-data (o tu usuario web)
chown -R www-data:www-data /var/www/html/admin
```

### 5. Proteger Archivos Sensibles

**Apache (.htaccess en raiz):**
```apache
# Denegar acceso a archivos de configuracion
<FilesMatch "^(connection|security)\.php$">
    Require all denied
</FilesMatch>

# Denegar acceso a .sql
<FilesMatch "\.sql$">
    Require all denied
</FilesMatch>

# Denegar acceso a .md
<FilesMatch "\.md$">
    Require all denied
</FilesMatch>
```

**Nginx:**
```nginx
location ~ /Admin_config/ {
    deny all;
    return 404;
}

location ~ \.(sql|md)$ {
    deny all;
    return 404;
}
```

---

## Mejoras Recomendadas

### 1. Rate Limiting para Login

Implementa limite de intentos de login:

```php
// En login_validate.php, agregar antes de validar
function check_rate_limit(PDO $conn, string $ip): bool
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts
        FROM login_attempts
        WHERE ip = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $stmt->execute(['ip' => $ip]);
    $result = $stmt->fetch();

    return $result['attempts'] < 5; // Max 5 intentos en 15 min
}

function log_login_attempt(PDO $conn, string $ip, bool $success): void
{
    $stmt = $conn->prepare("
        INSERT INTO login_attempts (ip, success, created_at)
        VALUES (:ip, :success, NOW())
    ");
    $stmt->execute(['ip' => $ip, 'success' => $success ? 1 : 0]);
}

// Tabla SQL necesaria
/*
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    INDEX idx_ip_time (ip, created_at)
);
*/
```

### 2. Logging de Seguridad

```php
function log_security_event(string $event, array $data = []): void
{
    $logFile = '/var/log/admin-panel/security.log';
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['adm_Id'] ?? null,
        'data' => $data,
    ];

    file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);
}

// Uso
log_security_event('login_success', ['username' => $username]);
log_security_event('login_failed', ['username' => $username]);
log_security_event('password_changed', ['user_id' => $userId]);
```

### 3. Two-Factor Authentication (2FA)

Considera implementar 2FA usando TOTP:

```php
// Requiere: composer require pragmarx/google2fa
use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

// Generar secret para usuario
$secret = $google2fa->generateSecretKey();

// Verificar codigo
$valid = $google2fa->verifyKey($secret, $userCode);
```

### 4. Politica de Contrasenas

Implementa validacion de contrasenas fuertes:

```php
function validate_password_strength(string $password): array
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Minimo 8 caracteres';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Requiere al menos una mayuscula';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Requiere al menos una minuscula';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Requiere al menos un numero';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Requiere al menos un caracter especial';
    }

    return $errors;
}
```

---

## Checklist de Seguridad

Antes de ir a produccion, verifica:

- [ ] HTTPS configurado y forzado
- [ ] Credenciales en variables de entorno
- [ ] Permisos de archivos correctos
- [ ] Headers de seguridad configurados
- [ ] Archivos sensibles protegidos
- [ ] Contrasena de admin cambiada
- [ ] Rate limiting implementado
- [ ] Logging de seguridad activo
- [ ] Backups automaticos configurados
- [ ] Actualizaciones de PHP al dia

---

## Reporte de Vulnerabilidades

Si encuentras una vulnerabilidad de seguridad:

1. **NO** la publiques en issues publicos
2. Contacta al maintainer directamente
3. Proporciona detalles para reproducir el problema
4. Espera confirmacion antes de divulgar

---

## Referencias

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [PDO Prepared Statements](https://www.php.net/manual/en/pdo.prepared-statements.php)
- [Password Hashing FAQ](https://www.php.net/manual/en/faq.passwords.php)
