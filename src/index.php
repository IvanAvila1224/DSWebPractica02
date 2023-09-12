<?php
$url = "pgsql:host=172.17.0.2;port=5432;dbname=mydb;";
$pdo = new PDO($url, "postgres", "password", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    if (isset($_POST['submitInsert']) || isset($_POST['submitUpdate'])) {
        if (empty($nombre)) {
            $errors[] = "El campo Nombre es obligatorio.";
        }
        if (empty($direccion)) {
            $errors[] = "El campo Dirección es obligatorio.";
        }
        if (empty($telefono)) {
            $errors[] = "El campo Teléfono es obligatorio.";
        }

        if (empty($errors)) {
            try {
                if (isset($_POST['submitInsert'])) {
                    // INSERT
                    $insert = $pdo->prepare("INSERT INTO mytable (nombre, direccion, telefono) VALUES (:nombre, :direccion, :telefono)");
                    $insert->bindParam(':nombre', $nombre);
                    $insert->bindParam(':direccion', $direccion);
                    $insert->bindParam(':telefono', $telefono);
                    $insert->execute();
                } elseif (isset($_POST['submitUpdate'])) {
                    // UPDATE
                    $idEditar = $_POST['idEditar'];

                    $verificarExistencia = $pdo->prepare("SELECT COUNT(*) FROM mytable WHERE clave = :id");
                    $verificarExistencia->bindParam(':id', $idEditar);
                    $verificarExistencia->execute();

                    if ($verificarExistencia->fetchColumn() > 0) {
                        $update = $pdo->prepare("UPDATE mytable SET nombre = :nombre, direccion = :direccion, telefono = :telefono WHERE clave = :clave");
                        $update->bindParam(':nombre', $nombre);
                        $update->bindParam(':direccion', $direccion);
                        $update->bindParam(':telefono', $telefono);
                        $update->bindParam(':clave', $idEditar);
                        $update->execute();
                    } else {
                        echo 'El registro a editar no existe.';
                    }
                }

                header('Location: index.php');
                exit();
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
    }
}

if (isset($_GET['editar'])) {
    $idEditar = $_GET['editar'];
    $selectById = $pdo->prepare("SELECT * FROM mytable WHERE clave = :clave");
    $selectById->bindParam(':clave', $idEditar);
    $selectById->execute();
    $registroEditar = $selectById->fetch(PDO::FETCH_ASSOC);

    if ($registroEditar) {
        $nombre = $registroEditar['nombre'];
        $direccion = $registroEditar['direccion'];
        $telefono = $registroEditar['telefono'];
    }
}

if (isset($_GET['eliminar'])) {
    $idToDelete = $_GET['eliminar'];

    $delete = $pdo->prepare("DELETE FROM mytable WHERE clave = :id");
    $delete->bindParam(':id', $idToDelete);

    $delete->execute();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" /> 
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">
    <title>REGISTRO</title>
    
    <script>
        function confirmarEliminacion() {
            return confirm('¿Estás seguro de que deseas eliminar este registro?');
        }

        function validarFormulario() {
            var nombre = document.getElementsByName("nombre")[0].value;
            var direccion = document.getElementsByName("direccion")[0].value;
            var telefono = document.getElementsByName("telefono")[0].value;

            if (nombre.trim() === "" || direccion.trim() === "" || telefono.trim() === "") {
                alert("Por favor, completa todos los campos.");
                return false;
            }

            return true;
        }
        function borrarCampos() {
        document.getElementsByName("nombre")[0].value = "";
        document.getElementsByName("direccion")[0].value = "";
        document.getElementsByName("telefono")[0].value = "";
    }
    </script>
</head>
<body>
    <form action="index.php" method="POST" autocomplete="off" onsubmit="return validarFormulario();">
        <h1>INGRESA LOS DATOS DEL FORMULARIO</h1>
        <div>
            <table>
                <tr>
                    <td>Nombre: </td>
                    <td><input class="input" type="text" name="nombre" value="<?php echo isset($nombre) ? $nombre : ''; ?>" /></td>
                </tr>
                <tr>
                    <td>Direccion: </td>
                    <td><input class="input" type="text" name="direccion" value="<?php echo isset($direccion) ? $direccion : ''; ?>" /></td>
                </tr> 
                <tr>
                    <td>Telefono: </td>
                    <td><input class="input" type="text" name="telefono" value="<?php echo isset($telefono) ? $telefono : ''; ?>" /></td>
                </tr>
            </table>
        </div>
        <input type="hidden" name="idEditar" value="<?php echo isset($idEditar) ? $idEditar : ''; ?>">
        <input type="submit" class="button asigCompeButton" name="submitInsert" value="Guardar">
        <input type="submit" class="button asigCompeButton" name="submitUpdate" value="Actualizar">
        <button class="button asigCompeButton" type="button" onclick="borrarCampos();">Borrar Campos</button>
    </form>

    <?php
    if (!empty($errors)) {
        echo '<div class="errors"><ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul></div>';
    }
    
    if ($pdo) {
        $select = $pdo->prepare("SELECT * FROM mytable");
        $select->execute();
        $result = $select->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            echo '<h2>Resultados:</h2>';
            echo '<table border="1">';
            echo '<tr>
                    <th> Clave </th>
                    <th> Nombre </th>
                    <th> Direccion </th>
                    <th> Telefono </th>
                    <th> Actualizar </th>
                    <th> Eliminar </th> </tr>';

            foreach ($result as $row) {
                echo '<tr>';
                echo '<td><a href="index.php?clave=' . $row['clave'] . '">' . $row['clave'] . '</a></td>';
                echo '<td>' . $row['nombre'] . '</td>';
                echo '<td>' . $row['direccion'] . '</td>';
                echo '<td>' . $row['telefono'] . '</td>';
                echo '<td><a href="index.php?editar=' . $row['clave'] . '">Editar</a></td>';

                
                echo '<td><a href="index.php?eliminar=' . $row['clave'] . '" onclick="return confirmarEliminacion();">Eliminar</a></td>';

                echo '</tr>';
            }

            echo '</table>';
        }

        if (isset($_GET['clave'])) {
            $idSeleccionado = $_GET['clave'];

            $selectById = $pdo->prepare("SELECT * FROM mytable WHERE clave = :id");
            $selectById->bindParam(':id', $idSeleccionado);
            $selectById->execute();
            $registroSeleccionado = $selectById->fetch(PDO::FETCH_ASSOC);

            if ($registroSeleccionado) {
                echo '<h2>Detalles del Registro Seleccionado:</h2>';
                echo 'Clave: ' . $registroSeleccionado['clave'] . '<br>';
                echo 'Nombre: ' . $registroSeleccionado['nombre'] . '<br>';
                echo 'Direccion: ' . $registroSeleccionado['direccion'] . '<br>';
                echo 'Telefono: ' . $registroSeleccionado['telefono'] . '<br>';
            }
        }
    }
    ?>
</body>
</html>
