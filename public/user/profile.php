<?php
session_start();

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Adatbázis kapcsolat
include '../../config/config.php'; // Frissítsd az elérési utat, ha szükséges

// Felhasználó adatainak lekérése
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id='$user_id'");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Felhasználó nem található!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            margin-top: 2rem;
        }
        .profile-container h1 {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto;
            display: block;
        }
        .profile-info {
            margin-top: 1.5rem;
        }
        .profile-info p {
            margin-bottom: 0.5rem;
        }
        .btn-container {
            margin-top: 1.5rem;
            text-align: center;
        }
        .btn-container a {
            margin: 0 0.5rem;
        }

        /* Sötét mód stílusok */
        body.dark-mode {
            background-color: #121212 !important;
            color: white !important;
        }
        .dark-mode .profile-container {
            background-color: #1e1e1e;
            color: white;
            border-color: #333;
        }
        .dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
        .dark-mode .btn-warning {
            background-color: #ffa726;
            border-color: #ffa726;
        }
    </style>
</head>
<body class="bg-light" id="pageBody">
    <div class="profile-container">
        <h1>Profil</h1>
        
        <!-- Profilkép megjelenítése -->
        <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profilkép" class="profile-picture">

        <!-- Felhasználó adatainak megjelenítése -->
        <div class="profile-info">
            <p><strong>Felhasználónév:</strong> <?php echo $user['username']; ?></p>
            <p><strong>E-mail:</strong> <?php echo $user['email']; ?></p>
            <p><strong>Neme:</strong> 
                <?php
                if ($user['gender'] == 'male') {
                    echo 'Férfi';
                } elseif ($user['gender'] == 'female') {
                    echo 'Nő';
                } else {
                    echo 'Egyéb';
                }
                ?>
            </p>
            <!-- Pontszám megjelenítése -->
            <p><strong>Pontszám:</strong> <?php echo $user['score']; ?></p>
        </div>

        <!-- Gombok -->
        <div class="btn-container">
            <a href="../dashboard.php" class="btn btn-primary">Vissza a dashboardra</a>
            <a href="edit_profile.php" class="btn btn-warning">Profil szerkesztése</a>
        </div>

        <!-- Sötét mód váltó gomb -->
        <div class="btn-container">
            <button id="toggleDarkMode" class="btn btn-secondary">Sötét mód</button>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sötét mód JavaScript -->
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
</body>
</html>