<?php
include 'conexion.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroControl = trim($_POST['numeroControl']);
    $id_libro      = intval($_POST['id_libro']);

    if (!empty($numeroControl) && $id_libro > 0) {
        try {
            $stmtLibro = $pdo->prepare("SELECT stock FROM libros WHERE id_libro = :id");
            $stmtLibro->execute([':id' => $id_libro]);
            $libro = $stmtLibro->fetch();

            if ($libro && $libro['stock'] > 0) {
                $fechaPrestamo = date('Y-m-d H:i:s');
                $fechaDevolucion = date('Y-m-d H:i:s', strtotime('+3 days'));

                $sql = "INSERT INTO prestamo (numeroControl, id_libro, fechaPrestamo, fechaDevolucion, estado) 
                        VALUES (:nc, :id_libro, :f_pres, :f_dev, 'Activo')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nc'       => $numeroControl,
                    ':id_libro' => $id_libro,
                    ':f_pres'   => $fechaPrestamo,
                    ':f_dev'    => $fechaDevolucion
                ]);

                $stmtUpdate = $pdo->prepare("UPDATE libros SET stock = stock - 1 WHERE id_libro = :id");
                $stmtUpdate->execute([':id' => $id_libro]);

                $mensaje = "¡Préstamo registrado con éxito!";
            } else {
                $mensaje = "Error: El libro seleccionado no tiene ejemplares disponibles.";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al procesar el préstamo.";
        }
    } else {
        $mensaje = "Por favor, selecciona un estudiante y un libro.";
    }
}

$estudiantes = $pdo->query("SELECT numeroControl, nombre, apellidoPaterno FROM estudiante WHERE estatus = 'ACTIVO'")->fetchAll();
$libros = $pdo->query("SELECT id_libro, titulo, stock FROM libros WHERE stock > 0")->fetchAll();

$prestamos = $pdo->query("SELECT p.*, e.nombre AS est_nombre, e.apellidoPaterno AS est_app, l.titulo AS libro_titulo 
                          FROM prestamo p 
                          JOIN estudiante e ON p.numeroControl = e.numeroControl 
                          JOIN libros l ON p.id_libro = l.id_libro")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Módulo de Préstamos</title>
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

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; }
        .form-group select { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; }

        .btn { background-color: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background-color: var(--primary-dark); }

        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; background-color: #d1fae5; color: #065f46; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; background: var(--card-bg); border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); }
        th, td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { background-color: #f8fafc; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); }
        tr:hover td { background-color: #f8fafc; }
    </style>
</head>
<body>

    <aside>
        <h2>BiblioTec</h2>
        <p>Sistema de Biblioteca</p>
        <nav>
            <a href="index.php">🏠 Principal</a>
            <a href="prestamos.php" class="active">📅 Préstamos</a>
            <a href="devoluciones.php">🔄 Devoluciones</a>
            <a href="estudiantes.php">🎓 Estudiantes</a>
            <a href="libros.php">📖 Libros</a>
            <a href="categorias.php">🏷️ Categorías</a>
            <a href="usuarios.php">👤 Usuarios (Admin)</a>
            <a href="reportes.php">📊 Reportes</a>
        </nav>
    </aside>

    <main>
        <h1>Gestión de Préstamos</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Registrar Nuevo Préstamo</h3>
            <form action="prestamos.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Estudiante:</label>
                        <select name="numeroControl" required>
                            <option value="">Selecciona un estudiante</option>
                            <?php foreach ($estudiantes as $est): ?>
                                <option value="<?php echo $est['numeroControl']; ?>">
                                    <?php echo htmlspecialchars($est['numeroControl'] . ' - ' . $est['nombre'] . ' ' . $est['apellidoPaterno']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Libro:</label>
                        <select name="id_libro" required>
                            <option value="">Selecciona un libro disponible</option>
                            <?php foreach ($libros as $lib): ?>
                                <option value="<?php echo $lib['id_libro']; ?>">
                                    <?php echo htmlspecialchars($lib['titulo'] . ' (Disponibles: ' . $lib['stock'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">Registrar Préstamo</button>
            </form>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                <h3>Préstamos Activos</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Estudiante</th>
                        <th>Libro</th>
                        <th>Fecha Salida</th>
                        <th>Fecha Límite (3 días)</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($prestamos)): ?>
                        <?php foreach ($prestamos as $pres): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($pres['id_prestamo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($pres['est_nombre'] . ' ' . $pres['est_app']); ?></td>
                                <td><?php echo htmlspecialchars($pres['libro_titulo']); ?></td>
                                <td><?php echo htmlspecialchars($pres['fechaPrestamo']); ?></td>
                                <td><?php echo htmlspecialchars($pres['fechaDevolucion']); ?></td>
                                <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?php echo htmlspecialchars($pres['estado']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No hay préstamos registrados todavía.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>