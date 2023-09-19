<?php
session_start();

$url = "pgsql:host=172.17.0.2;port=5432;dbname=mydb;";
$pdo = new PDO($url, "postgres", "password", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];

    
    $consulta = $pdo->prepare("SELECT clave FROM usuario WHERE username = :nombre_usuario AND contra = :contrasena");
    $consulta->bindParam(':nombre_usuario', $nombre_usuario);
    $consulta->bindParam(':contrasena', $contrasena);
    $consulta->execute();
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        
        $_SESSION['usuario_id'] = $usuario['clave'];
        header('Location: index.php'); 
        exit();
    } else {
        
        echo "Nombre de usuario o contraseña incorrectos.";
    }
}
?>
