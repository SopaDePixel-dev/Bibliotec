<?php
session_start();
unset($_SESSION['numeroControl']);
unset($_SESSION['nombre_estudiante']);
session_destroy();
header("Location: login_estudiante.php");
exit;
?>