<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$friend_code = trim($_POST['friend_code']);

// Ellenőrizzük, hogy a barátkód létezik-e és nem a sajátja
$stmt = $conn->prepare("SELECT id FROM users WHERE friend_code = ? AND id != ?");
$stmt->bind_param("si", $friend_code, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Érvénytelen barátkód vagy saját magadat próbálod hozzáadni!";
    header("Location: /user/profile.php");
    exit();
}

$friend = $result->fetch_assoc();
$friend_id = $friend['id'];

// Ellenőrizzük, hogy már létezik-e a kapcsolat
$stmt = $conn->prepare("SELECT id FROM user_friends WHERE 
                       (user_id = ? AND friend_id = ?) OR 
                       (user_id = ? AND friend_id = ?)");
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "Már létezik kapcsolat a felhasználóval!";
    header("Location: /user/profile.php");
    exit();
}

// Létrehozzuk a barátkérelmet
$stmt = $conn->prepare("INSERT INTO user_friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $user_id, $friend_id);
if ($stmt->execute()) {
    $_SESSION['success'] = "Barátkérelem elküldve!";
} else {
    $_SESSION['error'] = "Hiba a barátkérelem küldése közben";
}

header("Location: /user/profile.php");
?>
