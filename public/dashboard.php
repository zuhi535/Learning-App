<?php
session_start();

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Adatbáziskapcsolat ellenőrzése
include '../config/config.php';

if (!$conn) {
    die("Adatbáziskapcsolat nem jött létre!");
}

// Tantárgyak lekérése
$result = $conn->query("SELECT * FROM subjects");

if ($result === false) {
    die("Hiba a lekérdezés végrehajtásában: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Főoldal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body.dark-mode {
            background-color: #121212 !important;
            color: white !important;
        }
        .dark-mode .card {
            background-color: #1e1e1e;
            color: white;
        }
        .dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
    </style>
</head>
<body class="bg-light" id="pageBody">
    <div class="profile-section">
        <div class="btn-group">
            <a href="user/profile.php" class="btn btn-primary">Profil</a>
            <a href="logout.php" class="btn btn-danger">Kijelentkezés</a>
            <button id="toggleDarkMode" class="btn btn-secondary">Sötét mód</button>
        </div>
    </div>

    <div class="container mt-5">
        <div class="text-center mb-4">
            <h1 class="display-4">Üdvözöljük a felhasználói felületen!</h1>
            <p class="lead">Itt találja az elérhető tantárgyakat.</p>
        </div>

        <h2 class="mb-4">Elérhető tantárgyak</h2>
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">' . $row['name'] . '</h5>
                                <a href="subjects.php?id=' . $row['id'] . '" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="col-12"><p class="text-muted">Nincsenek elérhető tantárgyak.</p></div>';
            }
            ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const body = document.getElementById("pageBody");
            const toggleDarkModeBtn = document.getElementById("toggleDarkMode");
            
            if (localStorage.getItem("darkMode") === "enabled") {
                body.classList.add("dark-mode");
            }
            
            toggleDarkModeBtn.addEventListener("click", function () {
                body.classList.toggle("dark-mode");
                
                if (body.classList.contains("dark-mode")) {
                    localStorage.setItem("darkMode", "enabled");
                } else {
                    localStorage.removeItem("darkMode");
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
