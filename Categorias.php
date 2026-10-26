<?php
include 'conexion.php';

$mensaje = "";
$editando = false;
$cat_edit = [
    'id_categoria' => '',
    'nombre' => '',
    'descripcion' => ''
];

// --- 1. ELIMINAR CATEGORÍA ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        $stmt =$pdo->prepare("DELETE FROM categoria WHERE id_categoria = :id");
        $stmt->execute([':id' =>$id]);
        header("Location: categorias.php");
        exit;
    } catch (PDOException $e) {$mensaje = "Error: No se puede eliminar la categoría porque tiene libros asociados.";
    }
}

// --- 2. CARGAR DATOS PARA EDITAR ---
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt =$pdo->prepare("SELECT * FROM categoria WHERE id_categoria = :id");
    $stmt->execute([':id' =>$id]);
    $resultado =$stmt->fetch();
    if ($resultado) {$editando = true;
        $cat_edit =$resultado;
    }
}

// --- 3. GUARDAR (CREAR O ACTUALIZAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_categoria = intval($_POST['id_categoria'] ?? 0);
    $nombre       = trim($_POST['nombre']);
    $descripcion  = trim($_POST['descripcion']);
    $es_actualizacion =$_POST['es_actualizacion'] === '1';

    if (!empty($nombre)) {
        try {
            if ($es_actualizacion && $id_categoria > 0) { // Actualizar (Update)$sql = "UPDATE categoria SET nombre = :nombre, descripcion = :descripcion WHERE id_categoria = :id";
                $stmt =$pdo->prepare($sql);$stmt->execute([
                    ':nombre'      => $nombre,
                    ':descripcion' => $descripcion,
                    ':id'          => $id_categoria
                ]);
                $mensaje = "¡Categoría actualizada con éxito!";
            } else {
                // Insertar (Create)
                $sql = "INSERT INTO categoria (nombre, descripcion) VALUES (:nombre, :descripcion)";
                $stmt =$pdo->prepare($sql);$stmt->execute([
                    ':nombre'      => $nombre,
                    ':descripcion' => $descripcion
                ]);
                $mensaje = "¡Categoría registrada con éxito!";
            }
            $editando = false;
            $cat_edit = ['id_categoria' => '', 'nombre' => '', 'descripcion' => ''];
        } catch (PDOException $e) {$mensaje = "Error al guardar la categoría.";
        }
    } else {
        $mensaje = "El nombre de la categoría es obligatorio.";
    }
}

$categorias =$pdo->query("SELECT * FROM categoria")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Módulo de Categorías</title>
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

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; }
        .form-group textarea { resize: vertical; height: 80px; }

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
            <a href="categorias.php" class="active">🏷️ Categorías</a>
            <?php if (isset($_SESSION['rol']) &&$_SESSION['rol'] === 'ADMIN'): ?>
                <a href="usuarios.php">👤 Usuarios (Admin)</a>
            <?php endif; ?>
            <a href="reportes.php">📊 Reportes</a>
        </nav>
    </aside>

    <main>
        <h1>Gestión de Categorías (CRUD Completo)</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><?php echo $editando ? 'Editar Categoría' : 'Registrar Nueva Categoría'; ?></h3>
            <form action="categorias.php" method="POST">
                <input type="hidden" name="es_actualizacion" value="<?php echo $editando ? '1' : '0'; ?>">
                <input type="hidden" name="id_categoria" value="<?php echo htmlspecialchars($cat_edit['id_categoria']); ?>">
                <div class="form-group">
                    <label>Nombre de la Categoría:</label>
                    <input type="text" name="nombre" required placeholder="Ej. Programación" value="<?php echo htmlspecialchars($cat_edit['nombre']); ?>">
                </div>
                <div class="form-group">
                    <label>Descripción:</label>
                    <textarea name="descripcion" placeholder="Breve descripción..."><?php echo htmlspecialchars($cat_edit['descripcion']); ?></textarea>
                </div>
                <button type="submit" class="btn"><?php echo $editando ? 'Actualizar Categoría' : 'Guardar Categoría'; ?></button>
                <?php if ($editando): ?>
                    <a href="categorias.php" class="btn-cancel">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                <h3>Categorías Existentes</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as$cat): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($cat['id_categoria']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cat['descripcion']); ?></td>
                                <td>
                                    <a href="categorias.php?editar=<?php echo $cat['id_categoria']; ?>" class="action-link edit-link">✏️ Editar</a>
                                    <a href="categorias.php?eliminar=<?php echo $cat['id_categoria']; ?>" class="action-link delete-link" onclick="return confirm('¿Estás seguro de eliminar esta categoría?');">🗑️ Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No hay categorías registradas todavía.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>