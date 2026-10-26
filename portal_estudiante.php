<?php
session_start();
if (!isset($_SESSION['numeroControl'])) {
    header("Location: login_estudiante.php");
    exit;
}

include 'conexion.php';
$nc = $_SESSION['numeroControl'];

$stmtEst = $pdo->prepare("SELECT * FROM estudiante WHERE numeroControl = :nc");
$stmtEst->execute([':nc' => $nc]);
$estudiante = $stmtEst->fetch();

$libros = $pdo->query("SELECT l.*, c.nombre AS categoria_nombre FROM libros l LEFT JOIN categoria c ON l.id_categoria = c.id_categoria")->fetchAll();

$stmtPrestamosActivos = $pdo->prepare("SELECT p.*, l.titulo, l.editorial FROM prestamo p JOIN libros l ON p.id_libro = l.id_libro WHERE p.numeroControl = :nc AND p.estado = 'Activo'");
$stmtPrestamosActivos->execute([':nc' => $nc]);
$prestamosActivos = $stmtPrestamosActivos->fetchAll();

$stmtHistorial = $pdo->prepare("SELECT p.*, l.titulo, d.fechaDevolucion AS fechaRealDev, d.condicion, d.observaciones FROM prestamo p JOIN libros l ON p.id_libro = l.id_libro LEFT JOIN devolucion d ON p.id_prestamo = d.id_prestamo WHERE p.numeroControl = :nc");
$stmtHistorial->execute([':nc' => $nc]);
$historial = $stmtHistorial->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Mi Portal Estudiantil</title>
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

        main { flex: 1; display: flex; flex-direction: column; }

        header { background-color: #1d4ed8; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        header .materia { font-size: 0.9rem; font-weight: 600; letter-spacing: 0.5px; }
        header .user-info { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; }
        header .user-badge { background: rgba(255,255,255,0.2); padding: 6px 12px; border-radius: 20px; }
        
        .btn-logout { background-color: #dc2626; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: background 0.2s; }
        .btn-logout:hover { background-color: #b91c1c; }

        .content-body { padding: 30px; overflow-y: auto; }
        h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 20px; }
        .section-title { font-size: 1.3rem; font-weight: 700; margin: 30px 0 15px 0; color: var(--text-main); }

        .card { background: var(--card-bg); padding: 25px; border-radius: 10px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card h3 { margin-bottom: 15px; font-size: 1.1rem; color: var(--primary); }

        .profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .profile-item label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 4px; }
        .profile-item p { font-size: 1rem; font-weight: 600; color: var(--text-main); }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; background: var(--card-bg); border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); }
        th, td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { background-color: #f8fafc; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); }
        tr:hover td { background-color: #f8fafc; }
    </style>
</head>
<body>

    <aside>
        <h2>BiblioTec</h2>
        <p>Portal Estudiantil</p>
        <nav>
            <a href="portal_estudiante.php" class="active">📚 Mi Panel</a>
        </nav>
    </aside>

    <main>
        <header>
            <span class="materia">ESTUDIANTE - SISTEMA BIBLIOTECARIO</span>
            <div class="user-info">
                <span class="user-badge">Alumno: <strong><?php echo htmlspecialchars($estudiante['nombre']); ?></strong></span>
                <a href="logout_estudiante.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <div class="content-body">
            <h1>Mi Información Personal</h1>
            <div class="card">
                <div class="profile-grid">
                    <div class="profile-item">
                        <label>No. de Control</label>
                        <p><?php echo htmlspecialchars($estudiante['numeroControl']); ?></p>
                    </div>
                    <div class="profile-item">
                        <label>Nombre Completo</label>
                        <p><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidoPaterno'] . ' ' . $estudiante['apellidoMaterno']); ?></p>
                    </div>
                    <div class="profile-item">
                        <label>Carrera</label>
                        <p><?php echo htmlspecialchars($estudiante['carrera']); ?></p>
                    </div>
                    <div class="profile-item">
                        <label>Estatus</label>
                        <p><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;"><?php echo htmlspecialchars($estudiante['estatus']); ?></span></p>
                    </div>
                </div>
            </div>

            <h2 class="section-title">Mis Préstamos Activos</h2>
            <div class="card" style="padding: 0; overflow: hidden;">
                <table>
                    <thead>
                        <tr>
                            <th>ID Préstamo</th>
                            <th>Libro</th>
                            <th>Editorial</th>
                            <th>Fecha Salida</th>
                            <th>Fecha Límite</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($prestamosActivos)): ?>
                            <?php foreach ($prestamosActivos as $p): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($p['id_prestamo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($p['editorial']); ?></td>
                                    <td><?php echo htmlspecialchars($p['fechaPrestamo']); ?></td>
                                    <td><?php echo htmlspecialchars($p['fechaDevolucion']); ?></td>
                                    <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?php echo htmlspecialchars($p['estado']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    No tienes préstamos activos en este momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h2 class="section-title">Catálogo de Libros y Stock Disponible</h2>
            <div class="card" style="padding: 0; overflow: hidden;">
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Editorial</th>
                            <th>ISBN</th>
                            <th>Categoría</th>
                            <th>Stock Disponible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($libros)): ?>
                            <?php foreach ($libros as $lib): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($lib['titulo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($lib['editorial']); ?></td>
                                    <td><?php echo htmlspecialchars($lib['isbn']); ?></td>
                                    <td><?php echo htmlspecialchars($lib['categoria_nombre'] ?? 'General'); ?></td>
                                    <td><span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?php echo htmlspecialchars($lib['stock']); ?> disp.</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    No hay libros registrados en el catálogo.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h2 class="section-title">Historial de Préstamos Anteriores</h2>
            <div class="card" style="padding: 0; overflow: hidden;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Libro</th>
                            <th>Fecha Préstamo</th>
                            <th>Fecha Devolución</th>
                            <th>Estado</th>
                            <th>Condición</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($historial)): ?>
                            <?php foreach ($historial as $h): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($h['id_prestamo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($h['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($h['fechaPrestamo']); ?></td>
                                    <td><?php echo htmlspecialchars($h['fechaRealDev'] ?? 'Pendiente'); ?></td>
                                    <td><?php echo htmlspecialchars($h['estado']); ?></td>
                                    <td><?php echo htmlspecialchars($h['condicion'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    No cuentas con historial de préstamos previos.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>