<?php
session_start();
include 'conexion.php';

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);

    if (!empty($correo)) {
        // 1. Buscar si el correo pertenece a un ESTUDIANTE
        $stmt = $pdo->prepare("SELECT * FROM estudiante WHERE correo = :correo");
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();
        $tipo = 'estudiante';
        $campoId = 'numeroControl';
        $valorId = $usuario['numeroControl'] ?? '';

        // 2. Si no es estudiante, buscar en la tabla USUARIO (Personal/Admin)
        if (!$usuario) {
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = :correo");
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch();
            $tipo = 'usuario';
            $campoId = 'id_usuario';
            $valorId = $usuario['id_usuario'] ?? '';
        }

        if ($usuario) {
            // Generar token seguro y fecha de expiración (30 minutos)
            $token = bin2hex(random_bytes(32));
            $expiracion = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            // Guardar token en la base de datos según corresponda
            if ($tipo === 'estudiante') {
                $upd = $pdo->prepare("UPDATE estudiante SET token_recuperacion = :token, token_expiracion = :exp WHERE numeroControl = :id");
            } else {
                $upd = $pdo->prepare("UPDATE usuario SET token_recuperacion = :token, token_expiracion = :exp WHERE id_usuario = :id");
            }
            $upd->execute([':token' => $token, ':exp' => $expiracion, ':id' => $valorId]);

            // --- AQUÍ ENVIAMOS EL CORREO ---
            // (Asegúrate de tener configurado PHPMailer o usa la función mail de abajo)
            $enlace = "http://localhost/BiblioTec/restablecer.php?token=" . $token;
            
            // Ejemplo básico con mail() de PHP (Requiere configurar sendmail en XAMPP o usar PHPMailer)
            $asunto = "Recuperación de Contraseña - BiblioTec";
            $cuerpo = "Hola, " . $usuario['nombre'] . ".\n\nHas solicitado restablecer tu contraseña. Haz clic en el siguiente enlace:\n" . $enlace . "\n\nEste enlace expirará en 30 minutos.";
            $headers = "From: soportedebibliotec@gmail.com";

            if (@mail($correo, $asunto, $cuerpo, $headers)) {
                $mensaje = "¡Correo enviado con éxito! Revisa tu bandeja de entrada.";
            } else {
                // Si XAMPP no tiene configurado el servidor de correo local, puedes mostrar el enlace directo para pruebas:
                $mensaje = "Simulación de envío. Tu enlace de recuperación es: <a href='$enlace'>Restablecer Contraseña</a>";
            }

        } else {
            $error = "No se encontró ninguna cuenta asociada a este correo electrónico.";
        }
    } else {
        $error = "Por favor, ingresa un correo electrónico.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>BiblioTec - Recuperar Contraseña</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1e40af; --bg-color: #f1f5f9; --card-bg: #ffffff; --text-main: #1e293b; --border-color: #cbd5e1; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: var(--card-bg); padding: 30px; border-radius: 10px; border: 1px solid var(--border-color); width: 100%; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { color: var(--primary); font-size: 1.4rem; margin-bottom: 10px; text-align: center; }
        p { font-size: 0.85rem; color: #64748b; text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; }
        .btn { width: 100%; background: var(--primary); color: white; padding: 10px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 15px; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 15px; }
        .back { display: block; text-align: center; margin-top: 15px; font-size: 0.85rem; color: #64748b; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Recuperar Contraseña</h2>
        <p>Ingresa tu correo electrónico registrado para recibir las instrucciones.</p>

        <?php if (!empty($mensaje)): ?>
            <div class="alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="recuperar.php" method="POST">
            <div class="form-group">
                <label>Correo Electrónico:</label>
                <input type="email" name="correo" required placeholder="correo@ejemplo.com">
            </div>
            <button type="submit" class="btn">Enviar Enlace</button>
        </form>
        <a href="index.php" class="back">← Volver al inicio</a>
    </div>
</body>
</html>