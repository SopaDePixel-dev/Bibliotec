<?php
$host = 'localhost';
$port = '3307'; 
$db   = 'bibliotec';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "¡Conexión exitosa a la BD!";
} catch (\PDOException $e) {
    echo "Error de conexión a la Base de Datos: " . $e->getMessage();
    exit;
}
?>
