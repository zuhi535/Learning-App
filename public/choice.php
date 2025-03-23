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

// Ha a felhasználó választott egy lehetőséget
if (isset($_POST['choice'])) {
    $choice = $_POST['choice'];
    if ($choice == 'topics') {
        header("Location: topics.php?id=$subject_id");
        exit();
    } elseif ($choice == 'quizzes') {
        header("Location: quizzes.php?id=$subject_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Választás: <?php echo $subject['name']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .options {
            margin-top: 20px;
        }
        .options button {
            margin: 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Válassz lehetőséget</h1>
    <p>Mit szeretnél csinálni a(z) <strong><?php echo $subject['name']; ?></strong> tantárggyal?</p>

    <form method="post" action="">
        <div class="options">
            <button type="submit" name="choice" value="topics">Tananyag átnézése</button>
            <button type="submit" name="choice" value="quizzes">Kvízek elkezdése</button>
        </div>
    </form>
</body>
</html>