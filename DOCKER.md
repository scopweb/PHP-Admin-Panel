# Instrucciones de Uso con Docker

## Requisitos Previos
- Docker instalado (incluye Docker Compose v2)

## Inicio Rápido

### Opción 1: Usando el script automático
```bash
./start-docker.sh
```

### Opción 2: Manual

#### 1. Construir y Levantar los Contenedores

```bash
sudo docker compose up -d --build
```

Este comando:
- Construye la imagen de PHP con Apache
- Levanta el contenedor de MySQL
- Levanta phpMyAdmin para administrar la base de datos
- Crea la red y volúmenes necesarios

### 2. Verificar que los Contenedores Estén Corriendo

```bash
sudo docker compose ps
```

Deberías ver 3 contenedores corriendo:
- `php-admin-panel-web` (Puerto 8080)
- `php-admin-panel-db` (Puerto 3306)
- `php-admin-panel-phpmyadmin` (Puerto 8081)

## Acceso a los Servicios

- **Aplicación Web**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

### Credenciales de Acceso

#### Aplicación (Usuario por defecto):
- **Usuario**: admin@google.com
- **Contraseña**: Pt123456789

#### MySQL (Base de datos):
- **Host**: db (dentro de Docker) o localhost:3306 (desde tu máquina)
- **Database**: admin_panel
- **Usuario**: admin
- **Contraseña**: secretpassword
- **Root Password**: rootpassword

#### phpMyAdmin:
- **Usuario**: admin (o root)
- **Contraseña**: secretpassword (o rootpassword para root)

## Comandos Útiles

### Ver los logs de los contenedores
```bash
# Todos los servicios
sudo docker compose logs -f

# Solo el servicio web
sudo docker compose logs -f web

# Solo la base de datos
sudo docker compose logs -f db
```

### Detener los contenedores
```bash
sudo docker compose stop
```

### Iniciar los contenedores detenidos
```bash
sudo docker compose start
```

### Detener y eliminar los contenedores
```bash
sudo docker compose down
```

### Detener y eliminar contenedores + volúmenes (CUIDADO: Borra la BD)
```bash
sudo docker compose down -v
```

### Acceder al contenedor de PHP
```bash
sudo docker exec -it php-admin-panel-web bash
```

### Acceder al contenedor de MySQL
```bash
sudo docker exec -it php-admin-panel-db mysql -u admin -psecretpassword admin_panel
```

### Reiniciar solo un servicio
```bash
sudo docker compose restart web
```

## Resolución de Problemas

### La base de datos no se inicializa
Si la base de datos ya existe en el volumen, el script SQL no se ejecutará. Para forzar la reinicialización:
```bash
sudo docker compose down -v
sudo docker compose up -d --build
```

### Error de conexión a la base de datos
Espera unos segundos después de levantar los contenedores. MySQL tarda en inicializarse completamente. Verifica el estado con:
```bash
sudo docker compose logs db
```

### Permisos de archivos
Si tienes problemas de permisos:
```bash
sudo docker exec -it php-admin-panel-web chown -R www-data:www-data /var/www/html
sudo docker exec -it php-admin-panel-web chmod -R 755 /var/www/html
```

### Ver el estado de salud de MySQL
```bash
sudo docker inspect php-admin-panel-db | grep -A 10 Health
```

## Desarrollo

Los archivos del proyecto están montados como volumen, por lo que cualquier cambio que hagas en tu máquina se reflejará automáticamente en el contenedor (no necesitas reconstruir).

### Reinstalar/Reconstruir la imagen PHP
Si modificas el Dockerfile:
```bash
sudo docker compose up -d --build web
```

## Backup de la Base de Datos

### Crear un backup
```bash
sudo docker exec php-admin-panel-db mysqldump -u admin -psecretpassword admin_panel > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restaurar desde un backup
```bash
sudo docker exec -i php-admin-panel-db mysql -u admin -psecretpassword admin_panel < backup_file.sql
```

## Variables de Entorno

Puedes modificar las credenciales y configuración editando el archivo `docker-compose.yml` o creando un archivo `.env` (basado en `.env.example`).

## Notas de Seguridad

⚠️ **IMPORTANTE**: Las contraseñas en `docker-compose.yml` son para desarrollo. En producción:
- Usa contraseñas seguras
- Utiliza archivos `.env` que no se suban al repositorio
- Considera usar Docker Secrets o variables de entorno del sistema
