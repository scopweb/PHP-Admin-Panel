# Documentacion - PHP Admin Panel

Bienvenido a la documentacion del PHP Admin Panel.

---

## Indice

### Primeros Pasos

1. **[Guia de Instalacion](INSTALL.md)**
   - Requisitos del sistema
   - Instalacion rapida
   - Configuracion con Docker
   - Configuracion de Apache/Nginx
   - Solucion de problemas

2. **[Guia de Uso](USAGE.md)**
   - Estructura del proyecto
   - Funcionalidades principales
   - Crear nuevas paginas
   - Trabajar con base de datos
   - Formularios seguros
   - Personalizar el panel

3. **[Guia de Seguridad](SECURITY.md)**
   - Seguridad implementada
   - Configuracion para produccion
   - Mejoras recomendadas
   - Checklist de seguridad

---

## Documentacion Adicional

- **[CHANGELOG](../CHANGELOG.md)** - Historial de cambios y versiones
- **[SECURITY_REVIEW](../SECURITY_REVIEW.md)** - Auditoria de seguridad detallada

---

## Enlaces Rapidos

### Archivos Clave

| Archivo | Descripcion |
|---------|-------------|
| `Admin_config/connection.php` | Conexion a base de datos |
| `Admin_config/security.php` | Funciones de seguridad |
| `Admin_controllers/login.php` | Logica de autenticacion |
| `Admin_modules/login_validate.php` | Procesamiento de formularios |
| `your_db_name.sql` | Esquema de base de datos |

### Vistas Principales

| Vista | URL | Descripcion |
|-------|-----|-------------|
| Login | `/views/login.php` | Inicio de sesion |
| Dashboard | `/views/index.php` | Panel principal |
| Cambiar Password | `/views/changepass.php` | Cambio de contrasena |
| Recuperar Password | `/views/forgot-password.php` | Recuperacion |

---

## Requisitos

- PHP 8.0+
- PDO extension
- MySQL 5.7+ / MariaDB 10.2+
- Apache 2.4+ o Nginx 1.18+

---

## Soporte

Si tienes problemas o preguntas:

1. Revisa la documentacion
2. Busca en issues existentes
3. Abre un nuevo issue con detalles del problema
