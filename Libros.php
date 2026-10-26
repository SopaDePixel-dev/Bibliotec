<?php
include 'conexion.php';

$mensaje = "";
$editando = false;
$lib_edit = [
    'id_libro' => '',
    'titulo' => '',
    'editorial' => '',
    'isbn' => '',
    'stock' => 1,
    'id_categoria' => ''
];

// --- 1. ELIMINAR LIBRO ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        $stmt = $pdo->prepare("DELETE FROM libros WHERE id_libro = :id");
        $stmt->execute([':id' => $id]);
        header("Location: libros.php");
        exit;
    } catch (PDOException $e) {
        $mensaje = "Error: No se puede eliminar el libro porque tiene préstamos asociados.";
    }
}

// --- 2. CARGAR DATOS PARA EDITAR ---
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $pdo->prepare("SELECT * FROM libros WHERE id_libro = :id");
    $stmt->execute([':id' => $id]);
    $resultado = $stmt->fetch();
    if ($resultado) {
        $editando = true;
        $lib_edit = $resultado;
    }
}

// --- 3. GUARDAR (CREAR O ACTUALIZAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $id_libro     = intval($_POST['id_libro'] ?? 0);
    $titulo       = trim($_POST['titulo']);
    $editorial    = trim($_POST['editorial']);
    $isbn         = trim($_POST['isbn']);
    $stock        = intval($_POST['stock']);
    $id_categoria = intval($_POST['id_categoria']);
    $es_actualizacion = $_POST['es_actualizacion'] === '1';

    if (!empty($titulo) && !empty($isbn) && $id_categoria > 0) {
        try {
            if ($es_actualizacion && $id_libro > 0) {
                $sql = "UPDATE libros SET titulo = :titulo, editorial = :editorial, isbn = :isbn, stock = :stock, id_categoria = :id_cat WHERE id_libro = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':titulo'    => $titulo,
                    ':editorial' => $editorial,
                    ':isbn'      => $isbn,
                    ':stock'     => $stock,
                    ':id_cat'    => $id_categoria,
                    ':id'        => $id_libro
                ]);
                $mensaje = "¡Libro actualizado con éxito!";
            } else {
                $sql = "INSERT INTO libros (titulo, editorial, isbn, stock, id_categoria) 
                        VALUES (:titulo, :editorial, :isbn, :stock, :id_cat)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':titulo'    => $titulo,
                    ':editorial' => $editorial,
                    ':isbn'      => $isbn,
                    ':stock'     => $stock,
                    ':id_cat'    => $id_categoria
                ]);
                $mensaje = "¡Libro registrado con éxito!";
            }
            $editando = false;
            $lib_edit = ['id_libro' => '', 'titulo' => '', 'editorial' => '', 'isbn' => '', 'stock' => 1, 'id_categoria' => ''];
        } catch (PDOException $e) {
            $mensaje = "Error al guardar el libro.";
        }
    } else {
        $mensaje = "Por favor, completa los campos obligatorios y selecciona una categoría.";
    }
}

// --- 4. CONSULTA CON BÚSQUEDA DINÁMICA ---
$busqueda = trim($_GET['busqueda'] ?? '');
$categorias = $pdo->query("SELECT * FROM categoria")->fetchAll();

if (!empty($busqueda)) {
    $stmt = $pdo->prepare("SELECT l.*, c.nombre AS categoria_nombre FROM libros l LEFT JOIN categoria c ON l.id_categoria = c.id_categoria WHERE l.titulo LIKE :b OR l.editorial LIKE :b OR l.isbn LIKE :b ORDER BY l.id_libro DESC");
    $stmt->execute([':b' => "%$busqueda%"]);
    $libros = $stmt->fetchAll();
} else {
    $libros = $pdo->query("SELECT l.*, c.nombre AS categoria_nombre FROM libros l LEFT JOIN categoria c ON l.id_categoria = c.id_categoria ORDER BY l.id_libro DESC")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Módulo de Libros</title>
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
            <a href="estudiantes.php">🎓 Estudiantes</a>
            <a href="libros.php" class="active">📖 Libros</a>
            <a href="categorias.php">🏷️ Categorías</a>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMIN'): ?>
                <a href="usuarios.php">👤 Usuarios (Admin)</a>
            <?php endif; ?>
            <a href="reportes.php">📊 Reportes</a>
        </nav>
    </aside>

    <main>
        <h1>Gestión de Libros</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><?php echo $editando ? 'Editar Libro' : 'Registrar Nuevo Libro'; ?></h3>
            <form action="libros.php" method="POST">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="es_actualizacion" value="<?php echo $editando ? '1' : '0'; ?>">
                <input type="hidden" name="id_libro" value="<?php echo htmlspecialchars($lib_edit['id_libro']); ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Título:</label>
                        <input type="text" name="titulo" required placeholder="Título del libro" value="<?php echo htmlspecialchars($lib_edit['titulo']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Editorial:</label>
                        <input type="text" name="editorial" placeholder="Editorial" value="<?php echo htmlspecialchars($lib_edit['editorial']); ?>">
                    </div>
                    <div class="form-group">
                        <label>ISBN:</label>
                        <input type="text" name="isbn" required placeholder="Código ISBN" value="<?php echo htmlspecialchars($lib_edit['isbn']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Stock / Ejemplares:</label>
                        <input type="number" name="stock" min="1" required value="<?php echo htmlspecialchars($lib_edit['stock']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Categoría:</label>
                        <select name="id_categoria" required>
                            <option value="">Selecciona una categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($lib_edit['id_categoria'] == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn"><?php echo $editando ? 'Actualizar Libro' : 'Guardar Libro'; ?></button>
                <?php if ($editando): ?>
                    <a href="libros.php" class="btn-cancel">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                <h3>Catálogo de Libros Registrados</h3>
            </div>
            
            <div style="padding: 20px 20px 0 20px;">
                <form action="libros.php" method="GET" class="search-bar">
                    <input type="text" name="busqueda" placeholder="Buscar por título, editorial o ISBN..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn-search">🔍 Buscar</button>
                    <?php if (!empty($busqueda)): ?>
                        <a href="libros.php" class="btn-clear">❌ Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Editorial</th>
                        <th>ISBN</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($libros)): ?>
                        <?php foreach ($libros as $lib): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($lib['id_libro']); ?></strong></td>
                                <td><?php echo htmlspecialchars($lib['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($lib['editorial']); ?></td>
                                <td><?php echo htmlspecialchars($lib['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($lib['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?php echo htmlspecialchars($lib['stock']); ?> disp.</span></td>
                                <td>
                                    <a href="libros.php?editar=<?php echo $lib['id_libro']; ?>" class="action-link edit-link">✏️ Editar</a>
                                    <a href="libros.php?eliminar=<?php echo $lib['id_libro']; ?>" class="action-link delete-link" onclick="return confirm('¿Estás seguro de eliminar este libro?');">🗑️ Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No se encontraron libros registrados con ese criterio.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>