<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Obtener estadísticas y métricas generales
try {
    $totalEstudiantes = $pdo->query("SELECT COUNT(*) FROM estudiante")->fetchColumn();
    $totalLibros = $pdo->query("SELECT COUNT(*) FROM libros")->fetchColumn();
    $prestamosHistoricos = $pdo->query("SELECT COUNT(*) FROM prestamo")->fetchColumn();
    $prestamosActivos = $pdo->query("SELECT COUNT(*) FROM prestamo WHERE estado = 'Activo'")->fetchColumn();
    $totalDevoluciones = $pdo->query("SELECT COUNT(*) FROM devolucion")->fetchColumn();

    // Obtener el reporte general de préstamos con datos de estudiantes y libros
    $stmtReporte = $pdo->query("SELECT p.*, e.numeroControl, e.nombre, e.apellidoPaterno, l.titulo FROM prestamo p JOIN estudiante e ON p.numeroControl = e.numeroControl JOIN libros l ON p.id_libro = l.id_libro ORDER BY p.id_prestamo DESC");
    $reportePrestamos = $stmtReporte->fetchAll();
} catch (PDOException $e) {
    $totalEstudiantes = 0;
    $totalLibros = 0;
    $prestamosHistoricos = 0;
    $prestamosActivos = 0;
    $totalDevoluciones = 0;
    $reportePrestamos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Módulo de Reportes</title>
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

        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .metric-card { background: var(--card-bg); padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .metric-card h3 { font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .metric-card p { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }

        .card { background: var(--card-bg); padding: 25px; border-radius: 10px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h3 { font-size: 1.1rem; color: var(--primary); }

        .btn-print { background-color: #1e40af; color: white; padding: 8px 16px; border: none; border-radius: 6px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: background 0.2s; }
        .btn-print:hover { background-color: #1e3a8a; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; background: var(--card-bg); border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); }
        th, td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { background-color: #f8fafc; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); }
        tr:hover td { background-color: #f8fafc; }

        /* Estilos optimizados para impresión sin cortes */
        @media print {
            aside, .metrics-grid, .btn-print, header { display: none !important; }
            body, html { 
                background: white !important; 
                color: black !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            main { 
                padding: 0 !important; 
                margin: 0 !important;
                width: 100% !important;
                display: block !important;
            }
            .card { 
                border: none !important; 
                box-shadow: none !important; 
                padding: 0 !important; 
                width: 100% !important;
                margin: 0 !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #cbd5e1 !important;
                padding: 8px !important;
                font-size: 10pt !important;
            }
        }
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
                <a href="estudiantes.php">🎓 Estudiantes</a>
            <?php endif; ?>

            <a href="libros.php">📖 Libros</a>
            <a href="categorias.php">🏷️ Categorías</a>

            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMIN'): ?>
                <a href="usuarios.php">👤 Usuarios (Admin)</a>
            <?php endif; ?>

            <a href="reportes.php" class="active">📊 Reportes</a>
        </nav>
    </aside>

    <main>
        <h1>Reportes y Estadísticas Generales</h1>

        <!-- Métricas Principales -->
        <div class="metrics-grid">
            <div class="metric-card">
                <h3><?php echo $totalEstudiantes; ?></h3>
                <p>Total Estudiantes</p>
            </div>
            <div class="metric-card">
                <h3><?php echo $totalLibros; ?></h3>
                <p>Total Libros</p>
            </div>
            <div class="metric-card">
                <h3><?php echo $prestamosHistoricos; ?></h3>
                <p>Préstamos Históricos</p>
            </div>
            <div class="metric-card">
                <h3><?php echo $prestamosActivos; ?></h3>
                <p>Préstamos Activos</p>
            </div>
            <div class="metric-card">
                <h3><?php echo $totalDevoluciones; ?></h3>
                <p>Devoluciones Total</p>
            </div>
        </div>

        <!-- Tabla de Reporte General -->
        <div class="card" style="padding: 25px;">
            <div class="card-header">
                <h3>Reporte General de Préstamos y Estados</h3>
                <button onclick="window.print()" class="btn-print">🖨️ Imprimir Reporte</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>No. Control</th>
                        <th>Estudiante</th>
                        <th>Libro</th>
                        <th>Fecha Préstamo</th>
                        <th>Fecha Límite</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reportePrestamos)): ?>
                        <?php foreach ($reportePrestamos as $rep): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($rep['id_prestamo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($rep['numeroControl']); ?></td>
                                <td><?php echo htmlspecialchars($rep['nombre'] . ' ' . $rep['apellidoPaterno']); ?></td>
                                <td><?php echo htmlspecialchars($rep['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($rep['fechaPrestamo']); ?></td>
                                <td><?php echo htmlspecialchars($rep['fechaDevolucion']); ?></td>
                                <td>
                                    <span style="background: <?php echo ($rep['estado'] === 'Activo') ? '#e0f2fe' : '#d1fae5'; ?>; color: <?php echo ($rep['estado'] === 'Activo') ? '#0369a1' : '#065f46'; ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($rep['estado']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No hay registros de préstamos disponibles para generar el reporte.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>