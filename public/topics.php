<?php
session_start();

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../config/config.php'; // Adatbáziskapcsolat

if (!$conn) {
    die("Adatbáziskapcsolat nem jött létre!");
}

// Tantárgy azonosítójának lekérése az URL-ből
if (isset($_GET['id'])) {
    $subject_id = $_GET['id'];
} else {
    die("Tantárgy azonosítója hiányzik!");
}

// Tantárgy adatainak lekérése
$sql = "SELECT name FROM subjects WHERE id = $subject_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Tantárgy nem található!");
}

$subject = $result->fetch_assoc();

// Tantárgyhoz tartozó témák lekérése
$topics = []; // Inicializáljuk a $topics változót
$sql = "SELECT id, title, description, image FROM topics WHERE subject_id = $subject_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row; // Témák hozzáadása a $topics tömbhöz
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $subject['name']; ?> tananyagok</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .topic-card {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
        }
        .topic-card img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .topic-card h2 {
            margin-bottom: 1rem;
        }
        .topic-card p {
            margin-bottom: 1rem;
        }
        .back-link {
            margin-top: 2rem;
            text-align: center;
        }

        /* Sötét mód stílusok */
        body.dark-mode {
            background-color: #121212 !important;
            color: white !important;
        }
        .dark-mode .topic-card {
            background-color: #1e1e1e;
            color: white;
            border-color: #333;
        }
        .dark-mode .btn-secondary {
            background-color: #3700b3;
            border-color: #3700b3;
        }
    </style>
</head>
<body class="bg-light" id="pageBody">
    <div class="container mt-5">
        <h1 class="text-center mb-4"><?php echo $subject['name']; ?> tananyagok</h1>

        <?php if (count($topics) > 0): ?>
            <?php foreach ($topics as $topic): ?>
                <div class="topic-card">
                    <h2><?php echo $topic['title']; ?></h2>
                    <p><?php echo $topic['description']; ?></p>
                    <?php if (!empty($topic['image'])): ?>
                        <img src="../uploads/<?php echo $topic['image']; ?>" alt="<?php echo $topic['title']; ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Nincsenek tananyagok ehhez a tantárgyhoz.</p>
        <?php endif; ?>

        <div class="back-link">
            <a href="subjects.php?id=<?php echo $subject_id; ?>" class="btn btn-secondary">Vissza a tantárgyhoz</a>
        </div>

        <!-- Sötét mód váltó gomb -->
        <div class="text-center mt-4">
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