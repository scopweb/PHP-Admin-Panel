# Guia de Uso

Esta guia explica como usar el PHP Admin Panel y como extenderlo para tus necesidades.

---

## Estructura del Proyecto

```
PHP-Admin-Panel/
├── Admin_config/
│   ├── connection.php      # Conexion a base de datos (PDO)
│   └── security.php        # Funciones de seguridad
├── Admin_controllers/
│   └── login.php           # Logica de autenticacion
├── Admin_modules/
│   └── login_validate.php  # Procesamiento de formularios
├── views/
│   ├── login.php           # Pagina de login
│   ├── index.php           # Dashboard principal
│   ├── header.php          # Cabecera HTML
│   ├── navbar.php          # Barra de navegacion
│   ├── sidebar.php         # Menu lateral
│   ├── footer.php          # Pie de pagina
│   ├── scripts.php         # Scripts JavaScript
│   ├── logout.php          # Cerrar sesion
│   ├── changepass.php      # Cambiar contrasena
│   ├── forgot-password.php # Recuperar contrasena
│   ├── resetpassword.php   # Resetear contrasena
│   └── ...                 # Otras vistas
├── docs/                   # Documentacion
└── your_db_name.sql        # Esquema de base de datos
```

---

## Funcionalidades Principales

### 1. Autenticacion

#### Login
- URL: `/views/login.php`
- Credenciales por defecto: `admin@google.com` / `Pt123456789`
- Proteccion contra fuerza bruta (implementar rate limiting recomendado)

#### Logout
- Click en icono de usuario > "Sign Out"
- El logout usa POST con CSRF para mayor seguridad

#### Cambiar Contrasena
- URL: `/views/changepass.php`
- Requiere contrasena actual
- Minimo 8 caracteres

#### Recuperar Contrasena
1. Click en "Forgot Password?" en login
2. Ingresar email/username
3. Se genera un token de reset (valido 1 hora)
4. Ingresar nueva contrasena

---

## Crear Nuevas Paginas

### Pagina Protegida Basica

```php
<?php
declare(strict_types=1);

// Incluir header (carga security.php automaticamente)
include 'header.php';

// Requerir autenticacion
require_auth();
?>

<body id="page-top">
  <?php include 'navbar.php'; ?>

  <div id="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="content-wrapper">
      <div class="container-fluid">

        <!-- Tu contenido aqui -->
        <h1>Mi Nueva Pagina</h1>
        <p>Bienvenido, <?= escape($_SESSION['adm_name']) ?></p>

      </div>

      <?php include 'footer.php'; ?>
    </div>
  </div>

  <?php include 'scripts.php'; ?>
</body>
</html>
```

### Agregar al Menu (Sidebar)

Edita `views/sidebar.php`:

```php
<li class="nav-item">
  <a class="nav-link" href="mi-pagina.php">
    <i class="fas fa-fw fa-file"></i>
    <span>Mi Nueva Pagina</span>
  </a>
</li>
```

---

## Trabajar con Base de Datos

### Conexion

La conexion PDO esta disponible como `$conn` despues de incluir `connection.php`:

```php
<?php
require_once __DIR__ . '/../Admin_config/connection.php';

// $conn es un objeto PDO listo para usar
```

### Consultas Seguras (Prepared Statements)

#### SELECT

```php
// Obtener un registro
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = :id");
$stmt->execute(['id' => $id]);
$producto = $stmt->fetch();

// Obtener multiples registros
$stmt = $conn->prepare("SELECT * FROM productos WHERE categoria = :cat");
$stmt->execute(['cat' => $categoria]);
$productos = $stmt->fetchAll();
```

#### INSERT

```php
$stmt = $conn->prepare("
    INSERT INTO productos (nombre, precio, categoria)
    VALUES (:nombre, :precio, :categoria)
");

$stmt->execute([
    'nombre' => $nombre,
    'precio' => $precio,
    'categoria' => $categoria,
]);

$nuevoId = $conn->lastInsertId();
```

#### UPDATE

```php
$stmt = $conn->prepare("
    UPDATE productos
    SET nombre = :nombre, precio = :precio
    WHERE id = :id
");

$stmt->execute([
    'nombre' => $nombre,
    'precio' => $precio,
    'id' => $id,
]);

$filasAfectadas = $stmt->rowCount();
```

#### DELETE

```php
$stmt = $conn->prepare("DELETE FROM productos WHERE id = :id");
$stmt->execute(['id' => $id]);
```

---

## Formularios Seguros

### Crear Formulario con CSRF

```php
<form action="procesar.php" method="post">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>

    <button type="submit" name="guardar" class="btn btn-primary">Guardar</button>
</form>
```

### Procesar Formulario

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../Admin_config/connection.php';
require_once __DIR__ . '/../Admin_config/security.php';

// Verificar autenticacion
if (!is_authenticated()) {
    redirect('../views/login.php');
}

// Procesar formulario
if (isset($_POST['guardar'])) {
    // Validar CSRF
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        alert_redirect('Solicitud invalida', '../views/mi-pagina.php');
    }

    // Obtener y validar datos
    $nombre = trim($_POST['nombre'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

    if (empty($nombre) || !$email) {
        alert_redirect('Datos invalidos', '../views/mi-pagina.php');
    }

    // Guardar en base de datos
    try {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email) VALUES (:nombre, :email)");
        $stmt->execute(['nombre' => $nombre, 'email' => $email]);

        alert_redirect('Usuario creado exitosamente', '../views/mi-pagina.php');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        alert_redirect('Error al guardar', '../views/mi-pagina.php');
    }
}
```

---

## Funciones de Seguridad Disponibles

### security.php - Referencia Rapida

| Funcion | Descripcion | Ejemplo |
|---------|-------------|---------|
| `init_secure_session()` | Inicia sesion segura | `init_secure_session();` |
| `regenerate_session()` | Regenera ID de sesion | `regenerate_session();` |
| `generate_csrf_token()` | Genera token CSRF | `$token = generate_csrf_token();` |
| `validate_csrf_token($token)` | Valida token CSRF | `if (validate_csrf_token($_POST['csrf_token']))` |
| `csrf_field()` | HTML del campo CSRF | `<?= csrf_field() ?>` |
| `escape($value)` | Sanitiza para HTML | `<?= escape($nombre) ?>` |
| `hash_password($pass)` | Hash de contrasena | `$hash = hash_password($pass);` |
| `verify_password($pass, $hash)` | Verifica contrasena | `if (verify_password($pass, $hash))` |
| `is_authenticated()` | Verifica si hay sesion | `if (is_authenticated())` |
| `require_auth()` | Requiere login o redirige | `require_auth();` |
| `redirect($url)` | Redireccion HTTP | `redirect('index.php');` |
| `alert_redirect($msg, $url)` | Alert JS + redireccion | `alert_redirect('Exito', 'index.php');` |
| `get_current_user()` | Obtiene datos del usuario | `$user = get_current_user();` |

---

## Personalizar el Panel

### Cambiar Logo

1. Reemplaza `views/img/logo.jpg` con tu logo
2. O edita `views/navbar.php`:

```php
<a class="navbar-brand" href="index.php">
    <img src="img/tu-logo.png" alt="Mi Empresa" style="height: 40px;">
</a>
```

### Cambiar Colores

Edita `views/css/main.css` o crea tu propio archivo CSS:

```css
/* Colores principales */
.bg-primary { background-color: #tu-color !important; }
.btn-primary { background-color: #tu-color; border-color: #tu-color; }

/* Sidebar */
#sidebar-wrapper { background-color: #2c3e50; }
```

### Cambiar Titulo

Edita `views/header.php`:

```php
<title>Mi Panel de Admin</title>
```

---

## Ejemplos Comunes

### Tabla de Datos con DataTables

```php
<?php
declare(strict_types=1);
include 'header.php';
require_auth();
require_once __DIR__ . '/../Admin_config/connection.php';

$stmt = $conn->query("SELECT * FROM productos");
$productos = $stmt->fetchAll();
?>

<body id="page-top">
  <?php include 'navbar.php'; ?>
  <div id="wrapper">
    <?php include 'sidebar.php'; ?>
    <div id="content-wrapper">
      <div class="container-fluid">

        <h1>Productos</h1>

        <table class="table table-bordered" id="dataTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Precio</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productos as $p): ?>
            <tr>
              <td><?= escape($p['id']) ?></td>
              <td><?= escape($p['nombre']) ?></td>
              <td>$<?= escape($p['precio']) ?></td>
              <td>
                <a href="editar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                <a href="eliminar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      </div>
      <?php include 'footer.php'; ?>
    </div>
  </div>
  <?php include 'scripts.php'; ?>
</body>
```

---

## Siguiente Paso

Consulta la [Guia de Seguridad](SECURITY.md) para mejores practicas de produccion.
