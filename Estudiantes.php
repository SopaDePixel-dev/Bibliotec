<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: dashboard_admin.php");
    exit;
}

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
    'contrasena' => '',
    'correo' => ''
];


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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $numeroControl   = trim($_POST['numeroControl']);
    $nombre          = trim($_POST['nombre']);
    $apellidoPaterno = trim($_POST['apellidoPaterno']);
    $apellidoMaterno = trim($_POST['apellidoMaterno']);
    $carrera         = trim($_POST['carrera']);
    $correo          = trim($_POST['correo'] ?? '');
    $estatus         = $_POST['estatus'];
    $es_actualizacion = $_POST['es_actualizacion'] === '1';
    $contrasena      = trim($_POST['contrasena']);
   

    if (!empty($numeroControl) && !empty($nombre) && !empty($carrera)) {
        try {
            if ($es_actualizacion) {
                if (!empty($contrasena)) {
                    $passHash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $sql = "UPDATE estudiante SET nombre = :nom, apellidoPaterno = :app, apellidoMaterno = :apm, carrera = :car, estatus = :est, contrasena = :pass, correo = :correo WHERE numeroControl = :nc";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':nom' => $nombre, ':app' => $apellidoPaterno, ':apm' => $apellidoMaterno, ':car' => $carrera, ':est' => $estatus, ':pass' => $passHash, ':correo' => $correo, ':nc' => $numeroControl]);
                } else {
                    $sql = "UPDATE estudiante SET nombre = :nom, apellidoPaterno = :app, apellidoMaterno = :apm, carrera = :car, estatus = :est, correo = :correo WHERE numeroControl = :nc";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':nom' => $nombre, ':app' => $apellidoPaterno, ':apm' => $apellidoMaterno, ':car' => $carrera, ':est' => $estatus, ':correo' => $correo, ':nc' => $numeroControl]);
                }
                $mensaje = "¡Estudiante actualizado con éxito!";
            } else {
                $passHash = !empty($contrasena) ? password_hash($contrasena, PASSWORD_DEFAULT) : password_hash('123456', PASSWORD_DEFAULT);
                $sql = "INSERT INTO estudiante (numeroControl, nombre, apellidoPaterno, apellidoMaterno, carrera, estatus, contrasena, correo) 
                        VALUES (:nc, :nom, :app, :apm, :car, :est, :pass, :correo)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nc'   => $numeroControl,
                    ':nom'  => $nombre,
                    ':app'  => $apellidoPaterno,
                    ':apm'  => $apellidoMaterno,
                    ':car'  => $carrera,
                    ':est'  => $estatus,
                    ':pass' => $passHash,
                    ':correo' => $correo
                ]);
                $mensaje = "¡Estudiante registrado con éxito!";
            }
            $editando = false;
            $est_edit = ['numeroControl' => '', 'nombre' => '', 'apellidoPaterno' => '', 'apellidoMaterno' => '', 'carrera' => '', 'estatus' => 'ACTIVO', 'contrasena' => '', 'correo' => ''];
        } catch (PDOException $e) {
            $mensaje = "Error: El número de control ya está registrado.";
        }
    } else {
        $mensaje = "Por favor, completa los campos obligatorios.";
    }
}


$busqueda = trim($_GET['busqueda'] ?? '');
if (!empty($busqueda)) {
    $stmt = $pdo->prepare("SELECT * FROM estudiante WHERE numeroControl LIKE :b1 OR nombre LIKE :b2 OR apellidoPaterno LIKE :b3 OR carrera LIKE :b4 ORDER BY numeroControl DESC");
    $stmt->execute([
        ':b1' => "%$busqueda%",
        ':b2' => "%$busqueda%",
        ':b3' => "%$busqueda%",
        ':b4' => "%$busqueda%"
    ]);
    $estudiantes = $stmt->fetchAll();
} else {
    $estudiantes = $pdo->query("SELECT * FROM estudiante ORDER BY numeroControl DESC")->fetchAll();
}
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

        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-bar input { flex: 1; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; }
        .btn-search { background-color: #0284c7; color: white; padding: 10px 18px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-search:hover { background-color: #0369a1; }
        .btn-clear { background-color: #64748b; color: white; padding: 10px 18px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-clear:hover { background-color: #475569; }

        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; background-color: #d1fae5; color: #065f46; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; background: var(--card-bg); border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); }
        th, td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { background-color: #f8fafc; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); }
        tr:hover td { background-color: #f8fafc; }

        .action-link { text-decoration: none; font-weight: 600; font-size: 0.85rem; margin-right: 10px; }
        .edit-link { color: #0284c7; }
        .delete-link { color: #dc2626; }
    </style>
</head>
<body>

    <aside>
        <h2>BiblioTec</h2>
        <p>Sistema de Biblioteca</p>
        <nav>
            <a href="dashboard_admin.php">🏠 Principal</a>
            <a href="prestamos.php">📅 Préstamos</a>
            <a href="devoluciones.php">🔄 Devoluciones</a>
            
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMIN'): ?>
                <a href="estudiantes.php" class="active">🎓 Estudiantes</a>
            <?php endif; ?>

            <a href="libros.php">📖 Libros</a>
            <a href="categorias.php">🏷️ Categorías</a>

            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMIN'): ?>
                <a href="usuarios.php">👤 Usuarios (Admin)</a>
            <?php endif; ?>

            <a href="reportes.php">📊 Reportes</a>
        </nav>
    </aside>

    <main>
        <h1>Gestión de Estudiantes</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><?php echo $editando ? 'Editar Estudiante' : 'Registrar Nuevo Estudiante'; ?></h3>
            <form action="estudiantes.php" method="POST">
                <input type="hidden" name="accion" value="guardar">
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
                        <select name="carrera" required>
                            <option value="">Selecciona una carrera</option>
                            <option value="Ingeniería en Sistemas Computacionales" <?php echo ($est_edit['carrera'] === 'Ingeniería en Sistemas Computacionales') ? 'selected' : ''; ?>>Ingeniería en Sistemas Computacionales</option>
                            <option value="Ingeniería Mecatrónica" <?php echo ($est_edit['carrera'] === 'Ingeniería Mecatrónica') ? 'selected' : ''; ?>>Ingeniería Mecatrónica</option>
                            <option value="Ingeniería Industrial" <?php echo ($est_edit['carrera'] === 'Ingeniería Industrial') ? 'selected' : ''; ?>>Ingeniería Industrial</option>
                            <option value="Ingeniería Civil" <?php echo ($est_edit['carrera'] === 'Ingeniería Civil') ? 'selected' : ''; ?>>Ingeniería Civil</option>
                            <option value="Ingeniería Electrónica" <?php echo ($est_edit['carrera'] === 'Ingeniería Electrónica') ? 'selected' : ''; ?>>Ingeniería Electrónica</option>
                            <option value="Ingeniería Bioquímica" <?php echo ($est_edit['carrera'] === 'Ingeniería Bioquímica') ? 'selected' : ''; ?>>Ingeniería Bioquímica</option>
                            <option value="Gastronomia" <?php echo ($est_edit['carrera'] === 'Gastronomia') ? 'selected' : ''; ?>>Gastronomia</option>
                            <option value="Ingeniería en Industrias Alimentarias" <?php echo ($est_edit['carrera'] === 'Ingeniería en Industrias Alimentarias') ? 'selected' : ''; ?>>Ingeniería en Industrias Alimentarias</option>
                            <option value="Ingeniería en Gestión Empresarial" <?php echo ($est_edit['carrera'] === 'Ingeniería en Gestión Empresarial') ? 'selected' : ''; ?>>Ingeniería en Gestión Empresarial</option>
                        </select>
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
                    <div class="form-group">
                        <label>Correo Electrónico:</label>
                        <input type="email" name="correo" required placeholder="correo@ejemplo.com" value="<?php echo htmlspecialchars($est_edit['correo'] ?? ''); ?>">
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

            <div style="padding: 20px 20px 0 20px;">
                <form action="estudiantes.php" method="GET" class="search-bar">
                    <input type="text" name="busqueda" placeholder="Buscar por no. control, nombre o carrera..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn-search">🔍 Buscar</button>
                    <?php if (!empty($busqueda)): ?>
                        <a href="estudiantes.php" class="btn-clear">❌ Limpiar</a>
                    <?php endif; ?>
                </form>
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
                                No se encontraron estudiantes con ese criterio.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>