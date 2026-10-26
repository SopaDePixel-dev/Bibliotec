<?php
session_start();
include 'conexion.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreUsuario = trim($_POST['nombreUsuario']);
    $contrasena    = trim($_POST['contrasena']);

    if (!empty($nombreUsuario) && !empty($contrasena)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE nombreUsuario = :user");
            $stmt->execute([':user' => $nombreUsuario]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];

                header("Location: index.php");
                exit;
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error en el sistema de autenticación.";
        }
    } else {
        $error = "Por favor, llena todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Iniciar Sesión</title>
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
    </style>
</head>
<body>

    <div class="login-card">
        <h2>BiblioTec</h2>
        <p>Sistema de Gestión Bibliotecaria</p>

        <?php if (!empty($error)): ?>
            <div class="error-alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Usuario:</label>
                <input type="text" name="nombreUsuario" required placeholder="Ingresa tu usuario">
            </div>
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="contrasena" required placeholder="********">
            </div>
            <button type="submit" class="btn">Ingresar al Sistema</button>
        </form>
    </div>

</body>
</html>