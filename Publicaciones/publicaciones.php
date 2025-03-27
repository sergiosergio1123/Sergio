<?php
session_start();
include 'config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener publicaciones de la base de datos
$sql = "SELECT publicaciones.id, publicaciones.titulo, publicaciones.contenido, usuarios.usuario FROM publicaciones INNER JOIN usuarios ON publicaciones.usuario_id = usuarios.id ORDER BY publicaciones.fecha_publicacion DESC";
$result = $conn->query($sql);

// Manejar la creación de nuevas publicaciones
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST["titulo"];
    $contenido = $_POST["contenido"];
    $usuario_id = $_SESSION["usuario_id"];

    $sql = "INSERT INTO publicaciones (usuario_id, titulo, contenido) VALUES ($usuario_id, '$titulo', '$contenido')";

    if ($conn->query($sql) === TRUE) {
        header("Location: publicaciones.php");
        exit;
    } else {
        echo "<script>alert('Error: " . $sql . "<br>" . $conn->error . "');</script>";
    }
}

// Función para manejar los likes
function agregarLike($conn, $publicacionId, $usuarioId) {
    $sql = "INSERT INTO likes (publicacion_id, usuario_id) VALUES ($publicacionId, $usuarioId)";
    $conn->query($sql);
}

// Función para manejar los dislikes
function agregarDislike($conn, $publicacionId, $usuarioId) {
    $sql = "INSERT INTO dislikes (publicacion_id, usuario_id) VALUES ($publicacionId, $usuarioId)";
    $conn->query($sql);
}

// Función para obtener el número de likes y dislikes
function obtenerLikesDislikes($conn, $publicacionId) {
    $sqlLikes = "SELECT COUNT(*) FROM likes WHERE publicacion_id = $publicacionId";
    $sqlDislikes = "SELECT COUNT(*) FROM dislikes WHERE publicacion_id = $publicacionId";

    $likes = $conn->query($sqlLikes)->fetch_array()[0];
    $dislikes = $conn->query($sqlDislikes)->fetch_array()[0];

    return array('likes' => $likes, 'dislikes' => $dislikes);
}

// Manejar los likes y dislikes desde AJAX
if (isset($_GET['like'])) {
    agregarLike($conn, $_GET['like'], $_SESSION['usuario_id']);
    $likesDislikes = obtenerLikesDislikes($conn, $_GET['like']);
    echo json_encode($likesDislikes);
    exit;
}

if (isset($_GET['dislike'])) {
    agregarDislike($conn, $_GET['dislike'], $_SESSION['usuario_id']);
    $likesDislikes = obtenerLikesDislikes($conn, $_GET['dislike']);
    echo json_encode($likesDislikes);
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Publicaciones</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            margin: 0;
        }

        nav {
            text-align: right;
            margin-top: 10px;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            background-color: #0056b3;
        }

        main {
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }

        #publicaciones {
            width: 70%;
        }

        #nueva-publicacion {
            width: 25%;
            border: 1px solid #ddd;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
        }

        .publicacion {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 8px;
        }

        .publicacion h3 {
            margin-top: 0;
            color: #007bff;
        }

        .usuario {
            font-style: italic;
            color: #777;
            margin-top: 10px;
        }

        .interaccion {
            margin-top: 15px;
        }

        .like, .dislike {
            padding: 8px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #e0e0e0;
        }

        .like:hover, .dislike:hover {
            background-color: #d0d0d0;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        form input[type="text"], form textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <h1>Mi Página de Publicaciones</h1>
        <nav>
            <a href="logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <main>
        <section id="publicaciones">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $likesDislikes = obtenerLikesDislikes($conn, $row['id']);
                    echo "<article class='publicacion'>";
                    echo "<h3>" . $row["titulo"] . "</h3>";
                    echo "<p>" . $row["contenido"] . "</p>";
                    echo "<div class='usuario'>Usuario: " . $row["usuario"] . "</div>";
                    echo "<div class='interaccion'>";
                    echo "<button class='like' data-id='" . $row["id"] . "'>Me gusta (<span>" . $likesDislikes['likes'] . "</span>)</button>";
                    echo "<button class='dislike' data-id='" . $row["id"] . "'>No me gusta (<span>" . $likesDislikes['dislikes'] . "</span>)</button>";
                    echo "</div>";
                    echo "</article>";
                }
            } else {
                echo "No hay publicaciones";
            }
            ?>
        </section>

        <aside id="nueva-publicacion">
            <h2>Nueva Publicación</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="titulo">Título:</label>
                <input type="text" id="titulo" name="titulo" required><br><br>

                <label for="contenido">Contenido:</label>
                <textarea id="contenido" name="contenido" required></textarea><br><br>

                <button type="submit">Publicar</button>
            </form>
        </aside>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const likeButtons = document.querySelectorAll('.like');
            const dislikeButtons = document.querySelectorAll('.dislike');

            likeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const publicacionId = button.dataset.id;
                    const likeCount = button.querySelector('span');

                    fetch(`publicaciones.php?like=${publicacionId}`)
                        .then(response => response.json())
                        .then(data => {
                            likeCount.textContent = data.likes;
                        });
                });
            });

            dislikeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const publicacionId = button.dataset.id;
                    const dislikeCount = button.querySelector('span');

                    fetch(`publicaciones.php?dislike=${publicacionId}`)
                        .then(response => response.json())
                        .then(data => {
                            dislikeCount.textContent = data.dislikes;
                        });
                });
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>