# Security Review - PHP Admin Panel

**Fecha de revision:** 2025-11-22
**Revisor:** Claude Code
**Version analizada:** Commit 864fd63

---

## Resumen Ejecutivo

Este proyecto contiene **vulnerabilidades de seguridad criticas** que deben ser corregidas antes de usarse en produccion. A continuacion se detallan los problemas encontrados ordenados por severidad.

---

## Vulnerabilidades Criticas (Severidad Alta)

### 1. SQL Injection

**Archivos afectados:**
- `Admin_controllers/login.php:7`
- `Admin_controllers/login.php:21`
- `Admin_controllers/login.php:36`

**Descripcion:** Las consultas SQL concatenan directamente las variables de usuario sin sanitizacion ni prepared statements.

**Codigo vulnerable:**
```php
// login.php:7
$sql= "SELECT * FROM `tb_admin` WHERE `adm_username`='$username' AND `adm_password`='$password'";

// login.php:21
$sql= "SELECT `adm_username` FROM `tb_admin` WHERE `adm_username`='$username'";

// login.php:36
$sql= "UPDATE `tb_admin` SET `adm_password`='$newpass' WHERE `adm_username` = '$admin'";
```

**Impacto:** Un atacante puede:
- Bypass de autenticacion (login sin credenciales validas)
- Extraccion de toda la base de datos
- Modificacion/eliminacion de datos
- En algunos casos, ejecucion remota de codigo

**Solucion recomendada:** Usar prepared statements con mysqli o PDO:
```php
$stmt = $conn->prepare("SELECT * FROM tb_admin WHERE adm_username=? AND adm_password=?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
```

---

### 2. Cross-Site Scripting (XSS) Reflejado

**Archivos afectados:**
- `views/resetpassword.php:31`
- `views/navbar.php:41`
- `views/index.php:31`

**Descripcion:** Se imprimen variables directamente en el HTML sin sanitizacion.

**Codigo vulnerable:**
```php
// resetpassword.php:31
<input type="hidden" name="user" value="<?php echo $_GET['user'];?>">

// navbar.php:41
<li>Welcome, <?php echo($_SESSION['adm_name']);?></li>

// index.php:31
<p>Current User: <b><?php echo ($_SESSION['adm_name'])?></b></p>
```

**Impacto:** Un atacante puede:
- Robar cookies de sesion
- Ejecutar acciones en nombre del usuario
- Redirigir a sitios maliciosos

**Solucion recomendada:**
```php
<?php echo htmlspecialchars($_GET['user'], ENT_QUOTES, 'UTF-8'); ?>
```

---

### 3. Flujo de Reset de Contrasena Inseguro

**Archivos afectados:**
- `Admin_modules/login_validate.php:25-40`
- `views/forgot-password.php`
- `views/resetpassword.php`

**Descripcion:** El flujo de recuperacion de contrasena no tiene verificacion segura:
1. El usuario ingresa su email/username
2. Si existe, redirige directamente a la pagina de reset con el username en la URL
3. Cualquiera puede cambiar la contrasena de cualquier usuario conociendo solo su username

**Impacto:** Takeover completo de cualquier cuenta de administrador.

**Solucion recomendada:**
- Generar token aleatorio seguro y almacenarlo en BD
- Enviar link de reset por email con el token
- Validar token antes de permitir cambio de contrasena
- Tokens deben expirar (ej: 1 hora)

---

## Vulnerabilidades Medias

### 4. Uso de MD5 para Contrasenas

**Archivos afectados:**
- `Admin_modules/login_validate.php:8`
- `Admin_modules/login_validate.php:45-46`

**Descripcion:** Se usa MD5 para hashear contrasenas, que es criptograficamente inseguro.

**Codigo vulnerable:**
```php
$password = md5($_POST['pass']);
```

**Impacto:** Las contrasenas pueden ser crackeadas rapidamente con rainbow tables o fuerza bruta.

**Solucion recomendada:**
```php
// Para hashear
$password = password_hash($_POST['pass'], PASSWORD_DEFAULT);

// Para verificar
if (password_verify($_POST['pass'], $stored_hash)) { ... }
```

---

### 5. Falta de Proteccion CSRF

**Archivos afectados:** Todos los formularios

**Descripcion:** Los formularios no incluyen tokens CSRF para validar que las peticiones provienen del sitio legitimo.

**Impacto:** Un atacante puede hacer que usuarios autenticados ejecuten acciones no deseadas.

**Solucion recomendada:**
```php
// Generar token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// En formularios
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Validar en servidor
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF validation failed');
}
```

---

### 6. Logout via GET Request

**Archivos afectados:**
- `views/logout.php:5`
- `views/navbar.php:63`

**Descripcion:** El logout se realiza via GET request, lo que permite CSRF para cerrar sesiones.

**Codigo vulnerable:**
```php
if (isset($_GET['logout'])) {
    session_destroy();
}
```

**Solucion recomendada:** Usar POST con token CSRF.

---

## Vulnerabilidades Bajas

### 7. Credenciales por Defecto Inseguras

**Archivo afectado:** `Admin_config/connection.php:4-6`

**Descripcion:** Usuario root sin contrasena para base de datos.

```php
$user ="root";
$pass="";
```

**Recomendacion:** Usar variables de entorno para credenciales.

---

### 8. Supresion de Errores

**Archivos afectados:**
- `Admin_controllers/login.php:3`
- `views/index.php:5`

**Descripcion:** `error_reporting(0)` oculta errores pero no los previene.

**Recomendacion:** En desarrollo mostrar errores, en produccion loguearlos en archivo.

---

### 9. Falta de Validacion de Session en Logout

**Archivo afectado:** `views/logout.php`

**Descripcion:** No valida que exista una sesion activa antes de destruirla.

---

### 10. Sesion No Regenerada Post-Login

**Archivo afectado:** `Admin_modules/login_validate.php:14`

**Descripcion:** No se regenera el ID de sesion despues del login, vulnerable a session fixation.

**Solucion recomendada:**
```php
session_regenerate_id(true);
```

---

## Checklist de Seguridad

| Vulnerabilidad | Severidad | Estado |
|----------------|-----------|--------|
| SQL Injection | CRITICA | Pendiente |
| XSS Reflejado | CRITICA | Pendiente |
| Reset Password Inseguro | CRITICA | Pendiente |
| MD5 para passwords | MEDIA | Pendiente |
| Sin CSRF tokens | MEDIA | Pendiente |
| Logout via GET | MEDIA | Pendiente |
| Credenciales hardcoded | BAJA | Pendiente |
| Error reporting oculto | BAJA | Pendiente |
| Sin session regeneration | BAJA | Pendiente |

---

## Recomendaciones Generales

1. **No usar este proyecto en produccion** hasta corregir al menos las vulnerabilidades criticas
2. Implementar Content Security Policy (CSP) headers
3. Usar HTTPS en produccion
4. Implementar rate limiting para login
5. Agregar logging de intentos de login fallidos
6. Considerar usar un framework PHP moderno (Laravel, Symfony) que incluye protecciones por defecto

---

## Referencias

- [OWASP SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
- [OWASP XSS Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [PHP Password Hashing](https://www.php.net/manual/en/function.password-hash.php)
- [OWASP CSRF Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
