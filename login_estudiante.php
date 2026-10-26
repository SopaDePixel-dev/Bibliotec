<?php
session_start();
include 'conexion.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroControl = trim($_POST['numeroControl']);
    $contrasena    = trim($_POST['contrasena']);

    if (!empty($numeroControl) && !empty($contrasena)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM estudiante WHERE numeroControl = :nc AND estatus = 'ACTIVO'");
            $stmt->execute([':nc' => $numeroControl]);
            $estudiante = $stmt->fetch();

            if ($estudiante && password_verify($contrasena, $estudiante['contrasena'])) {
                $_SESSION['numeroControl'] = $estudiante['numeroControl'];
                $_SESSION['nombre_estudiante'] = $estudiante['nombre'] . ' ' . $estudiante['apellidoPaterno'];
                header("Location: portal_estudiante.php");
                exit;
            } else {
                $error = "Número de control o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error en el sistema.";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Portal de Estudiantes</title>
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
        body { background-color: var(--bg-color); color: var(--text-main); display: flex; justify-content: center; align-items: center; min-height: 100vh; }

        .login-card { background: var(--card-bg); padding: 40px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
        .login-card h2 { font-size: 1.6rem; color: var(--primary); margin-bottom: 8px; text-align: center; }
        .login-card p { font-size: 0.85rem; color: var(--text-muted); text-align: center; margin-bottom: 30px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.95rem; }

        .btn { width: 100%; background-color: var(--primary); color: white; padding: 12px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; font-size: 0.95rem; }
        .btn:hover { background-color: var(--primary-dark); }

        .error-alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.85rem; background-color: #fee2e2; color: #991b1b; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 15px; font-size: 0.85rem; color: var(--text-muted); text-decoration: none; }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Portal de Estudiantes</h2>
        <p>BiblioTec - Acceso Seguro</p>

        <?php if (!empty($error)): ?>
            <div class="error-alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login_estudiante.php" method="POST">
            <div class="form-group">
                <label>Número de Control:</label>
                <input type="text" name="numeroControl" maxlength="9" required placeholder="Ej. 210200123">
            </div>
            <div class="form-group">
                <label>Contraseña / NIP:</label>
                <input type="password" name="contrasena" required placeholder="********">
            </div>
            <button type="submit" class="btn">Entrar a mi Portal</button>
        </form>
        <a href="index.php" class="back-link">← Volver al inicio</a>
    </div>

</body>
</html>