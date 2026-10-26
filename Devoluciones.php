<?php
include 'conexion.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_prestamo   = intval($_POST['id_prestamo']);
    $condicion     = $_POST['condicion'];
    $observaciones = trim($_POST['observaciones']);

    if ($id_prestamo > 0) {
        try {
            $stmtPres = $pdo->prepare("SELECT id_libro, estado FROM prestamo WHERE id_prestamo = :id");
            $stmtPres->execute([':id' => $id_prestamo]);
            $prestamo = $stmtPres->fetch();

            if ($prestamo && $prestamo['estado'] === 'Activo') {
                $pdo->beginTransaction();

                $sqlDev = "INSERT INTO devolucion (id_prestamo, fechaDevolucion, condicion, observaciones) 
                           VALUES (:id_pres, NOW(), :cond, :obs)";
                $stmtDev = $pdo->prepare($sqlDev);
                $stmtDev->execute([
                    ':id_pres' => $id_prestamo,
                    ':cond'    => $condicion,
                    ':obs'     => $observaciones
                ]);

                $stmtUpdPres = $pdo->prepare("UPDATE prestamo SET estado = 'Devuelto' WHERE id_prestamo = :id");
                $stmtUpdPres->execute([':id' => $id_prestamo]);

                $stmtUpdLib = $pdo->prepare("UPDATE libros SET stock = stock + 1 WHERE id_libro = :id_lib");
                $stmtUpdLib->execute([':id_lib' => $prestamo['id_libro']]);

                $pdo->commit();
                $mensaje = "¡Devolución registrada con éxito!";
            } else {
                $mensaje = "Error: El préstamo no existe o ya fue devuelto.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje = "Error al procesar la devolución.";
        }
    } else {
        $mensaje = "Por favor, selecciona un préstamo válido.";
    }
}

$prestamosActivos = $pdo->query("SELECT p.id_prestamo, e.nombre, e.apellidoPaterno, l.titulo, p.fechaPrestamo 
                                 FROM prestamo p 
                                 JOIN estudiante e ON p.numeroControl = e.numeroControl 
                                 JOIN libros l ON p.id_libro = l.id_libro 
                                 WHERE p.estado = 'Activo'")->fetchAll();

$devoluciones = $pdo->query("SELECT d.*, p.id_prestamo, e.nombre AS est_nombre, e.apellidoPaterno AS est_app, l.titulo AS libro_titulo 
                             FROM devolucion d 
                             JOIN prestamo p ON d.id_prestamo = p.id_prestamo 
                             JOIN estudiante e ON p.numeroControl = e.numeroControl 
                             JOIN libros l ON p.id_libro = l.id_libro")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Módulo de Devoluciones</title>
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
        .form-group select, .form-group input { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; }

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
            <a href="prestamos.php">📅 Préstamos</a>
            <a href="devoluciones.php" class="active">🔄 Devoluciones</a>
            <a href="estudiantes.php">🎓 Estudiantes</a>
            <a href="libros.php">📖 Libros</a>
            <a href="categorias.php">🏷️ Categorías</a>
            <a href="usuarios.php">👤 Usuarios (Admin)</a>
            <a href="reportes.php">📊 Reportes</a>
        </nav>
    </aside>

    <main>
        <h1>Gestión de Devoluciones</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Registrar Devolución de Libro</h3>
            <form action="devoluciones.php" method="POST">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Seleccionar Préstamo Activo:</label>
                        <select name="id_prestamo" required>
                            <option value="">Selecciona el préstamo a devolver</option>
                            <?php foreach ($prestamosActivos as $pres): ?>
                                <option value="<?php echo $pres['id_prestamo']; ?>">
                                    <?php echo htmlspecialchars('ID #' . $pres['id_prestamo'] . ' - ' . $pres['nombre'] . ' ' . $pres['apellidoPaterno'] . ' ("' . $pres['titulo'] . '")'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Condición del Ejemplar:</label>
                        <select name="condicion">
                            <option value="NUEVO">Nuevo</option>
                            <option value="BUENO" selected>Bueno</option>
                            <option value="DETERIORADO">Deteriorado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observaciones:</label>
                        <input type="text" name="observaciones" placeholder="Ej. Sin daños aparentes">
                    </div>
                </div>
                <button type="submit" class="btn">Registrar Devolución</button>
            </form>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                <h3>Historial de Devoluciones</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID Dev.</th>
                        <th>ID Préstamo</th>
                        <th>Estudiante</th>
                        <th>Libro</th>
                        <th>Fecha Devolución</th>
                        <th>Condición</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($devoluciones)): ?>
                        <?php foreach ($devoluciones as $dev): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($dev['id_devolucion']); ?></strong></td>
                                <td>#<?php echo htmlspecialchars($dev['id_prestamo']); ?></td>
                                <td><?php echo htmlspecialchars($dev['est_nombre'] . ' ' . $dev['est_app']); ?></td>
                                <td><?php echo htmlspecialchars($dev['libro_titulo']); ?></td>
                                <td><?php echo htmlspecialchars($dev['fechaDevolucion']); ?></td>
                                <td><span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?php echo htmlspecialchars($dev['condicion']); ?></span></td>
                                <td><?php echo htmlspecialchars($dev['observaciones']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No hay devoluciones registradas todavía.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>