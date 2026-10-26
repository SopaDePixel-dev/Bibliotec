<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

try {
    $totalLibros = $pdo->query("SELECT COUNT(*) FROM libros")->fetchColumn();
    $totalEjemplares = $pdo->query("SELECT SUM(stock) FROM libros")->fetchColumn() ?: 0;
    $prestamosActivos = $pdo->query("SELECT COUNT(*) FROM prestamo WHERE estado = 'Activo'")->fetchColumn();
    $totalDevoluciones = $pdo->query("SELECT COUNT(*) FROM devolucion")->fetchColumn();
    $totalEstudiantes = $pdo->query("SELECT COUNT(*) FROM estudiante")->fetchColumn();
    $totalCategorias = $pdo->query("SELECT COUNT(*) FROM categoria")->fetchColumn();
} catch (PDOException $e) {
    $totalLibros = 0;
    $totalEjemplares = 0;
    $prestamosActivos = 0;
    $totalDevoluciones = 0;
    $totalEstudiantes = 0;
    $totalCategorias = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Panel Principal</title>
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

        header { background-color: #1d4ed8; color: white; padding: 15px 30px; display: flex; justify-content: flex-end; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        header .user-info { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; }
        header .user-badge { background: rgba(255,255,255,0.2); padding: 6px 12px; border-radius: 20px; }
        
        .btn-logout { background-color: #dc2626; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: background 0.2s; }
        .btn-logout:hover { background-color: #b91c1c; }

        .content-body { padding: 30px; overflow-y: auto; }
        .welcome-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 20px; }

        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .metric-card { background: var(--card-bg); padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .metric-card h3 { font-size: 2.2rem; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .metric-card p { font-size: 0.9rem; color: var(--text-muted); font-weight: 500; }

        .quick-actions { background: var(--card-bg); padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); }
        .quick-actions h4 { margin-bottom: 15px; font-size: 1.1rem; }
        .btn-group { display: flex; flex-wrap: wrap; gap: 10px; }
        .btn { background-color: var(--primary); color: white; padding: 10px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: background 0.2s; }
        .btn:hover { background-color: var(--primary-dark); }
    </style>
</head>
<body>

    <aside>
        <h2>BiblioTec</h2>
        <p>Sistema de Biblioteca</p>
        <nav>
            <a href="dashboard_admin.php" class="active">🏠 Principal</a>
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

            <a href="reportes.php">📊 Reportes</a>
        </nav>
    </aside>

    <main>
        <header>
            <div class="user-info">
                <span class="user-badge">
                    Sesión: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong> 
                    (Rol: <strong><?php echo htmlspecialchars($_SESSION['rol']); ?></strong>)
                </span>
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <div class="content-body">
            <h1 class="welcome-title">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>

            <div class="metrics-grid">
                <div class="metric-card">
                    <h3><?php echo $totalLibros; ?></h3>
                    <p>Libros registrados</p>
                </div>
                <div class="metric-card">
                    <h3><?php echo $totalEjemplares; ?></h3>
                    <p>Ejemplares (Stock total)</p>
                </div>
                <div class="metric-card">
                    <h3><?php echo $prestamosActivos; ?></h3>
                    <p>Préstamos activos</p>
                </div>
                <div class="metric-card">
                    <h3><?php echo $totalDevoluciones; ?></h3>
                    <p>Devoluciones registradas</p>
                </div>
                <div class="metric-card">
                    <h3><?php echo $totalEstudiantes; ?></h3>
                    <p>Estudiantes activos</p>
                </div>
                <div class="metric-card">
                    <h3><?php echo $totalCategorias; ?></h3>
                    <p>Categorías</p>
                </div>
            </div>

            <div class="quick-actions">
                <h4>Accesos Rápidos</h4>
                <div class="btn-group">
                    <a href="prestamos.php" class="btn">+ Ir a Préstamos</a>
                    <a href="devoluciones.php" class="btn">+ Ir a Devoluciones</a>
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMIN'): ?>
                        <a href="estudiantes.php" class="btn">+ Registrar Estudiante</a>
                    <?php endif; ?>
                    <a href="libros.php" class="btn">+ Registrar Libro</a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>