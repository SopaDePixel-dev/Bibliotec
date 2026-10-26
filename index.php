<?php
// Incluimos la conexión a la base de datos
include 'conexion.php';

// Manejo seguro por si la tabla aún no existe en la BD
try {
    $stmt = $pdo->query("SELECT id_libro, titulo, autor, anio_publicacion, stock FROM libros");
    $libros = $stmt->fetchAll();
} catch (PDOException $e) {
    $libros = [];
    $error = "Asegúrate de haber importado tu base de datos o creado la tabla 'libros'.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Catálogo</title>
    <!-- Fuente moderna de Google -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* Barra Lateral (Sidebar) */
        aside {
            width: 260px;
            background-color: var(--card-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 24px;
        }

        aside h2 {
            font-size: 1.25rem;
            color: var(--primary);
            margin-bottom: 32px;
        }

        aside nav a {
            display: block;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        aside nav a:hover, aside nav a.active {
            background-color: #eef2ff;
            color: var(--primary);
        }

        /* Contenido Principal */
        main {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .btn {
            background-color: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
            transition: background 0.2s;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        /* Tarjeta de la Tabla */
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 16px 24px;
        }

        th {
            background-color: #f8fafc;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
        }

        td {
            font-size: 0.95rem;
            border-bottom: 1px solid var(--border-color);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .badge {
            background-color: #d1fae5;
            color: #065f46;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .alert {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <!-- Menú lateral -->
    <aside>
        <h2>📚 BiblioTec</h2>
        <nav>
            <a href="index.php" class="active">Catálogo</a>
            <a href="libros.php">Registrar Libro</a>
            <a href="prestamos.php">Préstamos</a>
        </nav>
    </aside>

    <!-- Contenido -->
    <main>
        <div class="header-title">
            <h1>Catálogo de Libros</h1>
            <a href="libros.php" class="btn">+ Nuevo Libro</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Año</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($libros)): ?>
                        <?php foreach ($libros as $row): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($row['id_libro']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($row['autor']); ?></td>
                                <td><?php echo htmlspecialchars($row['anio_publicacion']); ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($row['stock']); ?> disp.</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                No hay libros registrados en la base de datos todavía.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
