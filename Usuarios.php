<?php
session_start();

// Validar acceso exclusivo para ADMIN
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: index.php");
    exit;
}

include 'conexion.php';

$mensaje = "";
$editando = false;
$usr_edit = [
    'id_usuario' => '',
    'nombre' => '',
    'nombreUsuario' => '',
    'rol' => 'BIBLIOTECARIO'
];

// --- 1. ELIMINAR USUARIO ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if ($id != $_SESSION['id_usuario']) {
        try {
            $stmt = $pdo->prepare("DELETE FROM usuario WHERE id_usuario = :id");
            $stmt->execute([':id' => $id]);
            header("Location: usuarios.php");
            exit;
        } catch (PDOException $e) {
            $mensaje = "Error al eliminar el usuario.";
        }
    } else {
        $mensaje = "No puedes eliminar tu propia sesión activa.";
    }
}

// --- 2. CARGAR DATOS PARA EDITAR ---
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $pdo->prepare("SELECT id_usuario, nombre, nombreUsuario, rol FROM usuario WHERE id_usuario = :id");
    $stmt->execute([':id' => $id]);
    $resultado = $stmt->fetch();
    if ($resultado) {
        $editando = true;
        $usr_edit = $resultado;
    }
}

// --- 3. GUARDAR (CREAR O ACTUALIZAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario    = intval($_POST['id_usuario'] ?? 0);
    $nombre        = trim($_POST['nombre']);
    $nombreUsuario = trim($_POST['nombreUsuario']);
    $contrasena    = trim($_POST['contrasena']);
    $rol           = $_POST['rol'];
    $es_actualizacion = $_POST['es_actualizacion'] === '1';

    if (!empty($nombre) && !empty($nombreUsuario) && !empty($rol)) {
        try {
            if ($es_actualizacion && $id_usuario > 0) {
                // Actualizar (Update)
                if (!empty($contrasena)) {
                    $passwordHash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $sql = "UPDATE usuario SET nombre = :nombre, nombreUsuario = :nusuario, contrasena = :pass, rol = :rol WHERE id_usuario = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':nombre' => $nombre, ':nusuario' => $nombreUsuario, ':pass' => $passwordHash, ':rol' => $rol, ':id' => $id_usuario]);
                } else {
                    $sql = "UPDATE usuario SET nombre = :nombre, nombreUsuario = :nusuario, rol = :rol WHERE id_usuario = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':nombre' => $nombre, ':nusuario' => $nombreUsuario, ':rol' => $rol, ':id' => $id_usuario]);
                }
                $mensaje = "¡Usuario actualizado con éxito!";
            } else {
                // Insertar (Create)
                if (!empty($contrasena)) {
                    $passwordHash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO usuario (nombre, nombreUsuario, contrasena, rol) VALUES (:nombre, :nusuario, :pass, :rol)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':nombre' => $nombre, ':nusuario' => $nombreUsuario, ':pass' => $passwordHash, ':rol' => $rol]);
                    $mensaje = "¡Usuario registrado con éxito!";
                } else {
                    $mensaje = "La contraseña es obligatoria para nuevos usuarios.";
                }
            }
            $editando = false;
            $usr_edit = ['id_usuario' => '', 'nombre' => '', 'nombreUsuario' => '', 'rol' => 'BIBLIOTECARIO'];
        } catch (PDOException $e) {
            $mensaje = "Error: El nombre de usuario ya está registrado.";
        }
    } else {
        $mensaje = "Por favor, completa los campos obligatorios.";
    }
}

$usuarios = $pdo->query("SELECT id_usuario, nombre, nombreUsuario, rol FROM usuario")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Módulo de Usuarios</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); display: flex; min-height: 100vh; }

        aside { width: 260px; background-color: var(--primary); color: white; display: flex; flex-direction: column; padding: 20px; }
        aside h2 { font-size: 1.4rem; margin-bottom: 5px; }
        aside p { font-size: 0.8rem; opacity: 0.8; margin-bottom: 30px; }
        aside nav a { display: block; padding: 12px 15px; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; margin-bottom: 6px; transition: background 0.2s; }
        aside nav a:hover, aside nav a.active { background-color: rgba(255, 255, 255, 0.15); }

        main { flex: 1; padding: 30px; overflow-y: auto; }
        h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 20px; }

        .card { background: var(--card-bg); padding: 25px; border-radius: 10px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card h3 { margin-bottom: 15px; font-size: 1.1rem; color: var(--primary); }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; }

        .btn { background-color: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background-color: var(--primary-dark); }
        .btn-cancel { background-color: #64748b; margin-left: 10px; text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 6px; color: white; font-weight: 600; font-size: 0.9rem; }
        .btn-cancel:hover { background-color: #475569; }

        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; background-color: #d1fae5; color: #065f46; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; background: var(--card-bg); border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); }
        th, td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { background-color: #f8fafc; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); }
        tr:hover td { background-color: #f8fafc; }

        .action-link { text-decoration: none; font-weight: 600; font-size: 0.85rem; margin-right: 10px; }
        .edit-link { color: #0284c7; }
        .edit-link:hover { text-decoration: underline; }
        .delete-link { color: #dc2626; }
        .delete-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <aside>
        <h2>BiblioTec</h2>
        <p>Sistema de Biblioteca</p>
        <nav>
            <a href="index.php">🏠 Principal</a>
            <a href="prestamos.php">📅 Préstamos</a>
            <a href="devoluciones.php">🔄 Devoluciones</a>
            <a href="estudiantes.php">🎓 Estudiantes</a>
            <a href="libros.php">📖 Libros</a>
            <a href="categorias.php">🏷️ Categorías</a>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMIN'): ?>
                <a href="usuarios.php" class="active">👤 Usuarios (Admin)</a>
            <?php endif; ?>
            <a href="reportes.php">📊 Reportes</a>
        </nav>
    </aside>

    <main>
        <h1>Gestión de Usuarios (CRUD Completo)</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><?php echo $editando ? 'Editar Usuario' : 'Registrar Nuevo Usuario'; ?></h3>
            <form action="usuarios.php" method="POST">
                <input type="hidden" name="es_actualizacion" value="<?php echo $editando ? '1' : '0'; ?>">
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($usr_edit['id_usuario']); ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre Completo:</label>
                        <input type="text" name="nombre" required placeholder="Nombre" value="<?php echo htmlspecialchars($usr_edit['nombre']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Usuario (Login):</label>
                        <input type="text" name="nombreUsuario" required placeholder="Usuario" value="<?php echo htmlspecialchars($usr_edit['nombreUsuario']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Contraseña:</label>
                        <input type="password" name="contrasena" placeholder="<?php echo $editando ? 'Dejar en blanco para no cambiar' : '********'; ?>">
                    </div>
                    <div class="form-group">
                        <label>Rol:</label>
                        <select name="rol" required>
                            <option value="ADMIN" <?php echo ($usr_edit['rol'] === 'ADMIN') ? 'selected' : ''; ?>>ADMIN</option>
                            <option value="BIBLIOTECARIO" <?php echo ($usr_edit['rol'] === 'BIBLIOTECARIO') ? 'selected' : ''; ?>>BIBLIOTECARIO</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn"><?php echo $editando ? 'Actualizar Usuario' : 'Guardar Usuario'; ?></button>
                <?php if ($editando): ?>
                    <a href="usuarios.php" class="btn-cancel">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                <h3>Usuarios Autorizados</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usr): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($usr['id_usuario']); ?></strong></td>
                                <td><?php echo htmlspecialchars($usr['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usr['nombreUsuario']); ?></td>
                                <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?php echo htmlspecialchars($usr['rol']); ?></span></td>
                                <td>
                                    <a href="usuarios.php?editar=<?php echo $usr['id_usuario']; ?>" class="action-link edit-link">✏️ Editar</a>
                                    <?php if ($usr['id_usuario'] != $_SESSION['id_usuario']): ?>
                                        <a href="usuarios.php?eliminar=<?php echo $usr['id_usuario']; ?>" class="action-link delete-link" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">🗑️ Eliminar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No hay usuarios registrados todavía.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>