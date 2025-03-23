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

// Ha a felhasználó választott egy lehetőséget
if (isset($_POST['choice'])) {
    $choice = $_POST['choice'];

    if ($choice == 'topics') {
        header("Location: topics.php?id=$subject_id");
        exit();
    } elseif ($choice == 'quizzes') {
        // Keresünk egy olyan témát, amelyhez van kvíz
        $sql = "SELECT t.id FROM topics t 
                JOIN quizzes q ON t.id = q.topic_id 
                WHERE t.subject_id = ? 
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $topic_id_with_quiz = $row['id'];

            // Átirányítás a megtalált témára
            header("Location: quizzes.php?id=$topic_id_with_quiz");
            exit();
        } else {
            // Nincs kvíz az adott tantárgyhoz
            $error_message = "Nincs elérhető kvíz ehhez a tantárgyhoz.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $subject['name']; ?> témák</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .subject-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            margin-top: 2rem;
        }
        .subject-container h1 {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .choice-form {
            margin-top: 2rem;
            text-align: center;
        }
        .choice-form button {
            margin: 0.5rem;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }

        /* Sötét mód stílusok */
        body.dark-mode {
            background-color: #121212 !important;
            color: white !important;
        }
        .dark-mode .subject-container {
            background-color: #1e1e1e;
            color: white;
            border-color: #333;
        }
        .dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
        .dark-mode .btn-secondary {
            background-color: #3700b3;
            border-color: #3700b3;
        }
        .dark-mode .btn-success {
            background-color: #03dac6;
            border-color: #03dac6;
        }
    </style>
</head>
<body class="bg-light" id="pageBody">
    <div class="subject-container">
        <h1><?php echo $subject['name']; ?> témák</h1>

        <!-- Hibaüzenet -->
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Választási lehetőségek -->
        <div class="choice-form">
            <h2>Válassz lehetőséget</h2>
            <p>Mit szeretnél csinálni a(z) <strong><?php echo $subject['name']; ?></strong> tantárggyal?</p>
            <form method="post" action="">
                <button type="submit" name="choice" value="topics" class="btn btn-primary">Tananyag átnézése</button>
                <button type="submit" name="choice" value="quizzes" class="btn btn-success">Kvízek elkezdése</button>
            </form>
        </div>

        <!-- Vissza a főoldalra gomb -->
        <div class="text-center mt-4">
            <a href="dashboard.php?id=<?php echo $subject_id; ?>" class="btn btn-secondary">Vissza a főoldalra</a>
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