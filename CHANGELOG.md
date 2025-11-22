# Changelog

Todos los cambios notables de este proyecto seran documentados en este archivo.

El formato esta basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [2.0.0] - 2025-11-22

### Importante
Esta version requiere **PHP 8.0+** y la extension **PDO**. Las contrasenas MD5 existentes se migraran automaticamente a bcrypt en el primer login.

### Agregado
- **Admin_config/security.php**: Nuevo helper centralizado de seguridad
  - `init_secure_session()`: Inicializacion segura de sesiones
  - `regenerate_session()`: Regeneracion de ID de sesion
  - `generate_csrf_token()` / `validate_csrf_token()`: Proteccion CSRF
  - `csrf_field()`: Genera campo hidden para formularios
  - `escape()`: Sanitizacion XSS (wrapper de htmlspecialchars)
  - `hash_password()` / `verify_password()`: Wrappers de password_hash
  - `require_auth()`: Proteccion de paginas autenticadas
  - `redirect()` / `alert_redirect()`: Helpers de redireccion

- **Sistema de reset de contrasena seguro**:
  - Tokens aleatorios de 64 caracteres
  - Expiracion automatica (1 hora)
  - Tokens almacenados en base de datos
  - Eliminacion de token despues de uso

- **Nuevos campos en base de datos**:
  - `adm_email`: Email del administrador
  - `reset_token`: Token de reset de contrasena
  - `reset_expires`: Fecha de expiracion del token
  - `created_at` / `updated_at`: Timestamps automaticos

- **Soporte para variables de entorno**:
  - `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`

### Cambiado
- **Admin_config/connection.php**: Migrado de mysqli a PDO
  - Prepared statements obligatorios
  - Manejo de errores con excepciones
  - Charset utf8mb4 por defecto

- **Admin_controllers/login.php**: Reescrito completamente
  - Todas las consultas usan prepared statements
  - `validate_login()`: Autenticacion segura con password_verify
  - `upgrade_password_hash()`: Migracion automatica de MD5 a bcrypt
  - `check_user_exists()`: Verificacion segura de usuario
  - `create_reset_token()`: Generacion de tokens de reset
  - `validate_reset_token()`: Validacion de tokens
  - `reset_password_with_token()`: Reset seguro de contrasena
  - `change_user_password()`: Cambio de contrasena autenticado

- **Admin_modules/login_validate.php**: Seguridad mejorada
  - Validacion CSRF en todos los formularios
  - Regeneracion de sesion post-login
  - Validacion de longitud minima de contrasena (8 caracteres)
  - Mensajes de error que no revelan informacion sensible

- **views/header.php**: Incluye security helper automaticamente

- **views/login.php**: Agregado token CSRF

- **views/forgot-password.php**: Agregado token CSRF

- **views/resetpassword.php**:
  - Usa tokens seguros en lugar de username en URL
  - Proteccion XSS con `escape()`
  - Token CSRF en formulario

- **views/changepass.php**:
  - Requiere autenticacion con `require_auth()`
  - Token CSRF
  - Validacion de longitud de contrasena

- **views/index.php**:
  - Proteccion XSS en salida de datos de usuario
  - Usa `require_auth()` en lugar de verificacion manual

- **views/navbar.php**:
  - Proteccion XSS en nombre de usuario
  - Logout cambiado de GET a POST con CSRF

- **views/logout.php**: Reescrito completamente
  - Solo acepta POST requests
  - Validacion CSRF
  - Limpieza completa de sesion y cookies

- **your_db_name.sql**: Esquema actualizado
  - Nuevas columnas para tokens de reset
  - Campo password extendido a 255 chars (bcrypt)
  - Indices optimizados
  - Script de migracion incluido

### Seguridad
- **SQL Injection**: Eliminado - Todas las consultas usan prepared statements
- **XSS**: Eliminado - Todas las salidas sanitizadas con `escape()`
- **CSRF**: Protegido - Tokens en todos los formularios POST
- **Password Hashing**: MD5 reemplazado por bcrypt (PASSWORD_DEFAULT)
- **Session Fixation**: Protegido - Regeneracion de sesion post-login
- **Logout CSRF**: Protegido - Logout requiere POST + CSRF token

### Eliminado
- `error_reporting(0)` - Errores ahora se manejan apropiadamente
- Consultas SQL con concatenacion de strings
- Hash MD5 para nuevas contrasenas (legacy soportado para migracion)
- Logout via GET request

---

## [1.0.0] - 2019-09-08

### Agregado
- Version inicial del panel de administracion
- Sistema de login/logout
- Recuperacion de contrasena
- Cambio de contrasena
- Dashboard con graficos
- Navegacion lateral (sidebar)
- DataTables integration
- Bootstrap 4 UI
- FontAwesome icons
- PHPMailer para emails

### Notas
- Esta version contenia vulnerabilidades de seguridad criticas
- No usar en produccion sin actualizar a v2.0.0+

---

## Guia de Migracion

### De 1.0.0 a 2.0.0

1. **Requisitos del servidor**:
   ```
   PHP 8.0 o superior
   Extension PDO habilitada
   MySQL 5.7+ o MariaDB 10.2+
   ```

2. **Actualizar base de datos**:
   ```sql
   ALTER TABLE `tb_admin`
     ADD COLUMN `adm_email` varchar(255) DEFAULT NULL AFTER `adm_username`,
     ADD COLUMN `reset_token` varchar(64) DEFAULT NULL AFTER `adm_type`,
     ADD COLUMN `reset_expires` datetime DEFAULT NULL AFTER `reset_token`,
     MODIFY `adm_password` varchar(255) NOT NULL,
     ADD INDEX `idx_reset_token` (`reset_token`);
   ```

3. **Configurar variables de entorno** (opcional pero recomendado):
   ```bash
   export DB_HOST=localhost
   export DB_USER=tu_usuario
   export DB_PASS=tu_contrasena
   export DB_NAME=tu_base_de_datos
   ```

4. **Contrasenas existentes**:
   - Las contrasenas MD5 se migraran automaticamente a bcrypt
   - Los usuarios solo necesitan hacer login normalmente
   - La migracion es transparente y automatica
