<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST["usuario"];
    $correo = $_POST["correo"];
    $password = password_hash($_POST["contrasena"], PASSWORD_DEFAULT); // Hashear contraseña

    // Verificar si el correo ya existe
    $sql_check = "SELECT * FROM usuarios WHERE correo = '$correo'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "<script>alert('El correo electrónico ya está registrado.');</script>";
    } else {
        // Insertar el nuevo usuario
        $sql = "INSERT INTO usuarios (usuario, correo, contrasena) VALUES ('$usuario', '$correo', '$password')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Registro exitoso'); window.location.href = 'login.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error: " . $sql . "<br>" . $conn->error . "');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .formulario {
            border: 1px solid #ccc;
            padding: 20px;
            width: 300px;
        }

        .formulario h2 {
            text-align: center;
        }

        .formulario label {
            display: block;
            margin-bottom: 5px;
        }

        .formulario input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .formulario button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="formulario">
        <h2>Registro</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required><br><br>

            <label for="correo">Correo Electrónico:</label>
            <input type="email" id="correo" name="correo" required><br><br>

            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required><br><br>

            <button type="submit">Registrarse</button>
            <p>
                ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
            </p>
        </form>
    </div>
</body>
</html>