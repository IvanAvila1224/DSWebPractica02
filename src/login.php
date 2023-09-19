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
    } 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script>
        function validarFormulario() {
            var nombreUsuario = document.getElementById("nombre_usuario").value;
            var contrasena = document.getElementById("contrasena").value;

            if (nombreUsuario.trim() === "" || contrasena.trim() === "") {
                alert("Por favor, completa todos los campos.");
                return false; 
            }
            
            return true; 
        }
    </script>
</head>
<body>
    <h1>Iniciar Sesión</h1>
    <form action="login.php" method="POST" onsubmit="return validarFormulario();">
        <label for="nombre_usuario">Nombre de Usuario:</label>
        <input type="text" id="nombre_usuario" name="nombre_usuario" ><br><br>
        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" ><br><br>
        <input type="submit" value="Iniciar Sesión">
    </form>
</body>
</html>
