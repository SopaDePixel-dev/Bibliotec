<?php
include 'conexion.php';

$mensaje = "";
$editando = false;
$est_edit = [
    'numeroControl' => '',
    'nombre' => '',
    'apellidoPaterno' => '',
    'apellidoMaterno' => '',
    'carrera' => '',
    'estatus' => 'ACTIVO',
    'contrasena' => ''
];

// --- 1. ELIMINAR ESTUDIANTE ---
if (isset($_GET['eliminar'])) {
    $nc = $_GET['eliminar'];
    try {
        $stmt = $pdo->prepare("DELETE FROM estudiante WHERE numeroControl = :nc");
        $stmt->execute([':nc' => $nc]);
        header("Location: estudiantes.php");
        exit;
    } catch (PDOException $e) {
        $mensaje = "Error: No se puede eliminar el estudiante porque tiene préstamos o registros asociados.";
    }
}

// --- 2. CARGAR DATOS PARA EDITAR ---
if (isset($_GET['editar'])) {
    $nc = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM estudiante WHERE numeroControl = :nc");
    $stmt->execute([':nc' => $nc]);
    $resultado = $stmt->fetch();
    if ($resultado) {
        $editando = true;
        $est_edit = $resultado;
    }
}

// --- 3. GUARDAR (CREAR O ACTUALIZAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroControl   = trim($_POST['numeroControl']);
    $nombre          = trim($_POST['nombre']);
    $apellidoPaterno = trim($_POST['apellidoPaterno']);
    $apellidoMaterno = trim($_POST['apellidoMaterno']);
    $carrera         = trim($_POST['carrera']);
    $estatus         = $_POST['estatus'];
    $es_actualizacion = $_POST['es_actualizacion'] === '1';
    $contrasena      = trim($_POST['contrasena']);

    if (!empty($numeroControl) && !empty($nombre) && !empty($carrera)) {
        try {
            if ($es_actualizacion) {
                // Actualizar (Update)
                if (!empty($contrasena)) {
                    $passHash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $sql = "UPDATE estudiante SET nombre = :nom, apellidoPaterno = :app, apellidoMaterno = :apm, carrera = :car, estatus = :est, contrasena = :pass WHERE numeroControl = :nc";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':nom' => $nombre, ':app' => $apellidoPaterno, ':apm' => $apellidoMaterno, ':car' => $carrera, ':est' => $estatus, ':pass' => $passHash, ':nc' => $numeroControl]);
                } else {
                    $sql = "UPDATE estudiante SET nombre = :nom, apellidoPaterno = :app, apellidoMaterno = :apm, carrera = :car, estatus = :est WHERE numeroControl = :nc";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':nom' => $nombre, ':app' => $apellidoPaterno, ':apm' => $apellidoMaterno, ':car' => $carrera, ':est' => $estatus, ':nc' => $numeroControl]);
                }
                $mensaje = "¡Estudiante actualizado con éxito!";
            } else {
                // Insertar (Create)
                $passHash = !empty($contrasena) ? password_hash($contrasena, PASSWORD_DEFAULT) : password_hash('123456', PASSWORD_DEFAULT);
                $sql = "INSERT INTO estudiante (numeroControl, nombre, apellidoPaterno, apellidoMaterno, carrera, estatus, contrasena) 
                        VALUES (:nc, :nom, :app, :apm, :car, :est, :pass)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nc'   => $numeroControl,
                    ':nom'  => $nombre,
                    ':app'  => $apellidoPaterno,
                    ':apm'  => $apellidoMaterno,
                    ':car'  => $carrera,
                    ':est'  => $estatus,
                    ':pass' => $passHash
                ]);
                $mensaje = "¡Estudiante registrado con éxito!";
            }
            // Limpiar formulario tras guardar
            $editando = false;
            $est_edit = ['numeroControl' => '', 'nombre' => '', 'apellidoPaterno' => '', 'apellidoMaterno' => '', 'carrera' => '', 'estatus' => 'ACTIVO', 'contrasena' => ''];
        } catch (PDOException $e) {
            $mensaje = "Error: El número de control ya está registrado.";
        }
    } else {
        $mensaje = "Por favor, completa los campos obligatorios.";
    }
}

$estudiantes = $pdo->query("SELECT * FROM estudiante")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Módulo de Estudiantes</title>
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
            <a href="estudiantes.php" class="active">🎓 Estudiantes</a>
            <a href="libros.php">📖 Libros</a>
            <a href="categorias.php">🏷️ Categorías</a>
            <a href="usuarios.php">👤 Usuarios (Admin)</a>
            <a href="reportes.php">📊 Reportes</a>
        </nav>
    </aside>

    <main>
        <h1>Gestión de Estudiantes (CRUD Completo)</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><?php echo $editando ? 'Editar Estudiante' : 'Registrar Nuevo Estudiante'; ?></h3>
            <form action="estudiantes.php" method="POST">
                <input type="hidden" name="es_actualizacion" value="<?php echo $editando ? '1' : '0'; ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label>No. de Control (9 caracteres):</label>
                        <input type="text" name="numeroControl" maxlength="9" required placeholder="Ej. 210200123" value="<?php echo htmlspecialchars($est_edit['numeroControl']); ?>" <?php echo $editando ? 'readonly style="background-color: #e2e8f0;"' : ''; ?>>
                    </div>
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" required placeholder="Nombre" value="<?php echo htmlspecialchars($est_edit['nombre']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Apellido Paterno:</label>
                        <input type="text" name="apellidoPaterno" required placeholder="Apellido Paterno" value="<?php echo htmlspecialchars($est_edit['apellidoPaterno']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Apellido Materno:</label>
                        <input type="text" name="apellidoMaterno" placeholder="Apellido Materno" value="<?php echo htmlspecialchars($est_edit['apellidoMaterno']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Carrera:</label>
                        <input type="text" name="carrera" required placeholder="Ej. Ing. en Sistemas" value="<?php echo htmlspecialchars($est_edit['carrera']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Estatus:</label>
                        <select name="estatus" required>
                            <option value="ACTIVO" <?php echo ($est_edit['estatus'] === 'ACTIVO') ? 'selected' : ''; ?>>ACTIVO</option>
                            <option value="BAJA" <?php echo ($est_edit['estatus'] === 'BAJA') ? 'selected' : ''; ?>>BAJA</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contraseña / NIP:</label>
                        <input type="password" name="contrasena" placeholder="<?php echo $editando ? 'Dejar en blanco para no cambiar' : '********'; ?>">
                    </div>
                </div>
                <button type="submit" class="btn"><?php echo $editando ? 'Actualizar Estudiante' : 'Guardar Estudiante'; ?></button>
                <?php if ($editando): ?>
                    <a href="estudiantes.php" class="btn-cancel">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                <h3>Lista de Estudiantes Registrados</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>No. Control</th>
                        <th>Nombre Completo</th>
                        <th>Carrera</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($estudiantes)): ?>
                        <?php foreach ($estudiantes as $est): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($est['numeroControl']); ?></strong></td>
                                <td><?php echo htmlspecialchars($est['nombre'] . ' ' . $est['apellidoPaterno'] . ' ' . $est['apellidoMaterno']); ?></td>
                                <td><?php echo htmlspecialchars($est['carrera']); ?></td>
                                <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?php echo htmlspecialchars($est['estatus']); ?></span></td>
                                <td>
                                    <a href="estudiantes.php?editar=<?php echo urlencode($est['numeroControl']); ?>" class="action-link edit-link">✏️ Editar</a>
                                    <a href="estudiantes.php?eliminar=<?php echo urlencode($est['numeroControl']); ?>" class="action-link delete-link" onclick="return confirm('¿Estás seguro de eliminar a este estudiante?');">🗑️ Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No hay estudiantes registrados todavía.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>